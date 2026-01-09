<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Workplace.php';

class AnimalController {
    
    public function list($workplaceId) {
        Auth::requireLogin();

        $userModel = new User();
        if (!$userModel->hasPermission(Auth::userId(), $workplaceId, 'view')) {
            die('Nemáte oprávnění k tomuto pracovišti');
        }

        $workplaceModel = new Workplace();
        $workplace = $workplaceModel->findById($workplaceId);
        $enclosures = $workplaceModel->getEnclosures($workplaceId);

        // Získat filtry z URL
        $filters = [
            'status' => $_GET['status'] ?? '',
            'enclosure_id' => $_GET['enclosure_id'] ?? '',
            'species' => $_GET['species'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        $animalModel = new Animal();
        $animals = $animalModel->getExaminationHistory($workplaceId, $filters);

        $canEdit = $userModel->hasPermission(Auth::userId(), $workplaceId, 'edit');

        View::render('animals/list', [
            'layout' => 'main',
            'title' => 'Přehled zvířat - ' . $workplace['name'],
            'workplace' => $workplace,
            'animals' => $animals,
            'enclosures' => $enclosures,
            'filters' => $filters,
            'canEdit' => $canEdit
        ]);
    }
    
    public function detail($id) {
        Auth::requireLogin();

        // DEBUG: Log what ID we received
        error_log("AnimalController::detail() - Received ID: " . var_export($id, true));

        $animalModel = new Animal();
        $animal = $animalModel->getDetail($id);

        if (!$animal) {
            die('Zvíře nenalezeno');
        }

        $userModel = new User();
        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'view')) {
            die('Nemáte oprávnění k tomuto pracovišti');
        }

        $canEdit = $userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit');

        // Check if viewing from biochemistry or urine analysis section
        $fromBiochemistry = ($_GET['from'] ?? '') === 'biochemistry';
        $fromUrineAnalysis = ($_GET['from'] ?? '') === 'urineanalysis';

        if ($fromUrineAnalysis) {
            // Fetch urine tests
            require_once __DIR__ . '/../core/Database.php';
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("
                SELECT ut.*, u.full_name as created_by_name
                FROM urine_tests ut
                LEFT JOIN users u ON ut.created_by = u.id
                WHERE ut.animal_id = ?
                ORDER BY ut.test_date DESC
            ");
            $stmt->execute([$id]);
            $urineTests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get results for all urine tests in one query
            if (!empty($urineTests)) {
                $urineTestIds = array_column($urineTests, 'id');
                $placeholders = str_repeat('?,', count($urineTestIds) - 1) . '?';

                $stmt = $db->prepare("
                    SELECT test_id, id, parameter_name, value, unit
                    FROM urine_results
                    WHERE test_id IN ($placeholders)
                    ORDER BY parameter_name
                ");
                $stmt->execute($urineTestIds);
                $allUrineResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Group results by test_id
                $urineResultsLookup = [];
                foreach ($allUrineResults as $result) {
                    $urineResultsLookup[$result['test_id']][] = $result;
                }

                // Assign results to tests
                foreach ($urineTests as &$test) {
                    $test['results'] = $urineResultsLookup[$test['id']] ?? [];
                }
            }

            // Fetch enclosures for editing
            $workplaceModel = new Workplace();
            $enclosures = $workplaceModel->getEnclosures($animal['workplace_id']);

            // Fetch all workplaces for transfer dropdown (excluding current workplace)
            $allWorkplaces = $workplaceModel->getAll();
            $transferWorkplaces = array_filter($allWorkplaces, function($wp) use ($animal) {
                return $wp['id'] != $animal['workplace_id'];
            });

            // Get reference sources
            $referenceSources = ['Idexx', 'Laboklin', 'Synlab', 'ZIMS'];

            View::render('animals/detail_urineanalysis', [
                'layout' => 'main',
                'title' => $animal['name'] ?? 'Zvíře #' . $animal['identifier'],
                'animal' => $animal,
                'urineTests' => $urineTests,
                'canEdit' => $canEdit,
                'enclosures' => $enclosures,
                'transferWorkplaces' => $transferWorkplaces,
                'referenceSources' => $referenceSources
            ]);
        } elseif ($fromBiochemistry) {
            // Fetch biochemistry and hematology tests
            require_once __DIR__ . '/../core/Database.php';
            $db = Database::getInstance()->getConnection();

            // Get biochemistry tests
            $stmtBiochem = $db->prepare("
                SELECT bt.*, u.full_name as created_by_name
                FROM biochemistry_tests bt
                LEFT JOIN users u ON bt.created_by = u.id
                WHERE bt.animal_id = ?
                ORDER BY bt.test_date DESC
            ");
            $stmtBiochem->execute([$id]);
            $biochemTests = $stmtBiochem->fetchAll(PDO::FETCH_ASSOC);

            // Get hematology tests
            $stmtHemato = $db->prepare("
                SELECT ht.*, u.full_name as created_by_name
                FROM hematology_tests ht
                LEFT JOIN users u ON ht.created_by = u.id
                WHERE ht.animal_id = ?
                ORDER BY ht.test_date DESC
            ");
            $stmtHemato->execute([$id]);
            $hematoTests = $stmtHemato->fetchAll(PDO::FETCH_ASSOC);

            // Fetch enclosures for editing
            $workplaceModel = new Workplace();
            $enclosures = $workplaceModel->getEnclosures($animal['workplace_id']);

            // Fetch all workplaces for transfer dropdown (excluding current workplace)
            $allWorkplaces = $workplaceModel->getAll();
            $transferWorkplaces = array_filter($allWorkplaces, function($wp) use ($animal) {
                return $wp['id'] != $animal['workplace_id'];
            });

            View::render('animals/detail_biochemistry', [
                'layout' => 'main',
                'title' => $animal['name'] ?? 'Zvíře #' . $animal['identifier'],
                'animal' => $animal,
                'biochemTests' => $biochemTests,
                'hematoTests' => $hematoTests,
                'canEdit' => $canEdit,
                'enclosures' => $enclosures,
                'transferWorkplaces' => $transferWorkplaces
            ]);
        } else {
            // Original parasitology view
            $examinations = $animalModel->getExaminations($id);
            $dewormings = $animalModel->getDewormings($id);
            $scheduledChecks = $animalModel->getScheduledChecks($id, 'pending');
            $statusHistory = $animalModel->getStatusHistory($id);

            // Fetch all workplaces for transfer dropdown (excluding current workplace)
            $workplaceModel = new Workplace();
            $allWorkplaces = $workplaceModel->getAll();
            $transferWorkplaces = array_filter($allWorkplaces, function($wp) use ($animal) {
                return $wp['id'] != $animal['workplace_id'];
            });

            // Fetch enclosures for the current workplace
            $enclosures = $workplaceModel->getEnclosures($animal['workplace_id']);

            View::render('animals/detail', [
                'layout' => 'main',
                'title' => $animal['name'] ?? 'Zvíře #' . $animal['identifier'],
                'animal' => $animal,
                'examinations' => $examinations,
                'dewormings' => $dewormings,
                'scheduledChecks' => $scheduledChecks,
                'statusHistory' => $statusHistory,
                'canEdit' => $canEdit,
                'transferWorkplaces' => $transferWorkplaces,
                'enclosures' => $enclosures
            ]);
        }
    }
    
    public function create($workplaceId) {
        Auth::requireLogin();
        
        $userModel = new User();
        if (!$userModel->hasPermission(Auth::userId(), $workplaceId, 'edit')) {
            die('Nemáte oprávnění editovat toto pracoviště');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $animalModel = new Animal();

            try {
                // Convert empty strings to null for optional fields
                $enclosureId = $_POST['enclosure_id'] ?? null;
                $enclosureId = ($enclosureId === '' || $enclosureId === '0') ? null : $enclosureId;

                $birthDate = $_POST['birth_date'] ?? null;
                $birthDate = ($birthDate === '') ? null : $birthDate;

                $notes = $_POST['notes'] ?? null;
                $notes = ($notes === '') ? null : $notes;

                $data = [
                    'workplace_id' => $workplaceId,
                    'name' => $_POST['name'] ?? null,
                    'species' => $_POST['species'] ?? null,
                    'identifier' => $_POST['identifier'] ?? null,
                    'birth_date' => $birthDate,
                    'gender' => $_POST['gender'] ?? 'unknown',
                    'current_status' => 'active',
                    'current_enclosure_id' => $enclosureId,
                    'notes' => $notes
                ];

                // Validate required fields
                if (empty($data['name']) || empty($data['species']) || empty($data['identifier'])) {
                    die('Chybí povinná pole: Jméno, Druh a Identifikátor jsou povinné');
                }

                $animalId = $animalModel->create($data);

                // Vytvořit záznam v historii
                $this->addStatusHistory($animalId, 'received', $_POST['birth_date'] ?? date('Y-m-d'));

                // Check if we should redirect to biochemistry or urine analysis
                $from = $_GET['from'] ?? '';
                if ($from === 'biochemistry') {
                    View::redirect('/biochemistry/workplace/' . $workplaceId);
                } elseif ($from === 'urineanalysis') {
                    View::redirect('/urineanalysis/workplace/' . $workplaceId);
                } else {
                    View::redirect('/workplace/' . $workplaceId . '/animals/' . $animalId);
                }
                return;
            } catch (Exception $e) {
                error_log("AnimalController::create error: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                die('Chyba při vytváření zvířete: ' . $e->getMessage());
            }
        }
        
        $workplaceModel = new Workplace();
        $workplace = $workplaceModel->findById($workplaceId);
        $enclosures = $workplaceModel->getEnclosures($workplaceId);

        $from = $_GET['from'] ?? '';

        View::render('animals/form', [
            'layout' => 'main',
            'title' => 'Přidat zvíře',
            'workplace' => $workplace,
            'enclosures' => $enclosures,
            'action' => 'create',
            'fromBiochemistry' => $from === 'biochemistry',
            'fromUrineAnalysis' => $from === 'urineanalysis',
            'from' => $from
        ]);
    }
    
    private function addStatusHistory($animalId, $status, $date) {
        $animalModel = new Animal();
        $animal = $animalModel->findById($animalId);

        $animalModel->execute("
            INSERT INTO animal_status_history
            (animal_id, status, status_date, to_workplace_id, to_enclosure_id, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ", [
            $animalId,
            $status,
            $date,
            $animal['workplace_id'],
            $animal['current_enclosure_id'],
            Auth::userId()
        ]);
    }

    public function updateNextTest($animalId) {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Pouze POST metoda']);
            return;
        }

        $animalModel = new Animal();
        $animal = $animalModel->findById($animalId);

        if (!$animal) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Zvíře nenalezeno']);
            return;
        }

        // Check if user has permission to edit this workplace
        $userModel = new User();
        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění editovat toto zvíře']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $nextTest = $input['next_test'] ?? '';

        try {
            $animalModel->update($animalId, ['next_check_date' => $nextTest]);
            http_response_code(200);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("AnimalController::updateNextTest error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba databáze: ' . $e->getMessage()]);
        }
    }

    public function update($animalId) {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Pouze POST metoda']);
            return;
        }

        $animalModel = new Animal();
        $animal = $animalModel->findById($animalId);

        if (!$animal) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Zvíře nenalezeno']);
            return;
        }

        // Check if user has permission to edit this workplace
        $userModel = new User();
        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění editovat toto zvíře']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        try {
            $updateData = [];

            // Only update fields that are present in the request
            if (isset($input['species'])) {
                $updateData['species'] = $input['species'];
            }
            if (isset($input['gender'])) {
                $updateData['gender'] = $input['gender'];
            }
            if (isset($input['birth_date'])) {
                $updateData['birth_date'] = $input['birth_date'] ?: null;
            }
            if (isset($input['current_enclosure_id'])) {
                $updateData['current_enclosure_id'] = $input['current_enclosure_id'] ?: null;
            }
            if (isset($input['next_check_date'])) {
                $updateData['next_check_date'] = $input['next_check_date'] ?: null;
            }
            if (isset($input['current_status'])) {
                $updateData['current_status'] = $input['current_status'];

                // If status is transferred, change workplace_id
                if ($input['current_status'] === 'transferred') {
                    if (empty($input['transfer_workplace_id'])) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Musíte vybrat, kam bylo zvíře přesunuto']);
                        return;
                    }

                    // Verify the target workplace exists
                    $workplaceModel = new Workplace();
                    $targetWorkplace = $workplaceModel->findById($input['transfer_workplace_id']);
                    if (!$targetWorkplace) {
                        http_response_code(400);
                        echo json_encode(['success' => false, 'error' => 'Cílové pracoviště neexistuje']);
                        return;
                    }

                    // Update workplace_id to transfer the animal
                    $updateData['workplace_id'] = $input['transfer_workplace_id'];
                    $updateData['current_enclosure_id'] = null; // Clear enclosure when transferring
                }
            }
            if (isset($input['notes'])) {
                $updateData['notes'] = $input['notes'] ?: null;
            }

            // Validate required fields
            if (isset($updateData['species']) && empty($updateData['species'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Druh je povinné pole']);
                return;
            }

            $animalModel->update($animalId, $updateData);

            // Add status history entry if status changed
            if (isset($input['current_status'])) {
                $this->addStatusHistory(
                    $animalId,
                    $input['current_status'],
                    date('Y-m-d')
                );
            }

            http_response_code(200);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("AnimalController::update error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba databáze: ' . $e->getMessage()]);
        }
    }
}