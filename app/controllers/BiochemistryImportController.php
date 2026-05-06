<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Animal.php';

class BiochemistryImportController {

    public function index() {
        Auth::requireLogin();
        Auth::requireAdmin();

        View::render('biochemistry/import', [
            'layout' => 'main',
            'title' => 'Import LDT vysledku'
        ]);
    }

    public function upload() {
        Auth::requireLogin();
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /biochemistry/import');
            exit;
        }

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Chyba pri nahravani souboru.';
            header('Location: /biochemistry/import');
            exit;
        }

        $file = $_FILES['import_file'];
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($fileExtension !== 'ldt') {
            $_SESSION['error'] = 'Nepodporovany format souboru. Importovat lze pouze .ldt soubory.';
            header('Location: /biochemistry/import');
            exit;
        }

        try {
            $data = $this->parseFile($file['tmp_name'], $fileExtension);

            $_SESSION['import_preview'] = $data;
            $_SESSION['import_filename'] = $file['name'];

            header('Location: /biochemistry/import/preview');
            exit;
        } catch (Exception $e) {
            error_log("LDT import error: " . $e->getMessage());
            $_SESSION['error'] = 'Chyba pri zpracovani LDT souboru: ' . $e->getMessage();
            header('Location: /biochemistry/import');
            exit;
        }
    }

    public function preview() {
        Auth::requireLogin();
        Auth::requireAdmin();

        if (!isset($_SESSION['import_preview'])) {
            $_SESSION['error'] = 'Zadna data k nahledu.';
            header('Location: /biochemistry/import');
            exit;
        }

        $data = $_SESSION['import_preview'];
        $filename = $_SESSION['import_filename'] ?? 'unknown.ldt';
        $validatedData = $this->validateData($data);

        View::render('biochemistry/import_preview', [
            'layout' => 'main',
            'title' => 'Nahled importu - ' . $filename,
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
            $_SESSION['error'] = 'Zadna data k importu.';
            header('Location: /biochemistry/import');
            exit;
        }

        try {
            $data = $_SESSION['import_preview'];
            $validatedData = $this->validateData($data);

            $errors = array_filter($validatedData, function($row) {
                return !empty($row['errors']);
            });

            if (!empty($errors)) {
                $_SESSION['error'] = 'Data obsahuji chyby. Opravte je prosim pred importem.';
                header('Location: /biochemistry/import/preview');
                exit;
            }

            $result = $this->importData($validatedData);

            unset($_SESSION['import_preview'], $_SESSION['import_filename']);

            $_SESSION['success'] = sprintf(
                'Import dokoncen: %d testu uspesne importovano, %d chyb.',
                $result['success'],
                $result['errors']
            );

            header('Location: /biochemistry/import');
            exit;
        } catch (Exception $e) {
            error_log("LDT import execution error: " . $e->getMessage());
            $_SESSION['error'] = 'Chyba pri importu dat: ' . $e->getMessage();
            header('Location: /biochemistry/import/preview');
            exit;
        }
    }

    private function parseFile($filePath, $extension) {
        if ($extension !== 'ldt') {
            throw new Exception('Import podporuje pouze .ldt soubory.');
        }

        return $this->parseLdt($filePath);
    }

    private function parseLdt($filePath) {
        $content = file_get_contents($filePath);

        if ($content === false || $content === '') {
            throw new Exception('Nelze otevrit LDT soubor nebo je soubor prazdny.');
        }

        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        $fields = $this->parseLdtFields($content);

        if (empty($fields)) {
            throw new Exception('LDT soubor neobsahuje zadne citelne zaznamy.');
        }

        return $this->mapLdtFieldsToImportRows($fields);
    }

    private function parseLdtFields($content) {
        $parts = preg_split('/(\r\n|\r|\n)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $fields = [];
        $lineNumber = 0;

        for ($i = 0; $i < count($parts); $i += 2) {
            $line = $parts[$i];
            $lineEnding = $parts[$i + 1] ?? '';

            if ($line === '') {
                continue;
            }

            $lineNumber++;

            if (strlen($line) < 7 || !ctype_digit(substr($line, 0, 3)) || !ctype_digit(substr($line, 3, 4))) {
                continue;
            }

            $fields[] = [
                'line_number' => $lineNumber,
                'declared_length' => (int)substr($line, 0, 3),
                'actual_length' => strlen($line) + strlen($lineEnding),
                'field_id' => substr($line, 3, 4),
                'value' => trim(substr($line, 7)),
            ];
        }

        return $fields;
    }

    private function mapLdtFieldsToImportRows($fields) {
        $first = function($fieldId) use ($fields) {
            foreach ($fields as $field) {
                if ($field['field_id'] === $fieldId && $field['value'] !== '') {
                    return $field['value'];
                }
            }

            return null;
        };

        $protocol = $this->normalizeLdtProtocol($first('8310') ?? $first('8311'));
        $animalName = $first('3204');
        $speciesOrBreed = $first('3102') ?? $first('3202');
        $reportDate = $this->parseLdtDate($first('8301') ?? $first('8302'));
        $sender = $first('0203') ?? $first('8300') ?? 'LDT';
        $labPatientId = $first('3101');
        $currentSectionType = null;
        $current = null;
        $chipNumber = null;
        $rows = [];

        $flush = function() use (&$current, &$rows, &$chipNumber, $protocol, $animalName, $speciesOrBreed, $reportDate, $sender, $labPatientId) {
            if (!$current) {
                return;
            }

            $parameterName = trim($current['parameter_name'] ?? '');
            $value = trim($current['value'] ?? '');

            if (empty($current['test_type'])) {
                if ($this->isChipNumberResult($parameterName) && $value !== '') {
                    $chipNumber = $value;
                }

                $current = null;
                return;
            }

            if ($parameterName !== '' || $value !== '') {
                $testDate = $this->parseLdtDate($current['date_raw'] ?? null) ?? $reportDate;
                $rows[] = [
                    'animal_code' => $labPatientId ?? $chipNumber ?? $animalName ?? '',
                    'animal_name_ldt' => $animalName ?? '',
                    'animal_identifier_ldt' => $labPatientId ?? '',
                    'animal_chip' => $chipNumber ?? '',
                    'species_or_breed' => $speciesOrBreed ?? '',
                    'test_type' => $current['test_type'],
                    'test_date' => $testDate ?? '',
                    'parameter_name' => $parameterName,
                    'value' => $value,
                    'unit' => trim($current['unit'] ?? ''),
                    'reference_range' => trim($current['reference_range'] ?? ''),
                    'test_location' => 'Laboklin',
                    'reference_source' => 'Laboklin',
                    'notes' => trim('LDT protokol: ' . ($protocol ?? '') . '; Zdroj: ' . $sender),
                    'ldt_protocol' => $protocol ?? '',
                    'ldt_result_index' => $current['index'] ?? '',
                    'ldt_date_raw' => $current['date_raw'] ?? '',
                ];
            }

            $current = null;
        };

        foreach ($fields as $field) {
            $fieldId = $field['field_id'];
            $value = $field['value'];

            if ($fieldId === '8470') {
                $detectedSectionType = $this->detectLdtSectionType($value);
                if ($detectedSectionType && $current && empty($current['test_type'])) {
                    $flush();
                    $currentSectionType = $detectedSectionType;
                    continue;
                }

                if ($detectedSectionType && (!$current || (empty($current['parameter_name']) && empty($current['value'])))) {
                    $currentSectionType = $detectedSectionType;
                    $current = null;
                    continue;
                }
            }

            if ($fieldId === '8470' && !$current) {
                continue;
            }

            if ($fieldId === '8410') {
                $flush();
                $current = [
                    'index' => $value,
                    'test_type' => $currentSectionType,
                ];
                continue;
            }

            if (!$current) {
                continue;
            }

            switch ($fieldId) {
                case '8411':
                    $current['parameter_name'] = $value;
                    break;
                case '8420':
                    $current['value'] = $value;
                    break;
                case '8421':
                    $current['unit'] = $value;
                    break;
                case '8432':
                    $current['date_raw'] = $value;
                    break;
                case '8460':
                    $current['reference_range'] = $value;
                    break;
            }
        }

        $flush();

        if (empty($rows)) {
            throw new Exception('V LDT souboru nebyly nalezeny importovatelne biochemicke nebo hematologicke vysledky.');
        }

        return $rows;
    }

    private function normalizeLdtProtocol($value) {
        if ($value === null) {
            return null;
        }

        return preg_replace('/[^A-Za-z0-9_-]/', '', trim($value));
    }

    private function detectLdtSectionType($value) {
        $normalized = $this->normalizeSearchText($value);

        if (strpos($normalized, 'hemat') !== false || strpos($normalized, 'krev') !== false || strpos($normalized, 'blood') !== false) {
            return 'hematology';
        }

        if (strpos($normalized, 'biochem') !== false || strpos($normalized, 'klinische chemie') !== false) {
            return 'biochemistry';
        }

        return null;
    }

    private function isChipNumberResult($parameterName) {
        $normalized = $this->normalizeSearchText($parameterName);
        return strpos($normalized, 'cislo cipu') !== false || strpos($normalized, 'chip') !== false;
    }

    private function normalizeSearchText($value) {
        $value = strtolower(trim((string)$value));

        return strtr($value, [
            'á' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e',
            'í' => 'i', 'ň' => 'n', 'ó' => 'o', 'ř' => 'r', 'š' => 's',
            'ť' => 't', 'ú' => 'u', 'ů' => 'u', 'ý' => 'y', 'ž' => 'z',
            'ä' => 'a', 'ö' => 'o', 'ü' => 'u', 'ß' => 'ss',
        ]);
    }

    private function parseLdtDate($value) {
        $value = trim((string)$value);

        if ($value === '') {
            return null;
        }

        if (preg_match('/^(\d{2})(\d{2})(\d{4})$/', $value, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $matches)) {
            return $matches[1] . '-' . $matches[2] . '-' . $matches[3];
        }

        return null;
    }

    private function validateData($data) {
        $validated = [];

        foreach ($data as $index => $row) {
            $errors = [];
            $warnings = [];
            $animal = $this->findAnimalForLdt($row);

            if (!$animal) {
                $label = $row['animal_name_ldt'] ?: ($row['animal_code'] ?? '');
                $errors[] = 'Zvire z LDT nebylo nalezeno v databazi: ' . $label;
            } elseif (isset($animal['ambiguous'])) {
                $errors[] = 'Podle jmena "' . ($row['animal_name_ldt'] ?? '') . '" bylo nalezeno vice zvirat. Doplnte jednoznacny identifikator.';
            } else {
                $row['animal_id'] = $animal['id'];
                $row['animal_name'] = $animal['name'];
                $row['animal_identifier'] = $animal['identifier'] ?? '';
            }

            if (empty($row['test_type']) || !in_array($row['test_type'], ['biochemistry', 'hematology'], true)) {
                $errors[] = 'Neplatny typ testu v LDT.';
            }

            if (empty($row['test_date'])) {
                $errors[] = 'Chybi datum testu.';
            } else {
                $date = date_create($row['test_date']);
                if (!$date) {
                    $errors[] = 'Neplatny format data.';
                } else {
                    $row['test_date'] = $date->format('Y-m-d');
                }
            }

            if (empty($row['parameter_name'])) {
                $errors[] = 'Chybi nazev parametru.';
            }

            if (!isset($row['value']) || $row['value'] === '') {
                $warnings[] = 'Prazdna hodnota parametru.';
            } else {
                $row['value'] = $this->normalizeResultValue($row['value']);
            }

            if (empty($row['unit'])) {
                $warnings[] = 'Chybi jednotka parametru.';
            }

            $row['errors'] = $errors;
            $row['warnings'] = $warnings;
            $row['row_number'] = $index + 1;

            $validated[] = $row;
        }

        return $validated;
    }

    private function findAnimalForLdt($row) {
        $animalModel = new Animal();
        $identifiers = array_filter(array_unique([
            trim($row['animal_identifier_ldt'] ?? ''),
            trim($row['animal_chip'] ?? ''),
            trim($row['animal_code'] ?? ''),
        ]));

        foreach ($identifiers as $identifier) {
            $animal = $animalModel->findByCode($identifier);
            if ($animal) {
                return $animal;
            }
        }

        $animalName = trim($row['animal_name_ldt'] ?? '');
        if ($animalName === '') {
            return null;
        }

        $matches = $animalModel->query(
            "SELECT * FROM animals WHERE name = ? OR identifier = ? ORDER BY id ASC",
            [$animalName, $animalName]
        );

        if (count($matches) === 1) {
            return $matches[0];
        }

        if (count($matches) > 1) {
            return ['ambiguous' => true];
        }

        return null;
    }

    private function normalizeResultValue($value) {
        $value = trim((string)$value);
        $lower = strtolower($value);

        if (in_array($lower, ['neg', 'neg.', 'negative', 'negativni'], true)) {
            return 'neg.';
        }

        if (in_array($lower, ['poz', 'poz.', 'positive', 'pozitivni'], true)) {
            return 'poz.';
        }

        if (preg_match('/^[<>]?\s*-?\d+(,\d+)?$/', $value)) {
            return str_replace(',', '.', $value);
        }

        return $value;
    }

    private function importData($data) {
        $db = Database::getInstance()->getConnection();
        $successCount = 0;
        $errorCount = 0;
        $groupedData = [];

        foreach ($data as $row) {
            if (!empty($row['errors'])) {
                $errorCount++;
                continue;
            }

            $key = $row['animal_id'] . '_' . $row['test_type'] . '_' . $row['test_date'] . '_' . ($row['ldt_protocol'] ?? '');
            if (!isset($groupedData[$key])) {
                $groupedData[$key] = [
                    'animal_id' => $row['animal_id'],
                    'test_type' => $row['test_type'],
                    'test_date' => $row['test_date'],
                    'test_location' => $row['test_location'] ?? 'Laboklin',
                    'reference_source' => $row['reference_source'] ?? 'Laboklin',
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

        foreach ($groupedData as $testData) {
            try {
                $db->beginTransaction();

                $tableName = $testData['test_type'] === 'biochemistry' ? 'biochemistry_tests' : 'hematology_tests';
                $stmt = $db->prepare("
                    SELECT id FROM {$tableName}
                    WHERE animal_id = ? AND test_date = ?
                ");
                $stmt->execute([$testData['animal_id'], $testData['test_date']]);
                $existingTest = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingTest) {
                    $testId = $existingTest['id'];
                    $stmt = $db->prepare("
                        UPDATE {$tableName}
                        SET test_location = ?, reference_source = ?, notes = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $testData['test_location'],
                        $testData['reference_source'],
                        $testData['notes'],
                        $testId
                    ]);
                } else {
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

                $resultsTableName = $testData['test_type'] === 'biochemistry' ? 'biochemistry_results' : 'hematology_results';
                foreach ($testData['parameters'] as $param) {
                    $stmt = $db->prepare("
                        SELECT id FROM {$resultsTableName}
                        WHERE test_id = ? AND parameter_name = ?
                    ");
                    $stmt->execute([$testId, $param['parameter_name']]);
                    $existingParam = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existingParam) {
                        $stmt = $db->prepare("
                            UPDATE {$resultsTableName}
                            SET value = ?, unit = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$param['value'], $param['unit'], $existingParam['id']]);
                    } else {
                        $stmt = $db->prepare("
                            INSERT INTO {$resultsTableName}
                            (test_id, parameter_name, value, unit)
                            VALUES (?, ?, ?, ?)
                        ");
                        $stmt->execute([$testId, $param['parameter_name'], $param['value'], $param['unit']]);
                    }
                }

                $db->commit();
                $successCount++;
            } catch (Exception $e) {
                $db->rollBack();
                error_log("LDT import error for test: " . $e->getMessage());
                $errorCount++;
            }
        }

        return [
            'success' => $successCount,
            'errors' => $errorCount
        ];
    }
}
