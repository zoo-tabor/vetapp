<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Animal.php';

class BiochemistryImportController {

    public function index() {
        Auth::requireLogin();
        Auth::requireAdmin(); // Only admins can import data

        View::render('biochemistry/import', [
            'layout' => 'main',
            'title' => 'Import biochemie a hematologie'
        ]);
    }

    public function upload() {
        Auth::requireLogin();
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /biochemistry/import');
            exit;
        }

        // Check if file was uploaded
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Chyba při nahrávání souboru';
            header('Location: /biochemistry/import');
            exit;
        }

        $file = $_FILES['import_file'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($fileExtension, ['xlsx', 'xls', 'csv'])) {
            $_SESSION['error'] = 'Nepodporovaný formát souboru. Podporovány jsou pouze .xlsx, .xls a .csv';
            header('Location: /biochemistry/import');
            exit;
        }

        try {
            // Parse the file
            $data = $this->parseFile($file['tmp_name'], $fileExtension);

            // Store data in session for preview
            $_SESSION['import_preview'] = $data;
            $_SESSION['import_filename'] = $file['name'];

            header('Location: /biochemistry/import/preview');
            exit;

        } catch (Exception $e) {
            error_log("Import error: " . $e->getMessage());
            $_SESSION['error'] = 'Chyba při zpracování souboru: ' . $e->getMessage();
            header('Location: /biochemistry/import');
            exit;
        }
    }

    public function preview() {
        Auth::requireLogin();
        Auth::requireAdmin();

        if (!isset($_SESSION['import_preview'])) {
            $_SESSION['error'] = 'Žádná data k náhledu';
            header('Location: /biochemistry/import');
            exit;
        }

        $data = $_SESSION['import_preview'];
        $filename = $_SESSION['import_filename'] ?? 'unknown';

        // Validate and enrich data
        $validatedData = $this->validateData($data);

        View::render('biochemistry/import_preview', [
            'layout' => 'main',
            'title' => 'Náhled importu - ' . $filename,
            'data' => $validatedData,
            'filename' => $filename
        ]);
    }

    public function execute() {
        Auth::requireLogin();
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /biochemistry/import');
            exit;
        }

        if (!isset($_SESSION['import_preview'])) {
            $_SESSION['error'] = 'Žádná data k importu';
            header('Location: /biochemistry/import');
            exit;
        }

        try {
            $data = $_SESSION['import_preview'];
            $validatedData = $this->validateData($data);

            // Check for errors
            $errors = array_filter($validatedData, function($row) {
                return !empty($row['errors']);
            });

            if (!empty($errors)) {
                $_SESSION['error'] = 'Data obsahují chyby. Opravte je prosím před importem.';
                header('Location: /biochemistry/import/preview');
                exit;
            }

            // Execute import
            $result = $this->importData($validatedData);

            // Clear session data
            unset($_SESSION['import_preview']);
            unset($_SESSION['import_filename']);

            $_SESSION['success'] = sprintf(
                'Import dokončen: %d záznamů úspěšně importováno, %d chyb',
                $result['success'],
                $result['errors']
            );

            header('Location: /biochemistry/import');
            exit;

        } catch (Exception $e) {
            error_log("Import execution error: " . $e->getMessage());
            $_SESSION['error'] = 'Chyba při importu dat: ' . $e->getMessage();
            header('Location: /biochemistry/import/preview');
            exit;
        }
    }

    private function parseFile($filePath, $extension) {
        if ($extension === 'csv') {
            return $this->parseCSV($filePath);
        } else {
            return $this->parseExcel($filePath);
        }
    }

    private function parseCSV($filePath) {
        $data = [];
        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new Exception('Nelze otevřít CSV soubor');
        }

        // Remove UTF-8 BOM if present
        $bom = pack('H*','EFBBBF');
        $content = preg_replace("/^$bom/", '', $content);

        // Split into lines
        $lines = preg_split('/\r\n|\r|\n/', $content);

        if (empty($lines)) {
            throw new Exception('CSV soubor je prázdný');
        }

        // Read header
        $header = str_getcsv($lines[0], ';');
        if (empty($header)) {
            throw new Exception('CSV soubor má špatný formát hlavičky');
        }

        // Clean header from any remaining BOM or whitespace
        $header = array_map('trim', $header);

        // Read data rows
        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) {
                continue; // Skip empty lines
            }

            $row = str_getcsv($line, ';');
            if (count($row) === count($header)) {
                // Trim all values to remove whitespace
                $row = array_map('trim', $row);
                $data[] = array_combine($header, $row);
            }
        }

        return $data;
    }

    private function parseExcel($filePath) {
        // Check if PhpSpreadsheet is available
        if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            // Try to use simple XLSX parser
            return $this->parseExcelSimple($filePath);
        }

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (empty($rows)) {
            throw new Exception('Excel soubor je prázdný');
        }

        $header = array_shift($rows);
        $data = [];

        foreach ($rows as $row) {
            if (count($row) === count($header)) {
                $data[] = array_combine($header, $row);
            }
        }

        return $data;
    }

    private function parseExcelSimple($filePath) {
        // Simple XML-based XLSX parser for when PhpSpreadsheet is not available
        $zip = new ZipArchive;

        if ($zip->open($filePath) !== true) {
            throw new Exception('Nelze otevřít Excel soubor. Použijte prosím CSV formát.');
        }

        $sheetData = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetData === false) {
            throw new Exception('Nelze načíst data z Excel souboru');
        }

        // This is a simplified parser - you may want to use PhpSpreadsheet library instead
        $xml = simplexml_load_string($sheetData);

        // For now, suggest using CSV
        throw new Exception('Pro import Excel souborů je nutné nainstalovat PhpSpreadsheet knihovnu. Použijte prosím CSV formát nebo kontaktujte administrátora.');
    }

    private function validateData($data) {
        $animalModel = new Animal();
        $validated = [];

        foreach ($data as $index => $row) {
            $errors = [];
            $warnings = [];

            // Expected columns: animal_code, test_type, test_date, parameter_name, value, unit

            // Validate animal code
            if (empty($row['animal_code'])) {
                $errors[] = 'Chybí kód zvířete';
            } else {
                $animal = $animalModel->findByCode($row['animal_code']);
                if (!$animal) {
                    $errors[] = 'Zvíře s kódem "' . $row['animal_code'] . '" nebylo nalezeno';
                } else {
                    $row['animal_id'] = $animal['id'];
                    $row['animal_name'] = $animal['name'];
                }
            }

            // Validate test type
            if (empty($row['test_type'])) {
                $errors[] = 'Chybí typ testu';
            } elseif (!in_array(strtolower($row['test_type']), ['biochemistry', 'hematology', 'biochemie', 'hematologie'])) {
                $errors[] = 'Neplatný typ testu (povolené: biochemistry, hematology)';
            } else {
                // Normalize test type
                $testType = strtolower($row['test_type']);
                if ($testType === 'biochemie') $testType = 'biochemistry';
                if ($testType === 'hematologie') $testType = 'hematology';
                $row['test_type'] = $testType;
            }

            // Validate test date
            if (empty($row['test_date'])) {
                $errors[] = 'Chybí datum testu';
            } else {
                // Try parsing DD.MM.YYYY format first (Czech format)
                if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $row['test_date'], $matches)) {
                    $row['test_date'] = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                }
                $date = date_create($row['test_date']);
                if (!$date) {
                    $errors[] = 'Neplatný formát data';
                } else {
                    $row['test_date'] = $date->format('Y-m-d');
                }
            }

            // Validate parameter name
            if (empty($row['parameter_name'])) {
                $errors[] = 'Chybí název parametru';
            }

            // Validate value - allow both numeric and text values (e.g., "neg.", "negativní")
            if (!isset($row['value']) || $row['value'] === '') {
                $warnings[] = 'Prázdná hodnota parametru';
            }
            // Normalize common text values
            if (isset($row['value'])) {
                $value = trim($row['value']);
                // Normalize negative values
                if (in_array(strtolower($value), ['neg', 'neg.', 'negativní', 'negative'])) {
                    $row['value'] = 'neg.';
                }
                // Normalize positive values
                if (in_array(strtolower($value), ['poz', 'poz.', 'pozitivní', 'positive'])) {
                    $row['value'] = 'poz.';
                }
            }

            $row['errors'] = $errors;
            $row['warnings'] = $warnings;
            $row['row_number'] = $index + 2; // +2 because of header and 0-index

            $validated[] = $row;
        }

        return $validated;
    }

    private function importData($data) {
        $db = Database::getInstance()->getConnection();
        $successCount = 0;
        $errorCount = 0;

        // Group data by animal and test
        $groupedData = [];
        foreach ($data as $row) {
            if (!empty($row['errors'])) {
                $errorCount++;
                continue;
            }

            $key = $row['animal_id'] . '_' . $row['test_type'] . '_' . $row['test_date'];
            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'animal_id' => $row['animal_id'],
                    'test_type' => $row['test_type'],
                    'test_date' => $row['test_date'],
                    'test_location' => $row['test_location'] ?? '',
                    'reference_source' => $row['reference_source'] ?? 'Import',
                    'notes' => $row['notes'] ?? '',
                    'parameters' => []
                ];
            }

            $groupedData[$key]['parameters'][] = [
                'parameter_name' => $row['parameter_name'],
                'value' => $row['value'],
                'unit' => $row['unit'] ?? ''
            ];
        }

        // Import each test
        foreach ($groupedData as $testData) {
            try {
                $db->beginTransaction();

                $tableName = $testData['test_type'] === 'biochemistry' ? 'biochemistry_tests' : 'hematology_tests';

                // Check if test already exists
                $stmt = $db->prepare("
                    SELECT id FROM {$tableName}
                    WHERE animal_id = ? AND test_date = ?
                ");
                $stmt->execute([$testData['animal_id'], $testData['test_date']]);
                $existingTest = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingTest) {
                    // Update existing test
                    $testId = $existingTest['id'];
                } else {
                    // Insert new test
                    $stmt = $db->prepare("
                        INSERT INTO {$tableName}
                        (animal_id, test_date, test_location, reference_source, notes, created_by)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $testData['animal_id'],
                        $testData['test_date'],
                        $testData['test_location'],
                        $testData['reference_source'],
                        $testData['notes'],
                        Auth::userId()
                    ]);
                    $testId = $db->lastInsertId();
                }

                // Insert parameters
                $resultsTableName = $testData['test_type'] === 'biochemistry' ? 'biochemistry_results' : 'hematology_results';

                foreach ($testData['parameters'] as $param) {
                    // Check if parameter exists
                    $stmt = $db->prepare("
                        SELECT id FROM {$resultsTableName}
                        WHERE test_id = ? AND parameter_name = ?
                    ");
                    $stmt->execute([$testId, $param['parameter_name']]);
                    $existingParam = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existingParam) {
                        // Update existing parameter
                        $stmt = $db->prepare("
                            UPDATE {$resultsTableName}
                            SET value = ?, unit = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $param['value'],
                            $param['unit'],
                            $existingParam['id']
                        ]);
                    } else {
                        // Insert new parameter
                        $stmt = $db->prepare("
                            INSERT INTO {$resultsTableName}
                            (test_id, parameter_name, value, unit)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $testId,
                            $param['parameter_name'],
                            $param['value'],
                            $param['unit']
                        ]);
                    }
                }

                $db->commit();
                $successCount++;

            } catch (Exception $e) {
                $db->rollBack();
                error_log("Import error for test: " . $e->getMessage());
                $errorCount++;
            }
        }

        return [
            'success' => $successCount,
            'errors' => $errorCount
        ];
    }
}
