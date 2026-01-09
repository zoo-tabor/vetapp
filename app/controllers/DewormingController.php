<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../models/User.php';

class DewormingController {

    public function create() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Support both single animal_id and multiple animal_ids[]
                $animalIds = $_POST['animal_ids'] ?? [];
                $singleAnimalId = $_POST['animal_id'] ?? null;

                // If single animal_id is provided (from detail page), convert to array
                if ($singleAnimalId && empty($animalIds)) {
                    $animalIds = [$singleAnimalId];
                }

                $dewormingDate = $_POST['deworming_date'] ?? null;
                $medication = trim($_POST['medication'] ?? '');
                $dosage = trim($_POST['dosage'] ?? '');
                $administrationRoute = trim($_POST['administration_route'] ?? '');
                $reason = trim($_POST['reason'] ?? '');
                $relatedExaminationId = $_POST['related_examination_id'] ?? null;
                $notes = trim($_POST['notes'] ?? '');

                // Validate required fields
                if (empty($animalIds) || empty($dewormingDate)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Animal ID a datum odčervení jsou povinné']);
                    return;
                }

                $animalModel = new Animal();
                $userModel = new User();

                $successCount = 0;
                $errors = [];

                // Create deworming record for each selected animal
                foreach ($animalIds as $animalId) {
                    // Get animal to check workplace permissions
                    $animal = $animalModel->findById($animalId);

                    if (!$animal) {
                        $errors[] = "Zvíře ID $animalId nenalezeno";
                        continue;
                    }

                    // Check edit permissions
                    if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit')) {
                        $errors[] = "Nemáte oprávnění editovat zvíře ID $animalId";
                        continue;
                    }

                    // Create deworming record
                    $result = $animalModel->execute("
                        INSERT INTO dewormings
                        (animal_id, workplace_id, deworming_date, medication, dosage, administration_route, reason, related_examination_id, notes, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ", [
                        $animalId,
                        $animal['workplace_id'],
                        $dewormingDate,
                        $medication ?: null,
                        $dosage ?: null,
                        $administrationRoute ?: null,
                        $reason ?: null,
                        $relatedExaminationId ?: null,
                        $notes ?: null,
                        Auth::userId()
                    ]);

                    $successCount++;
                }

                if ($successCount > 0) {
                    http_response_code(200);
                    $message = $successCount === 1 ? 'Odčervení vytvořeno' : "Odčervení vytvořeno pro $successCount zvířat";
                    echo json_encode(['success' => true, 'message' => $message, 'errors' => $errors]);
                } else {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Nepodařilo se vytvořit žádné odčervení', 'errors' => $errors]);
                }
            } catch (Exception $e) {
                error_log("DewormingController::create error: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Chyba při vytváření záznamu: ' . $e->getMessage()]);
            }
        }
    }
}
