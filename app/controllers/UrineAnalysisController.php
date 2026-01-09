<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/Animal.php';

class UrineAnalysisController {

    public function index() {
        Auth::requireLogin();

        // Get user's workplaces
        $userModel = new User();
        $workplaceModel = new Workplace();

        $workplaces = $userModel->getWorkplacePermissions(Auth::userId());

        View::render('urineanalysis/dashboard', [
            'layout' => 'main',
            'title' => 'Analýza moči - Dashboard',
            'workplaces' => $workplaces
        ]);
    }

    public function workplace($id) {
        Auth::requireLogin();

        $userModel = new User();
        $workplaceModel = new Workplace();

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

        // Check if user can edit
        $canEdit = Auth::role() === 'admin' || $userModel->hasPermission(Auth::userId(), $id, 'edit');

        // Get animals for this workplace
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT
                a.id,
                a.identifier,
                a.name,
                a.species,
                e.name as enclosure_name,
                MAX(ut.test_date) as last_test_date
            FROM animals a
            LEFT JOIN enclosures e ON a.current_enclosure_id = e.id
            LEFT JOIN urine_tests ut ON a.id = ut.animal_id
            WHERE a.workplace_id = ?
            GROUP BY a.id, a.identifier, a.name, a.species, e.name
            ORDER BY a.name ASC
        ");
        $stmt->execute([$id]);
        $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        View::render('urineanalysis/workplace', [
            'layout' => 'main',
            'title' => 'Analýza moči - ' . $workplace['name'],
            'workplace' => $workplace,
            'animals' => $animals,
            'canEdit' => $canEdit
        ]);
    }

    public function animal($id) {
        Auth::requireLogin();

        require_once __DIR__ . '/../models/Animal.php';

        $animalModel = new Animal();
        $userModel = new User();

        // Get animal with workplace info
        $animal = $animalModel->findById($id);
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
                'message' => 'Nemáte oprávnění k tomuto pracovišti'
            ]);
            return;
        }

        // Check if user can edit
        $canEdit = Auth::role() === 'admin' || $userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit');

        // Get urine tests for this animal
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT
                ut.id,
                ut.test_date,
                ut.test_location,
                ut.reference_source,
                ut.notes,
                u.full_name as created_by_name
            FROM urine_tests ut
            LEFT JOIN users u ON ut.created_by = u.id
            WHERE ut.animal_id = ?
            ORDER BY ut.test_date DESC
        ");
        $stmt->execute([$id]);
        $urineTests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get results for each test with reference ranges
        foreach ($urineTests as &$test) {
            $stmt = $db->prepare("
                SELECT id, parameter_name, value, unit
                FROM urine_results
                WHERE test_id = ?
                ORDER BY parameter_name
            ");
            $stmt->execute([$test['id']]);
            $test['results'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get reference ranges for this test's reference source and animal species
            $stmt = $db->prepare("
                SELECT parameter_name, reference_text, min_value, max_value, unit
                FROM urine_reference_ranges
                WHERE species = ? AND reference_source = ?
            ");
            $stmt->execute([$animal['species'], $test['reference_source']]);
            $referenceRanges = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Create a lookup array for reference ranges
            $refLookup = [];
            foreach ($referenceRanges as $ref) {
                $refLookup[$ref['parameter_name']] = $ref;
            }

            // Add reference range and evaluation to each result
            foreach ($test['results'] as &$result) {
                $ref = $refLookup[$result['parameter_name']] ?? null;
                $result['reference_range'] = null;
                $result['evaluation'] = null;

                if ($ref) {
                    // Check if it's a text-based reference (qualitative)
                    if (!empty($ref['reference_text'])) {
                        $result['reference_range'] = $ref['reference_text'];
                        // For text values, simple match check
                        $normalizedValue = strtolower(trim($result['value']));
                        $normalizedRef = strtolower(trim($ref['reference_text']));
                        if ($normalizedValue === $normalizedRef ||
                            ($normalizedRef === 'negativní' && in_array($normalizedValue, ['neg.', 'neg', 'negativní', 'negative']))) {
                            $result['evaluation'] = 'V normě';
                        } else {
                            $result['evaluation'] = 'Mimo normu';
                        }
                    }
                    // Numeric range
                    elseif ($ref['min_value'] !== null || $ref['max_value'] !== null) {
                        $minVal = $ref['min_value'];
                        $maxVal = $ref['max_value'];

                        if ($minVal !== null && $maxVal !== null) {
                            $result['reference_range'] = $minVal . ' - ' . $maxVal;
                        } elseif ($minVal !== null) {
                            $result['reference_range'] = '> ' . $minVal;
                        } elseif ($maxVal !== null) {
                            $result['reference_range'] = '< ' . $maxVal;
                        }

                        // Calculate evaluation for numeric values
                        $numValue = floatval(str_replace(',', '.', $result['value']));
                        if (is_numeric($numValue)) {
                            if ($minVal !== null && $maxVal !== null) {
                                if ($numValue < $minVal) {
                                    // Below range
                                    if ($minVal != 0) {
                                        $deviation = (($minVal - $numValue) / $minVal) * 100;
                                        $result['evaluation'] = '↓ ' . round($deviation, 1) . '%';
                                    } else {
                                        $result['evaluation'] = '↓ Nízké';
                                    }
                                    $result['evaluation_class'] = 'low';
                                } elseif ($numValue > $maxVal) {
                                    // Above range
                                    if ($maxVal != 0) {
                                        $deviation = (($numValue - $maxVal) / $maxVal) * 100;
                                        $result['evaluation'] = '↑ ' . round($deviation, 1) . '%';
                                    } else {
                                        // When max is 0 and value is above, calculate absolute difference
                                        $result['evaluation'] = '↑ ' . $numValue;
                                    }
                                    $result['evaluation_class'] = 'high';
                                } else {
                                    // Within range
                                    $result['evaluation'] = 'OK';
                                    $result['evaluation_class'] = 'ok';
                                }
                            } elseif ($minVal !== null) {
                                if ($numValue < $minVal) {
                                    $result['evaluation'] = '↓ Nízké';
                                    $result['evaluation_class'] = 'low';
                                } else {
                                    $result['evaluation'] = 'OK';
                                    $result['evaluation_class'] = 'ok';
                                }
                            } elseif ($maxVal !== null) {
                                if ($numValue > $maxVal) {
                                    $result['evaluation'] = '↑ Vysoké';
                                    $result['evaluation_class'] = 'high';
                                } else {
                                    $result['evaluation'] = 'OK';
                                    $result['evaluation_class'] = 'ok';
                                }
                            } else {
                                $result['evaluation'] = '-';
                                $result['evaluation_class'] = '';
                            }
                        }
                    }

                    // Set evaluation class for text-based references
                    if (!isset($result['evaluation_class'])) {
                        if ($result['evaluation'] === 'V normě') {
                            $result['evaluation_class'] = 'ok';
                        } elseif ($result['evaluation'] === 'Mimo normu') {
                            $result['evaluation_class'] = 'abnormal';
                        } else {
                            $result['evaluation_class'] = '';
                        }
                    }
                }
            }
        }

        // Get reference sources
        $referenceSources = ['Idexx', 'Laboklin', 'Synlab', 'ZIMS'];

        View::render('urineanalysis/animal', [
            'layout' => 'main',
            'title' => 'Analýza moči - ' . $animal['name'],
            'animal' => $animal,
            'urineTests' => $urineTests,
            'canEdit' => $canEdit,
            'referenceSources' => $referenceSources
        ]);
    }

    public function comprehensiveTable($animalId) {
        Auth::requireLogin();

        require_once __DIR__ . '/../models/Animal.php';

        $animalModel = new Animal();
        $userModel = new User();

        // Get animal details
        $animal = $animalModel->findById($animalId);
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

        // Get all urine tests ordered by date
        $stmt = $db->prepare("
            SELECT ut.id, ut.test_date, ut.reference_source
            FROM urine_tests ut
            WHERE ut.animal_id = ?
            ORDER BY ut.test_date ASC
        ");
        $stmt->execute([$animalId]);
        $urineTests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all unique parameters
        $stmtParams = $db->prepare("
            SELECT DISTINCT parameter_name, unit
            FROM urine_results ur
            JOIN urine_tests ut ON ur.test_id = ut.id
            WHERE ut.animal_id = ?
            ORDER BY parameter_name ASC
        ");
        $stmtParams->execute([$animalId]);
        $allParameters = [];

        foreach ($stmtParams->fetchAll(PDO::FETCH_ASSOC) as $param) {
            $allParameters[$param['parameter_name']] = [
                'unit' => $param['unit']
            ];
        }

        // Get reference ranges for default source (Synlab) and this species
        $stmtRef = $db->prepare("
            SELECT parameter_name, reference_text, min_value, max_value
            FROM urine_reference_ranges
            WHERE species = ? AND reference_source = 'Synlab'
        ");
        $stmtRef->execute([$animal['species']]);
        $referenceRanges = [];

        foreach ($stmtRef->fetchAll(PDO::FETCH_ASSOC) as $ref) {
            $referenceRanges[$ref['parameter_name']] = $ref;
        }

        // Get results for each test
        $testResults = [];

        foreach ($urineTests as &$test) {
            $stmt = $db->prepare("
                SELECT id, parameter_name, value, unit
                FROM urine_results
                WHERE test_id = ?
            ");
            $stmt->execute([$test['id']]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $testResults['urine_' . $test['id']] = [];
            foreach ($results as $result) {
                $testResults['urine_' . $test['id']][$result['parameter_name']] = [
                    'value' => $result['value'],
                    'id' => $result['id'],
                    'unit' => $result['unit']
                ];
            }
            $test['key'] = 'urine_' . $test['id'];
        }

        View::render('urineanalysis/comprehensive_table', [
            'layout' => 'main',
            'title' => 'Kompletní tabulka - ' . $animal['name'],
            'animal' => $animal,
            'urineTests' => $urineTests,
            'allParameters' => $allParameters,
            'testResults' => $testResults,
            'referenceRanges' => $referenceRanges
        ]);
    }

    public function createTest() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('HTTP/1.1 405 Method Not Allowed');
            die('Method not allowed');
        }

        $animalId = $_POST['animal_id'] ?? null;
        $testDate = $_POST['test_date'] ?? null;
        $testLocation = $_POST['test_location'] ?? '';
        $referenceSource = $_POST['reference_source'] ?? null;
        $notes = $_POST['notes'] ?? '';
        $params = $_POST['params'] ?? [];

        if (!$animalId || !$testDate || !$referenceSource) {
            $_SESSION['error'] = 'Chybí povinné údaje';
            header('Location: /urineanalysis/animal/' . $animalId);
            exit;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            // Insert test
            $stmt = $db->prepare("
                INSERT INTO urine_tests (animal_id, test_date, test_location, reference_source, notes, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$animalId, $testDate, $testLocation, $referenceSource, $notes, Auth::userId()]);
            $testId = $db->lastInsertId();

            // Insert results
            foreach ($params as $paramName => $paramData) {
                if (!empty($paramData['value'])) {
                    $stmt = $db->prepare("
                        INSERT INTO urine_results (test_id, parameter_name, value, unit)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$testId, $paramName, $paramData['value'], $paramData['unit']]);
                }
            }

            $db->commit();
            $_SESSION['success'] = 'Test byl úspěšně přidán';
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Chyba při ukládání testu: ' . $e->getMessage();
        }

        header('Location: /urineanalysis/animal/' . $animalId);
        exit;
    }

    public function referenceRanges() {
        Auth::requireLogin();

        // Only admins and users with edit permissions can manage reference ranges
        if (Auth::role() !== 'admin') {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Přístup odepřen',
                'message' => 'Pouze administrátoři mohou spravovat referenční hodnoty'
            ]);
            return;
        }

        // Get filter parameters
        $filterSpecies = $_GET['species'] ?? '';
        $filterSource = $_GET['source'] ?? 'Synlab';

        // Get all unique species from animals
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT DISTINCT species FROM animals ORDER BY species");
        $allSpecies = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Get reference ranges based on filters
        $query = "SELECT * FROM urine_reference_ranges WHERE 1=1";
        $params = [];

        if ($filterSpecies) {
            $query .= " AND species = ?";
            $params[] = $filterSpecies;
        }

        if ($filterSource) {
            $query .= " AND reference_source = ?";
            $params[] = $filterSource;
        }

        $query .= " ORDER BY species, parameter_name";

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $referenceRanges = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get all available reference sources
        $referenceSources = ['Idexx', 'Laboklin', 'Synlab', 'ZIMS'];

        // Get all standard parameters from the modal
        $standardParameters = [
            'Moč chemicky' => [
                'Glukóza',
                'Bílkovina',
                'Bilirubin',
                'Urobilinogen',
                'pH',
                'Krev',
                'Ketony',
                'Nitrity',
                'Leukocyty',
                'Specifická hustota'
            ],
            'Močový sediment' => [
                'Erytrocyty elementy',
                'Erytrocyty',
                'Leukocyty elementy',
                'Leukocyty',
                'Bakterie',
                'Drť',
                'Hlen'
            ],
            'Močové parametry' => [
                'Albumin - moč',
                'Kreatinin - moč',
                'Albumin/Kreatinin - moč',
                'Bílkovina - moč',
                'Bílkovina/Kreatinin - moč'
            ]
        ];

        View::render('urineanalysis/reference_ranges', [
            'layout' => 'main',
            'title' => 'Správa referenčních hodnot - Analýza moči',
            'referenceRanges' => $referenceRanges,
            'allSpecies' => $allSpecies,
            'referenceSources' => $referenceSources,
            'filterSpecies' => $filterSpecies,
            'filterSource' => $filterSource,
            'standardParameters' => $standardParameters
        ]);
    }

    public function saveReferenceRanges() {
        Auth::requireLogin();

        if (Auth::role() !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nedostatečná oprávnění']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $ranges = $input['ranges'] ?? [];

        if (empty($ranges)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Žádná data k uložení']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            foreach ($ranges as $range) {
                $species = $range['species'] ?? '';
                $source = $range['source'] ?? '';
                $paramName = $range['parameter'] ?? '';
                $referenceText = $range['referenceText'] ?? '';
                $minValue = $range['min'] !== '' ? $range['min'] : null;
                $maxValue = $range['max'] !== '' ? $range['max'] : null;
                $unit = $range['unit'] ?? '';

                if (empty($species) || empty($source) || empty($paramName)) {
                    continue;
                }

                // Insert or update reference range
                $stmt = $db->prepare("
                    INSERT INTO urine_reference_ranges
                    (species, reference_source, parameter_name, reference_text, min_value, max_value, unit)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    reference_text = VALUES(reference_text),
                    min_value = VALUES(min_value),
                    max_value = VALUES(max_value),
                    unit = VALUES(unit)
                ");
                $stmt->execute([$species, $source, $paramName, $referenceText, $minValue, $maxValue, $unit]);
            }

            $db->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $db->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba databáze: ' . $e->getMessage()]);
        }
    }

    public function updateResult($resultId) {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method not allowed']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $newValue = $input['value'] ?? null;

        if ($newValue === null) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Missing value']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            // Update the result
            $stmt = $db->prepare("
                UPDATE urine_results
                SET value = ?
                WHERE id = ?
            ");
            $stmt->execute([$newValue, $resultId]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
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

        // Get all unique parameters from urine analysis for this workplace
        $stmtUrineParams = $db->prepare("
            SELECT DISTINCT ur.parameter_name, ur.unit
            FROM urine_results ur
            JOIN urine_tests ut ON ur.test_id = ut.id
            JOIN animals a ON ut.animal_id = a.id
            WHERE a.workplace_id = ?
            ORDER BY ur.parameter_name ASC
        ");
        $stmtUrineParams->execute([$workplaceId]);
        $urineParams = $stmtUrineParams->fetchAll(PDO::FETCH_ASSOC);

        View::render('urineanalysis/workplace_search', [
            'layout' => 'main',
            'title' => 'Vytvoření grafu - ' . $workplace['name'],
            'workplace' => $workplace,
            'animals' => $animals,
            'urineParams' => $urineParams
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

        // Get all unique parameters from urine analysis
        $stmtParams = $db->prepare("
            SELECT DISTINCT parameter_name, unit
            FROM urine_results ur
            JOIN urine_tests ut ON ur.test_id = ut.id
            WHERE ut.animal_id = ?
            ORDER BY parameter_name ASC
        ");
        $stmtParams->execute([$animalId]);
        $urineParams = $stmtParams->fetchAll(PDO::FETCH_ASSOC);

        View::render('urineanalysis/search', [
            'layout' => 'main',
            'title' => 'Vyhledávání parametrů - ' . $animal['name'],
            'animal' => $animal,
            'urineParams' => $urineParams
        ]);
    }

    public function showGraph($animalId) {
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
                SELECT parameter_name, min_value, max_value, unit
                FROM urine_reference_ranges
                WHERE species = ? AND reference_source = ? AND parameter_name IN ($placeholders)
            ");

            $queryParams = array_merge([$animal['species'], $referenceSource], $paramNames);
            $stmtRanges->execute($queryParams);
            $ranges = $stmtRanges->fetchAll(PDO::FETCH_ASSOC);

            // Organize by parameter name
            foreach ($ranges as $range) {
                $referenceRanges[$range['parameter_name']] = [
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
            $color = $param['color'];

            $stmt = $db->prepare("
                SELECT ut.test_date, ur.value
                FROM urine_results ur
                JOIN urine_tests ut ON ur.test_id = ut.id
                WHERE ut.animal_id = ? AND ur.parameter_name = ?
                ORDER BY ut.test_date DESC
                LIMIT ?
            ");

            $stmt->execute([$animalId, $paramName, $sampleCount]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Reverse to show oldest to newest
            $results = array_reverse($results);

            $graphData[] = [
                'name' => $paramName,
                'color' => $color,
                'data' => $results
            ];
        }

        View::render('urineanalysis/graph', [
            'layout' => 'main',
            'title' => 'Graf parametrů - ' . $animal['name'],
            'animal' => $animal,
            'graphData' => $graphData,
            'sampleCount' => $sampleCount,
            'referenceRanges' => $referenceRanges,
            'referenceSource' => $referenceSource
        ]);
    }
}
