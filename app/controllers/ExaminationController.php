<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Examination.php';
require_once __DIR__ . '/../models/User.php';

class ExaminationController {

    public function create($workplaceId) {
        Auth::requireLogin();

        // Check edit permissions
        $userModel = new User();
        if (!$userModel->hasPermission(Auth::userId(), $workplaceId, 'edit')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění editovat toto pracoviště']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $examinationDate = $_POST['examination_date'] ?? '';
            $institution = $_POST['institution'] ?? '';
            $notes = trim($_POST['notes'] ?? '');
            $animalIds = $_POST['animal_ids'] ?? [];
            $enclosureIds = $_POST['enclosure_ids'] ?? [];
            $examinations = $_POST['examinations'] ?? [];

            // Validate required fields
            if (empty($examinationDate) || empty($institution)) {
                $missing = [];
                if (empty($examinationDate)) $missing[] = 'datum vyšetření';
                if (empty($institution)) $missing[] = 'instituce';

                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Chybí povinná pole: ' . implode(', ', $missing)]);
                return;
            }

            // Validate examinations array
            if (empty($examinations)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Musíte přidat alespoň jedno vyšetření']);
                return;
            }

            // Validate at least one target selected
            if (empty($animalIds) && empty($enclosureIds)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Musíte vybrat alespoň jedno zvíře nebo výběh']);
                return;
            }

            $examinationModel = new Examination();

            try {
                // Create multiple examination records for each selected animal
                if (!empty($animalIds)) {
                    foreach ($animalIds as $animalId) {
                        // Create one record for each examination type
                        foreach ($examinations as $exam) {
                            $sampleType = $exam['sample_type'] ?? '';
                            $intensity = $exam['intensity'] ?? '';
                            $parasiteFound = trim($exam['parasite_found'] ?? '');

                            // Skip if essential fields are missing
                            if (empty($sampleType) || empty($intensity)) {
                                continue;
                            }

                            // Determine finding status
                            $findingStatus = ($intensity === 'neg.' || $intensity === '0') ? 'negative' : 'positive';

                            $data = [
                                'animal_id' => $animalId,
                                'examination_date' => $examinationDate,
                                'sample_type' => $sampleType,
                                'institution' => $institution,
                                'parasite_found' => $parasiteFound ?: null,
                                'finding_status' => $findingStatus,
                                'intensity' => $intensity,
                                'notes' => $notes ?: null,
                                'created_by' => Auth::userId()
                            ];
                            $examinationModel->createExamination($data);
                        }
                    }
                }

                // Create examinations for animals in selected enclosures
                if (!empty($enclosureIds)) {
                    foreach ($enclosureIds as $enclosureId) {
                        // Create one record for each examination type
                        foreach ($examinations as $exam) {
                            $sampleType = $exam['sample_type'] ?? '';
                            $intensity = $exam['intensity'] ?? '';
                            $parasiteFound = trim($exam['parasite_found'] ?? '');

                            // Skip if essential fields are missing
                            if (empty($sampleType) || empty($intensity)) {
                                continue;
                            }

                            // Determine finding status
                            $findingStatus = ($intensity === 'neg.' || $intensity === '0') ? 'negative' : 'positive';

                            $data = [
                                'enclosure_id' => $enclosureId,
                                'examination_date' => $examinationDate,
                                'sample_type' => $sampleType,
                                'institution' => $institution,
                                'parasite_found' => $parasiteFound ?: null,
                                'finding_status' => $findingStatus,
                                'intensity' => $intensity,
                                'notes' => $notes ?: null,
                                'created_by' => Auth::userId()
                            ];
                            $examinationModel->createExaminationForEnclosure($data);
                        }
                    }
                }

                http_response_code(200);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                error_log("ExaminationController::create error: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Chyba databáze: ' . $e->getMessage()]);
            }
        }
    }

    public function getDetails() {
        Auth::requireLogin();

        $ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

        if (empty($ids)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Chybí ID vyšetření']);
            return;
        }

        $examinationModel = new Examination();

        try {
            $examinations = [];
            foreach ($ids as $id) {
                $exam = $examinationModel->findById(trim($id));
                if ($exam) {
                    $examinations[] = $exam;
                }
            }

            if (empty($examinations)) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Vyšetření nebylo nalezeno']);
                return;
            }

            http_response_code(200);
            echo json_encode(['success' => true, 'examinations' => $examinations]);
        } catch (Exception $e) {
            error_log("ExaminationController::getDetails error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba databáze: ' . $e->getMessage()]);
        }
    }

    public function update() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Pouze POST metoda']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $examinations = $input['examinations'] ?? [];

        if (empty($examinations)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Chybí data vyšetření']);
            return;
        }

        $examinationModel = new Examination();
        $userModel = new User();

        try {
            // Check permissions and update each examination
            foreach ($examinations as $examData) {
                $id = $examData['id'] ?? null;

                if (!$id) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Chybí ID vyšetření']);
                    return;
                }

                // Check if examination exists
                $exam = $examinationModel->findById($id);
                if (!$exam) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Vyšetření #' . $id . ' nebylo nalezeno']);
                    return;
                }

                // Check permissions
                if (!$userModel->hasPermission(Auth::userId(), $exam['workplace_id'], 'edit')) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění upravit toto vyšetření']);
                    return;
                }

                // Determine finding status based on intensity
                $intensity = $examData['intensity'] ?? '';
                $findingStatus = ($intensity === 'neg.' || $intensity === '0') ? 'negative' : 'positive';

                // Update the examination
                $updateData = [
                    'sample_type' => $examData['sample_type'] ?? $exam['sample_type'],
                    'examination_date' => $examData['examination_date'] ?? $exam['examination_date'],
                    'institution' => $examData['institution'] ?? $exam['institution'],
                    'parasite_found' => $examData['parasite_found'] ?? null,
                    'intensity' => $intensity,
                    'finding_status' => $findingStatus,
                    'notes' => $examData['notes'] ?? null
                ];

                $examinationModel->update($id, $updateData);
            }

            http_response_code(200);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("ExaminationController::update error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba databáze: ' . $e->getMessage()]);
        }
    }

    public function delete() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Pouze POST metoda']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];

        if (empty($ids)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Chybí ID vyšetření']);
            return;
        }

        $examinationModel = new Examination();

        try {
            // Check permissions for each examination
            foreach ($ids as $id) {
                $exam = $examinationModel->findById($id);
                if (!$exam) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Vyšetření #' . $id . ' nebylo nalezeno']);
                    return;
                }

                // Check if user has permission to edit this workplace
                $userModel = new User();
                if (!$userModel->hasPermission(Auth::userId(), $exam['workplace_id'], 'edit')) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění smazat toto vyšetření']);
                    return;
                }
            }

            // Delete all examinations
            foreach ($ids as $id) {
                $examinationModel->delete($id);
            }

            http_response_code(200);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("ExaminationController::delete error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba databáze: ' . $e->getMessage()]);
        }
    }

    public function getByAnimals() {
        Auth::requireLogin();

        $animalIds = $_GET['animal_ids'] ?? '';

        if (empty($animalIds)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Chybí ID zvířat']);
            return;
        }

        $animalIdsArray = explode(',', $animalIds);
        $animalIdsArray = array_filter(array_map('trim', $animalIdsArray));

        if (empty($animalIdsArray)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Neplatná ID zvířat']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            // Create placeholders for IN clause
            $placeholders = implode(',', array_fill(0, count($animalIdsArray), '?'));

            // Get all examinations for the specified animals
            $stmt = $db->prepare("
                SELECT DISTINCT
                    e.id,
                    e.examination_date,
                    e.institution,
                    e.sample_type,
                    e.finding_status,
                    e.animal_id
                FROM examinations e
                WHERE e.animal_id IN ($placeholders)
                ORDER BY e.examination_date DESC
            ");

            $stmt->execute($animalIdsArray);
            $examinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'examinations' => $examinations
            ]);
        } catch (Exception $e) {
            error_log("ExaminationController::getByAnimals error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba databáze: ' . $e->getMessage()]);
        }
    }
}
