<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/Animal.php';

class BiochemistryController {

    public function index() {
        Auth::requireLogin();

        // Get user's workplaces
        $userModel = new User();
        $workplaceModel = new Workplace();

        $workplaces = $userModel->getWorkplacePermissions(Auth::userId());

        View::render('biochemistry/dashboard', [
            'layout' => 'main',
            'title' => 'Biochemie a hematologie - Dashboard',
            'workplaces' => $workplaces
        ]);
    }

    public function workplace($id) {
        Auth::requireLogin();

        $userModel = new User();
        $workplaceModel = new Workplace();
        $animalModel = new Animal();

        // Check permissions
        if (!$userModel->hasPermission(Auth::userId(), $id)) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Přístup odepřen',
                'message' => 'Nemáte oprávnění k tomuto pracovišti'
            ]);
            return;
        }

        $workplace = $workplaceModel->findById($id);
        if (!$workplace) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Chyba',
                'message' => 'Pracoviště nenalezeno'
            ]);
            return;
        }

        // Get all animals for this workplace
        $animals = $animalModel->getByWorkplace($id);

        // Get latest test dates for each animal using optimized queries
        $db = Database::getInstance()->getConnection();

        if (!empty($animals)) {
            $animalIds = array_column($animals, 'id');
            $placeholders = str_repeat('?,', count($animalIds) - 1) . '?';

            // Get latest biochemistry test dates for all animals in one query
            $stmtBiochem = $db->prepare("
                SELECT bt.animal_id, MAX(bt.test_date) as test_date
                FROM biochemistry_tests bt
                WHERE bt.animal_id IN ($placeholders)
                GROUP BY bt.animal_id
            ");
            $stmtBiochem->execute($animalIds);
            $biochemResults = $stmtBiochem->fetchAll(PDO::FETCH_ASSOC);

            // Create lookup array for biochemistry results
            $biochemLookup = [];
            foreach ($biochemResults as $result) {
                $biochemLookup[$result['animal_id']] = $result['test_date'];
            }

            // Get latest hematology test dates for all animals in one query
            $stmtHemato = $db->prepare("
                SELECT ht.animal_id, MAX(ht.test_date) as test_date
                FROM hematology_tests ht
                WHERE ht.animal_id IN ($placeholders)
                GROUP BY ht.animal_id
            ");
            $stmtHemato->execute($animalIds);
            $hematoResults = $stmtHemato->fetchAll(PDO::FETCH_ASSOC);

            // Create lookup array for hematology results
            $hematoLookup = [];
            foreach ($hematoResults as $result) {
                $hematoLookup[$result['animal_id']] = $result['test_date'];
            }

            // Assign test dates to animals
            foreach ($animals as &$animal) {
                $animal['last_biochemistry'] = $biochemLookup[$animal['id']] ?? null;
                $animal['last_hematology'] = $hematoLookup[$animal['id']] ?? null;
            }
        }

        View::render('biochemistry/workplace', [
            'layout' => 'main',
            'title' => 'Biochemie a hematologie - ' . $workplace['name'],
            'workplace' => $workplace,
            'animals' => $animals,
            'canEdit' => $userModel->hasPermission(Auth::userId(), $id, 'edit')
        ]);
    }

    public function animal($id) {
        Auth::requireLogin();

        $animalModel = new Animal();
        $userModel = new User();

        // Get animal details with enclosure and workplace names
        $animal = $animalModel->getDetail($id);
        if (!$animal) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Chyba',
                'message' => 'Zvíře nenalezeno'
            ]);
            return;
        }

        // Check permissions (must have access to the animal's workplace)
        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'])) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Přístup odepřen',
                'message' => 'Nemáte oprávnění k tomuto zvířeti'
            ]);
            return;
        }

        // Get biochemistry and hematology tests
        $db = Database::getInstance()->getConnection();

        // Get all biochemistry tests with results
        $stmtBiochem = $db->prepare("
            SELECT bt.id, bt.test_date, bt.test_location, bt.reference_source, bt.notes,
                   u.full_name as created_by_name
            FROM biochemistry_tests bt
            LEFT JOIN users u ON bt.created_by = u.id
            WHERE bt.animal_id = ?
            ORDER BY bt.test_date DESC
        ");
        $stmtBiochem->execute([$id]);
        $biochemTests = $stmtBiochem->fetchAll(PDO::FETCH_ASSOC);

        // Get all hematology tests with results
        $stmtHemato = $db->prepare("
            SELECT ht.id, ht.test_date, ht.test_location, ht.reference_source, ht.notes,
                   u.full_name as created_by_name
            FROM hematology_tests ht
            LEFT JOIN users u ON ht.created_by = u.id
            WHERE ht.animal_id = ?
            ORDER BY ht.test_date DESC
        ");
        $stmtHemato->execute([$id]);
        $hematoTests = $stmtHemato->fetchAll(PDO::FETCH_ASSOC);

        // Get results for all biochemistry tests in one query
        if (!empty($biochemTests)) {
            $biochemTestIds = array_column($biochemTests, 'id');
            $placeholders = str_repeat('?,', count($biochemTestIds) - 1) . '?';

            $stmtResults = $db->prepare("
                SELECT test_id, parameter_name, value, unit
                FROM biochemistry_results
                WHERE test_id IN ($placeholders)
            ");
            $stmtResults->execute($biochemTestIds);
            $allBiochemResults = $stmtResults->fetchAll(PDO::FETCH_ASSOC);

            // Group results by test_id
            $biochemResultsLookup = [];
            foreach ($allBiochemResults as $result) {
                $biochemResultsLookup[$result['test_id']][] = $result;
            }

            // Assign results to tests
            foreach ($biochemTests as &$test) {
                $test['results'] = $biochemResultsLookup[$test['id']] ?? [];
            }
        }

        // Get results for all hematology tests in one query
        if (!empty($hematoTests)) {
            $hematoTestIds = array_column($hematoTests, 'id');
            $placeholders = str_repeat('?,', count($hematoTestIds) - 1) . '?';

            $stmtResults = $db->prepare("
                SELECT test_id, parameter_name, value, unit
                FROM hematology_results
                WHERE test_id IN ($placeholders)
            ");
            $stmtResults->execute($hematoTestIds);
            $allHematoResults = $stmtResults->fetchAll(PDO::FETCH_ASSOC);

            // Group results by test_id
            $hematoResultsLookup = [];
            foreach ($allHematoResults as $result) {
                $hematoResultsLookup[$result['test_id']][] = $result;
            }

            // Assign results to tests
            foreach ($hematoTests as &$test) {
                $test['results'] = $hematoResultsLookup[$test['id']] ?? [];
            }
        }

        // Get available reference sources
        $referenceSources = ['Laboklin', 'Idexx', 'Synlab', 'ZIMS'];

        View::render('biochemistry/animal', [
            'layout' => 'main',
            'title' => 'Biochemie a hematologie - ' . $animal['name'],
            'animal' => $animal,
            'biochemTests' => $biochemTests,
            'hematoTests' => $hematoTests,
            'referenceSources' => $referenceSources,
            'canEdit' => $userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit')
        ]);
    }

    public function createTest() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /');
            exit;
        }

        $animalId = $_POST['animal_id'] ?? null;
        $testType = $_POST['test_type'] ?? null;
        $testDate = $_POST['test_date'] ?? null;
        $testLocation = $_POST['test_location'] ?? '';
        $referenceSource = $_POST['reference_source'] ?? 'Idexx';
        $notes = $_POST['notes'] ?? '';

        if (!$animalId || !$testType || !$testDate) {
            $_SESSION['error'] = 'Chybí povinné údaje';
            header('Location: /biochemistry/animal/' . $animalId);
            exit;
        }

        // Check permissions
        $animalModel = new Animal();
        $userModel = new User();
        $animal = $animalModel->findById($animalId);

        if (!$animal || !$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit')) {
            $_SESSION['error'] = 'Nemáte oprávnění k této akci';
            header('Location: /biochemistry/animal/' . $animalId);
            exit;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            // Insert test
            $tableName = $testType === 'biochemistry' ? 'biochemistry_tests' : 'hematology_tests';
            $stmt = $db->prepare("
                INSERT INTO {$tableName}
                (animal_id, test_date, test_location, reference_source, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $animalId,
                $testDate,
                $testLocation,
                $referenceSource,
                $notes,
                Auth::userId()
            ]);

            $testId = $db->lastInsertId();

            // Insert test results (standard parameters)
            $resultsTableName = $testType === 'biochemistry' ? 'biochemistry_results' : 'hematology_results';
            $params = $_POST['params'] ?? [];

            $stmt = $db->prepare("
                INSERT INTO {$resultsTableName}
                (test_id, parameter_name, value, unit)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($params as $paramName => $paramData) {
                if (!empty($paramData['value'])) {
                    $stmt->execute([
                        $testId,
                        $paramName,
                        $paramData['value'],
                        $paramData['unit']
                    ]);
                }
            }

            // Insert custom parameters
            $customParams = $_POST['custom_params'] ?? [];
            foreach ($customParams as $customParam) {
                if (!empty($customParam['name']) && !empty($customParam['value'])) {
                    $stmt->execute([
                        $testId,
                        $customParam['name'],
                        $customParam['value'],
                        $customParam['unit'] ?? ''
                    ]);
                }
            }

            $db->commit();

            $_SESSION['success'] = 'Test byl úspěšně přidán';
            header('Location: /biochemistry/animal/' . $animalId);
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error creating test: " . $e->getMessage());
            $_SESSION['error'] = 'Chyba při ukládání testu: ' . $e->getMessage();
            header('Location: /biochemistry/animal/' . $animalId);
            exit;
        }
    }

    public function comprehensiveTable($animalId) {
        Auth::requireLogin();

        $animalModel = new Animal();
        $userModel = new User();

        // Get animal details
        $animal = $animalModel->getDetail($animalId);
        if (!$animal) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Chyba',
                'message' => 'Zvíře nenalezeno'
            ]);
            return;
        }

        // Check permissions
        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'])) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Přístup odepřen',
                'message' => 'Nemáte oprávnění k tomuto zvířeti'
            ]);
            return;
        }

        $db = Database::getInstance()->getConnection();

        // Get all biochemistry tests ordered by date
        $stmtBiochem = $db->prepare("
            SELECT bt.id, bt.test_date, bt.reference_source
            FROM biochemistry_tests bt
            WHERE bt.animal_id = ?
            ORDER BY bt.test_date ASC
        ");
        $stmtBiochem->execute([$animalId]);
        $biochemTests = $stmtBiochem->fetchAll(PDO::FETCH_ASSOC);

        // Get all hematology tests ordered by date
        $stmtHemato = $db->prepare("
            SELECT ht.id, ht.test_date, ht.reference_source
            FROM hematology_tests ht
            WHERE ht.animal_id = ?
            ORDER BY ht.test_date ASC
        ");
        $stmtHemato->execute([$animalId]);
        $hematoTests = $stmtHemato->fetchAll(PDO::FETCH_ASSOC);

        // Get all unique parameters from both test types
        $allParameters = [];

        // Get unique biochemistry parameters
        $stmtBiochemParams = $db->prepare("
            SELECT DISTINCT parameter_name, unit
            FROM biochemistry_results br
            JOIN biochemistry_tests bt ON br.test_id = bt.id
            WHERE bt.animal_id = ?
            ORDER BY parameter_name ASC
        ");
        $stmtBiochemParams->execute([$animalId]);
        $biochemParams = $stmtBiochemParams->fetchAll(PDO::FETCH_ASSOC);

        foreach ($biochemParams as $param) {
            $allParameters[$param['parameter_name']] = [
                'type' => 'biochemistry',
                'unit' => $param['unit']
            ];
        }

        // Get unique hematology parameters
        $stmtHematoParams = $db->prepare("
            SELECT DISTINCT parameter_name, unit
            FROM hematology_results hr
            JOIN hematology_tests ht ON hr.test_id = ht.id
            WHERE ht.animal_id = ?
            ORDER BY parameter_name ASC
        ");
        $stmtHematoParams->execute([$animalId]);
        $hematoParams = $stmtHematoParams->fetchAll(PDO::FETCH_ASSOC);

        foreach ($hematoParams as $param) {
            $allParameters[$param['parameter_name']] = [
                'type' => 'hematology',
                'unit' => $param['unit']
            ];
        }

        // Get results for each test
        $testResults = [];

        foreach ($biochemTests as &$test) {
            $stmt = $db->prepare("
                SELECT id, parameter_name, value, unit
                FROM biochemistry_results
                WHERE test_id = ?
            ");
            $stmt->execute([$test['id']]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $testResults['biochem_' . $test['id']] = [];
            foreach ($results as $result) {
                $testResults['biochem_' . $test['id']][$result['parameter_name']] = [
                    'value' => $result['value'],
                    'id' => $result['id'],
                    'unit' => $result['unit']
                ];
            }
            $test['key'] = 'biochem_' . $test['id'];
        }

        foreach ($hematoTests as &$test) {
            $stmt = $db->prepare("
                SELECT id, parameter_name, value, unit
                FROM hematology_results
                WHERE test_id = ?
            ");
            $stmt->execute([$test['id']]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $testResults['hemato_' . $test['id']] = [];
            foreach ($results as $result) {
                $testResults['hemato_' . $test['id']][$result['parameter_name']] = [
                    'value' => $result['value'],
                    'id' => $result['id'],
                    'unit' => $result['unit']
                ];
            }
            $test['key'] = 'hemato_' . $test['id'];
        }

        View::render('biochemistry/comprehensive_table', [
            'layout' => 'main',
            'title' => 'Kompletní tabulka - ' . $animal['name'],
            'animal' => $animal,
            'biochemTests' => $biochemTests,
            'hematoTests' => $hematoTests,
            'allParameters' => $allParameters,
            'testResults' => $testResults
        ]);
    }

    public function referenceRanges() {
        Auth::requireLogin();
        Auth::requireAdmin();

        $db = Database::getInstance()->getConnection();

        // Get all unique species from animals table
        $stmt = $db->query("SELECT DISTINCT species FROM animals ORDER BY species ASC");
        $species = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get all reference sources
        try {
            $stmt = $db->query("SELECT source_name FROM reference_sources ORDER BY source_name ASC");
            $sources = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // If no sources exist, use default ones
            if (empty($sources)) {
                $sources = ['Idexx', 'Laboklin', 'Synlab', 'ZIMS'];
            }
        } catch (PDOException $e) {
            // Table doesn't exist yet, use default sources
            $sources = ['Idexx', 'Laboklin', 'Synlab', 'ZIMS'];
        }

        $selectedSpecies = $_GET['species'] ?? '';
        $testType = $_GET['test_type'] ?? 'biochemistry';
        $activeSource = $_GET['source'] ?? ($sources[0] ?? 'Idexx');

        $ranges = [];
        if ($selectedSpecies) {
            $stmt = $db->prepare("
                SELECT * FROM reference_ranges
                WHERE species = ? AND test_type = ?
                ORDER BY parameter_name ASC
            ");
            $stmt->execute([$selectedSpecies, $testType]);
            $ranges = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Parameter definitions (same as in add_test_modal.php)
        $biochemParams = [
            ['name' => 'Amyláza', 'unit' => 'U/L'],
            ['name' => 'Lipáza', 'unit' => 'U/L'],
            ['name' => 'Glukóza', 'unit' => 'mmol/L'],
            ['name' => 'Fruktozamin', 'unit' => 'µmol/L'],
            ['name' => 'Triacylglyceridy', 'unit' => 'mmol/L'],
            ['name' => 'Cholesterol', 'unit' => 'mmol/L'],
            ['name' => 'Bilirubin celkový', 'unit' => 'µmol/L'],
            ['name' => 'ALP', 'unit' => 'U/L'],
            ['name' => 'GLDH', 'unit' => 'U/L'],
            ['name' => 'y-GT', 'unit' => 'U/L'],
            ['name' => 'ALT', 'unit' => 'U/L'],
            ['name' => 'AST', 'unit' => 'U/L'],
            ['name' => 'CK (Kreatinkináza)', 'unit' => 'U/L'],
            ['name' => 'Celková bílkovina', 'unit' => 'g/L'],
            ['name' => 'Albumin', 'unit' => 'g/L'],
            ['name' => 'Globuliny', 'unit' => 'g/L'],
            ['name' => 'A/G poměr', 'unit' => ''],
            ['name' => 'SDMA', 'unit' => 'µg/dL'],
            ['name' => 'Močovina', 'unit' => 'mmol/L'],
            ['name' => 'Kreatinin', 'unit' => 'µmol/L'],
            ['name' => 'Fosfor', 'unit' => 'mmol/L'],
            ['name' => 'Hořčík', 'unit' => 'mmol/L'],
            ['name' => 'Vápník', 'unit' => 'mmol/L'],
            ['name' => 'Chloridy', 'unit' => 'mmol/L'],
            ['name' => 'Sodík', 'unit' => 'mmol/L'],
            ['name' => 'Draslík', 'unit' => 'mmol/L'],
            ['name' => 'Na-/K-kvocient', 'unit' => ''],
            ['name' => 'Železo', 'unit' => 'µmol/L'],
            ['name' => 'T4', 'unit' => 'nmol/L'],
            ['name' => 'FT4', 'unit' => 'pmol/L'],
            ['name' => 'TSH', 'unit' => 'ng/mL'],
        ];

        $hematoParams = [
            ['name' => 'Erytrocyty', 'unit' => '10^12/L'],
            ['name' => 'Hematokrit', 'unit' => '%'],
            ['name' => 'Hemoglobin', 'unit' => 'g/L'],
            ['name' => 'Hypochromazie', 'unit' => '%'],
            ['name' => 'Anizocytoza', 'unit' => '%'],
            ['name' => 'MCHC', 'unit' => 'g/L'],
            ['name' => 'MCH', 'unit' => 'pg'],
            ['name' => 'MCV', 'unit' => 'fL'],
            ['name' => 'Retikulocyty', 'unit' => '%'],
            ['name' => 'IRF', 'unit' => '%'],
            ['name' => 'Ret-He', 'unit' => 'pg'],
            ['name' => 'Leukocyty', 'unit' => '10^9/L'],
            ['name' => 'Neutrofily', 'unit' => '%'],
            ['name' => 'Lymfocyty', 'unit' => '%'],
            ['name' => 'Monocyty', 'unit' => '%'],
            ['name' => 'Eozinofily', 'unit' => '%'],
            ['name' => 'Bazofily', 'unit' => '%'],
            ['name' => 'Tyčky', 'unit' => '%'],
            ['name' => 'Neutrofily - absolutní', 'unit' => '10^9/L'],
            ['name' => 'Lymfocyty - absolutní', 'unit' => '10^9/L'],
            ['name' => 'Monocyty - absolutní', 'unit' => '10^9/L'],
            ['name' => 'Eozinofily - absolutní', 'unit' => '10^9/L'],
            ['name' => 'Bazofily - absolutní', 'unit' => '10^9/L'],
            ['name' => 'Tyčky - absolutní', 'unit' => '10^9/L'],
            ['name' => 'Trombocyty', 'unit' => '10^9/L'],
        ];

        View::render('biochemistry/reference_ranges', [
            'layout' => 'main',
            'title' => 'Správa referenčních hodnot',
            'species' => $species,
            'selectedSpecies' => $selectedSpecies,
            'testType' => $testType,
            'activeSource' => $activeSource,
            'sources' => $sources,
            'ranges' => $ranges,
            'biochemParams' => $biochemParams,
            'hematoParams' => $hematoParams
        ]);
    }

    public function saveReferenceRanges() {
        Auth::requireLogin();
        Auth::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /biochemistry/reference-ranges');
            exit;
        }

        $species = $_POST['species'] ?? null;
        $testType = $_POST['test_type'] ?? null;
        $source = $_POST['source'] ?? null;
        $params = $_POST['params'] ?? [];

        if (!$species || !$testType || !$source) {
            $_SESSION['error'] = 'Chybí povinné údaje';
            header('Location: /biochemistry/reference-ranges');
            exit;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO reference_ranges
                (test_type, parameter_name, species, source, min_value, max_value, unit)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    min_value = VALUES(min_value),
                    max_value = VALUES(max_value),
                    unit = VALUES(unit)
            ");

            $count = 0;
            foreach ($params as $paramName => $paramData) {
                $min = !empty($paramData['min']) ? $paramData['min'] : null;
                $max = !empty($paramData['max']) ? $paramData['max'] : null;
                $unit = $paramData['unit'] ?? '';

                // Only save if at least min or max is provided
                if ($min !== null || $max !== null) {
                    $stmt->execute([
                        $testType,
                        $paramName,
                        $species,
                        $source,
                        $min,
                        $max,
                        $unit
                    ]);
                    $count++;
                }
            }

            $db->commit();

            $_SESSION['success'] = "Úspěšně uloženo {$count} referenčních hodnot";
            header("Location: /biochemistry/reference-ranges?species=" . urlencode($species) . "&test_type={$testType}&source={$source}");
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error saving reference ranges: " . $e->getMessage());
            $_SESSION['error'] = 'Chyba při ukládání referenčních hodnot: ' . $e->getMessage();
            header("Location: /biochemistry/reference-ranges?species=" . urlencode($species) . "&test_type={$testType}&source={$source}");
            exit;
        }
    }

    public function workplaceSearch($workplaceId) {
        Auth::requireLogin();

        $userModel = new User();
        $workplaceModel = new Workplace();
        $animalModel = new Animal();

        // Check permissions
        if (!$userModel->hasPermission(Auth::userId(), $workplaceId)) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Přístup odepřen',
                'message' => 'Nemáte oprávnění k tomuto pracovišti'
            ]);
            return;
        }

        $workplace = $workplaceModel->findById($workplaceId);
        if (!$workplace) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Chyba',
                'message' => 'Pracoviště nenalezeno'
            ]);
            return;
        }

        // Get all animals for this workplace
        $animals = $animalModel->getByWorkplace($workplaceId);

        $db = Database::getInstance()->getConnection();

        // Get all unique parameters from biochemistry for this workplace
        $stmtBiochemParams = $db->prepare("
            SELECT DISTINCT br.parameter_name, br.unit
            FROM biochemistry_results br
            JOIN biochemistry_tests bt ON br.test_id = bt.id
            JOIN animals a ON bt.animal_id = a.id
            WHERE a.workplace_id = ?
            ORDER BY br.parameter_name ASC
        ");
        $stmtBiochemParams->execute([$workplaceId]);
        $biochemParams = $stmtBiochemParams->fetchAll(PDO::FETCH_ASSOC);

        // Get all unique parameters from hematology for this workplace
        $stmtHematoParams = $db->prepare("
            SELECT DISTINCT hr.parameter_name, hr.unit
            FROM hematology_results hr
            JOIN hematology_tests ht ON hr.test_id = ht.id
            JOIN animals a ON ht.animal_id = a.id
            WHERE a.workplace_id = ?
            ORDER BY hr.parameter_name ASC
        ");
        $stmtHematoParams->execute([$workplaceId]);
        $hematoParams = $stmtHematoParams->fetchAll(PDO::FETCH_ASSOC);

        View::render('biochemistry/workplace_search', [
            'layout' => 'main',
            'title' => 'Vyhledávání - ' . $workplace['name'],
            'workplace' => $workplace,
            'animals' => $animals,
            'biochemParams' => $biochemParams,
            'hematoParams' => $hematoParams
        ]);
    }

    public function search($animalId) {
        Auth::requireLogin();

        $animalModel = new Animal();
        $userModel = new User();

        // Get animal details
        $animal = $animalModel->getDetail($animalId);
        if (!$animal) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Chyba',
                'message' => 'Zvíře nenalezeno'
            ]);
            return;
        }

        // Check permissions
        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'])) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Přístup odepřen',
                'message' => 'Nemáte oprávnění k tomuto zvířeti'
            ]);
            return;
        }

        $db = Database::getInstance()->getConnection();

        // Get all unique parameters from biochemistry
        $stmtBiochemParams = $db->prepare("
            SELECT DISTINCT parameter_name, unit
            FROM biochemistry_results br
            JOIN biochemistry_tests bt ON br.test_id = bt.id
            WHERE bt.animal_id = ?
            ORDER BY parameter_name ASC
        ");
        $stmtBiochemParams->execute([$animalId]);
        $biochemParams = $stmtBiochemParams->fetchAll(PDO::FETCH_ASSOC);

        // Get all unique parameters from hematology
        $stmtHematoParams = $db->prepare("
            SELECT DISTINCT parameter_name, unit
            FROM hematology_results hr
            JOIN hematology_tests ht ON hr.test_id = ht.id
            WHERE ht.animal_id = ?
            ORDER BY parameter_name ASC
        ");
        $stmtHematoParams->execute([$animalId]);
        $hematoParams = $stmtHematoParams->fetchAll(PDO::FETCH_ASSOC);

        View::render('biochemistry/search', [
            'layout' => 'main',
            'title' => 'Vyhledávání parametrů - ' . $animal['name'],
            'animal' => $animal,
            'biochemParams' => $biochemParams,
            'hematoParams' => $hematoParams
        ]);
    }

    public function graph($animalId) {
        Auth::requireLogin();

        $animalModel = new Animal();
        $userModel = new User();

        // Get animal details
        $animal = $animalModel->getDetail($animalId);
        if (!$animal) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Chyba',
                'message' => 'Zvíře nenalezeno'
            ]);
            return;
        }

        // Check permissions
        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'])) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Přístup odepřen',
                'message' => 'Nemáte oprávnění k tomuto zvířeti'
            ]);
            return;
        }

        // Get parameters from POST
        $parametersJson = $_POST['parameters'] ?? '[]';
        $parameters = json_decode($parametersJson, true);
        $sampleCount = (int)($_POST['sample_count'] ?? 5);
        $referenceSource = $_POST['reference_source'] ?? '';

        if (empty($parameters)) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Chyba',
                'message' => 'Nebyly vybrány žádné parametry'
            ]);
            return;
        }

        $db = Database::getInstance()->getConnection();

        // Fetch reference ranges if source is selected
        $referenceRanges = [];
        if (!empty($referenceSource)) {
            $paramNames = array_column($parameters, 'name');
            $placeholders = str_repeat('?,', count($paramNames) - 1) . '?';

            $stmtRanges = $db->prepare("
                SELECT parameter_name, test_type, min_value, max_value, unit
                FROM reference_ranges
                WHERE species = ? AND source = ? AND parameter_name IN ($placeholders)
            ");

            $queryParams = array_merge([$animal['species'], $referenceSource], $paramNames);
            $stmtRanges->execute($queryParams);
            $ranges = $stmtRanges->fetchAll(PDO::FETCH_ASSOC);

            // Organize by parameter name and test type
            foreach ($ranges as $range) {
                $key = $range['parameter_name'] . '_' . $range['test_type'];
                $referenceRanges[$key] = [
                    'min' => $range['min_value'],
                    'max' => $range['max_value'],
                    'unit' => $range['unit']
                ];
            }
        }

        // Fetch data for each parameter
        $graphData = [];

        foreach ($parameters as $param) {
            $paramName = $param['name'];
            $type = $param['type'];
            $color = $param['color'];

            if ($type === 'biochemistry') {
                $stmt = $db->prepare("
                    SELECT bt.test_date, br.value
                    FROM biochemistry_results br
                    JOIN biochemistry_tests bt ON br.test_id = bt.id
                    WHERE bt.animal_id = ? AND br.parameter_name = ?
                    ORDER BY bt.test_date DESC
                    LIMIT ?
                ");
            } else {
                $stmt = $db->prepare("
                    SELECT ht.test_date, hr.value
                    FROM hematology_results hr
                    JOIN hematology_tests ht ON hr.test_id = ht.id
                    WHERE ht.animal_id = ? AND hr.parameter_name = ?
                    ORDER BY ht.test_date DESC
                    LIMIT ?
                ");
            }

            $stmt->execute([$animalId, $paramName, $sampleCount]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Reverse to show oldest to newest
            $results = array_reverse($results);

            $graphData[] = [
                'name' => $paramName,
                'type' => $type,
                'color' => $color,
                'data' => $results
            ];
        }

        View::render('biochemistry/graph', [
            'layout' => 'main',
            'title' => 'Graf parametrů - ' . $animal['name'],
            'animal' => $animal,
            'graphData' => $graphData,
            'sampleCount' => $sampleCount,
            'referenceRanges' => $referenceRanges,
            'referenceSource' => $referenceSource
        ]);
    }

    public function updateResult($resultId) {
        Auth::requireLogin();

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Pouze POST metoda']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $newValue = $input['value'] ?? null;
        $testType = $input['test_type'] ?? null;

        if ($newValue === null || $testType === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Chybí povinné parametry']);
            return;
        }

        if (!is_numeric($newValue)) {
            http_response_code(400);
            echo json_encode(['error' => 'Hodnota musí být číslo']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            // Determine the correct table based on test type
            $table = ($testType === 'biochemistry') ? 'biochemistry_results' : 'hematology_results';

            // First, get the result to verify it exists and get the test_id to check permissions
            $stmtCheck = $db->prepare("
                SELECT r.*, t.animal_id
                FROM {$table} r
                JOIN " . ($testType === 'biochemistry' ? 'biochemistry_tests' : 'hematology_tests') . " t ON r.test_id = t.id
                WHERE r.id = ?
            ");
            $stmtCheck->execute([$resultId]);
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                http_response_code(404);
                echo json_encode(['error' => 'Výsledek nenalezen']);
                return;
            }

            // Get animal to check workplace permissions
            $animalModel = new Animal();
            $animal = $animalModel->findById($result['animal_id']);

            if (!$animal) {
                http_response_code(404);
                echo json_encode(['error' => 'Zvíře nenalezeno']);
                return;
            }

            // Check permissions
            $userModel = new User();
            if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit')) {
                http_response_code(403);
                echo json_encode(['error' => 'Nemáte oprávnění editovat tento výsledek']);
                return;
            }

            // Update the value
            $stmtUpdate = $db->prepare("
                UPDATE {$table}
                SET value = ?
                WHERE id = ?
            ");
            $stmtUpdate->execute([$newValue, $resultId]);

            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Hodnota byla úspěšně aktualizována']);

        } catch (Exception $e) {
            error_log("BiochemistryController::updateResult error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Chyba při aktualizaci hodnoty: ' . $e->getMessage()]);
        }
    }

    public function advancedSearch($workplaceId) {
        Auth::requireLogin();

        $userModel = new User();
        $workplaceModel = new Workplace();

        // Check permissions
        if (!$userModel->hasPermission(Auth::userId(), $workplaceId)) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Přístup odepřen',
                'message' => 'Nemáte oprávnění k tomuto pracovišti'
            ]);
            return;
        }

        $workplace = $workplaceModel->findById($workplaceId);
        if (!$workplace) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Chyba',
                'message' => 'Pracoviště nenalezeno'
            ]);
            return;
        }

        // Get all unique parameters from both biochemistry and hematology
        $db = Database::getInstance()->getConnection();

        // Get biochemistry parameters
        $stmtBiochem = $db->prepare("
            SELECT DISTINCT parameter_name, unit
            FROM biochemistry_results
            ORDER BY parameter_name
        ");
        $stmtBiochem->execute();
        $biochemParams = $stmtBiochem->fetchAll(PDO::FETCH_ASSOC);

        // Get hematology parameters
        $stmtHemato = $db->prepare("
            SELECT DISTINCT parameter_name, unit
            FROM hematology_results
            ORDER BY parameter_name
        ");
        $stmtHemato->execute();
        $hematoParams = $stmtHemato->fetchAll(PDO::FETCH_ASSOC);

        // Get all reference sources
        $stmtSources = $db->prepare("
            SELECT DISTINCT reference_source
            FROM biochemistry_tests
            WHERE reference_source IS NOT NULL
            UNION
            SELECT DISTINCT reference_source
            FROM hematology_tests
            WHERE reference_source IS NOT NULL
            ORDER BY reference_source
        ");
        $stmtSources->execute();
        $referenceSources = $stmtSources->fetchAll(PDO::FETCH_COLUMN);

        View::render('biochemistry/advanced_search', [
            'layout' => 'main',
            'title' => 'Pokročilé vyhledávání',
            'workplace' => $workplace,
            'biochemParams' => $biochemParams,
            'hematoParams' => $hematoParams,
            'referenceSources' => $referenceSources
        ]);
    }

    public function searchAnimalApi() {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $workplaceId = $_GET['workplace_id'] ?? null;
        $query = $_GET['query'] ?? null;

        if (!$workplaceId || !$query) {
            http_response_code(400);
            echo json_encode([
                'error' => 'Missing required parameters',
                'debug' => [
                    'workplace_id' => $workplaceId,
                    'query' => $query
                ]
            ]);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("
                SELECT
                    a.id,
                    a.identifier,
                    a.name,
                    a.species,
                    e.name as enclosure_name
                FROM animals a
                LEFT JOIN enclosures e ON a.current_enclosure_id = e.id
                WHERE a.workplace_id = ?
                  AND (LOWER(a.name) LIKE LOWER(?) OR LOWER(a.identifier) LIKE LOWER(?))
                ORDER BY a.name
            ");

            $searchTerm = '%' . $query . '%';
            $stmt->execute([$workplaceId, $searchTerm, $searchTerm]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'results' => $results,
                'debug' => [
                    'workplace_id' => $workplaceId,
                    'query' => $query,
                    'search_term' => $searchTerm,
                    'count' => count($results)
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function searchParameterApi() {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $workplaceId = $_GET['workplace_id'] ?? null;
        $paramType = $_GET['param_type'] ?? null;
        $paramName = $_GET['param_name'] ?? null;
        $direction = $_GET['direction'] ?? null;
        $refSource = $_GET['ref_source'] ?? null;

        if (!$workplaceId || !$paramType || !$paramName || !$direction) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            // Determine table names based on param type
            $resultsTable = $paramType === 'biochemistry' ? 'biochemistry_results' : 'hematology_results';
            $testsTable = $paramType === 'biochemistry' ? 'biochemistry_tests' : 'hematology_tests';

            // Build query
            $sql = "
                SELECT DISTINCT
                    a.id as animal_id,
                    a.identifier,
                    a.name as animal_name,
                    a.species,
                    r.value,
                    r.unit,
                    t.test_date,
                    t.reference_source,
                    ref.min_value,
                    ref.max_value
                FROM {$resultsTable} r
                JOIN {$testsTable} t ON r.test_id = t.id
                JOIN animals a ON t.animal_id = a.id
                LEFT JOIN reference_ranges ref ON
                    ref.test_type = ?
                    AND ref.parameter_name COLLATE utf8mb4_unicode_ci = r.parameter_name COLLATE utf8mb4_unicode_ci
                    AND ref.species COLLATE utf8mb4_unicode_ci = a.species COLLATE utf8mb4_unicode_ci
                WHERE a.workplace_id = ?
                  AND r.parameter_name = ?
                  AND ref.min_value IS NOT NULL
                  AND ref.max_value IS NOT NULL
            ";

            $params = [$paramType, $workplaceId, $paramName];

            if ($refSource) {
                $sql .= " AND t.reference_source = ?";
                $params[] = $refSource;
            }

            // Add direction condition
            if ($direction === 'elevated') {
                $sql .= " AND r.value > ref.max_value";
            } elseif ($direction === 'decreased') {
                $sql .= " AND r.value < ref.min_value";
            } elseif ($direction === 'both') {
                $sql .= " AND (r.value > ref.max_value OR r.value < ref.min_value)";
            }

            $sql .= " ORDER BY t.test_date DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Calculate deviations
            foreach ($results as &$result) {
                $value = floatval($result['value']);
                $min = floatval($result['min_value']);
                $max = floatval($result['max_value']);

                if ($value > $max) {
                    $result['status'] = 'high';
                    $result['deviation'] = number_format((($value - $max) / $max) * 100, 2);
                } elseif ($value < $min) {
                    $result['status'] = 'low';
                    $result['deviation'] = number_format((($min - $value) / $min) * 100, 2);
                } else {
                    $result['status'] = 'normal';
                    $result['deviation'] = '0.00';
                }
            }

            echo json_encode([
                'results' => $results,
                'debug' => [
                    'sql' => $sql,
                    'params' => $params,
                    'count' => count($results)
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Database error: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function addReferenceSource() {
        Auth::requireLogin();

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Pouze POST metoda']);
            return;
        }

        // Check if user is admin
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění přidávat referenční zdroje']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $sourceName = trim($input['source_name'] ?? '');

        if (empty($sourceName)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Název zdroje je povinný']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            // Check if source already exists
            $stmt = $db->prepare("
                SELECT COUNT(*) as count
                FROM reference_sources
                WHERE source_name = ?
            ");
            $stmt->execute([$sourceName]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['count'] > 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Tento zdroj již existuje']);
                return;
            }

            // Insert new source
            $stmt = $db->prepare("
                INSERT INTO reference_sources (source_name, created_at)
                VALUES (?, NOW())
            ");
            $stmt->execute([$sourceName]);

            echo json_encode(['success' => true, 'message' => 'Referenční zdroj byl úspěšně přidán']);
        } catch (Exception $e) {
            error_log("BiochemistryController::addReferenceSource error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba databáze: ' . $e->getMessage()]);
        }
    }
}
