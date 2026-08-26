<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/ParasitologyGroup.php';

class DewormingController {

    public function create() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        try {
            // Cíle: jednotlivá zvířata a/nebo parazitologické skupiny.
            $animalIds = $_POST['animal_ids'] ?? [];
            $singleAnimalId = $_POST['animal_id'] ?? null; // z detailu zvířete
            if ($singleAnimalId && empty($animalIds)) {
                $animalIds = [$singleAnimalId];
            }
            $groupIds = $_POST['group_ids'] ?? [];

            $dewormingDate = $_POST['deworming_date'] ?? null;
            $medication = trim($_POST['medication'] ?? '');
            $dosage = trim($_POST['dosage'] ?? '');
            $administrationRoute = trim($_POST['administration_route'] ?? '');
            $reason = trim($_POST['reason'] ?? '');
            $relatedExaminationId = $_POST['related_examination_id'] ?? null;
            $notes = trim($_POST['notes'] ?? '');

            // Validace: aspoň jeden cíl a datum.
            if ((empty($animalIds) && empty($groupIds)) || empty($dewormingDate)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Vyberte alespoň jedno zvíře nebo skupinu a datum odčervení']);
                return;
            }

            $animalModel = new Animal();
            $userModel = new User();
            $groupModel = new ParasitologyGroup();
            $db = Database::getInstance()->getConnection();

            // Vloží jeden záznam odčervení (individuální nebo skupinový) a vrátí ID.
            $insertDeworming = function($animalId, $workplaceId, $groupId)
                use ($db, $dewormingDate, $medication, $dosage, $administrationRoute, $reason, $relatedExaminationId, $notes) {
                $stmt = $db->prepare("
                    INSERT INTO dewormings
                    (animal_id, workplace_id, group_id, deworming_date, medication, dosage, administration_route, reason, related_examination_id, notes, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $animalId,
                    $workplaceId,
                    $groupId,
                    $dewormingDate,
                    $medication ?: null,
                    $dosage ?: null,
                    $administrationRoute ?: null,
                    $reason ?: null,
                    $relatedExaminationId ?: null,
                    $notes ?: null,
                    Auth::userId()
                ]);
                return $db->lastInsertId();
            };

            // Zapíše napojená zvířata do junction (jednotné čtení historie).
            $addJunction = function($dewId, $ids) use ($db) {
                if (empty($ids)) {
                    return;
                }
                $ins = $db->prepare("INSERT IGNORE INTO deworming_animals (deworming_id, animal_id) VALUES (?, ?)");
                foreach ($ids as $aid) {
                    $ins->execute([$dewId, (int)$aid]);
                }
            };

            $successCount = 0;
            $errors = [];

            // Individuální odčervení.
            foreach ($animalIds as $animalId) {
                $animal = $animalModel->findById($animalId);
                if (!$animal) {
                    $errors[] = "Zvíře ID $animalId nenalezeno";
                    continue;
                }
                if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'parasitology', 'edit')) {
                    $errors[] = "Nemáte oprávnění editovat zvíře ID $animalId";
                    continue;
                }
                $dewId = $insertDeworming($animalId, $animal['workplace_id'], null);
                $addJunction($dewId, [$animalId]);
                $successCount++;
            }

            // Skupinové odčervení – jeden záznam na skupinu + snímek členů.
            foreach ($groupIds as $groupId) {
                $group = $groupModel->findById($groupId);
                if (!$group) {
                    $errors[] = "Skupina ID $groupId nenalezena";
                    continue;
                }
                if (!$userModel->hasPermission(Auth::userId(), $group['workplace_id'], 'parasitology', 'edit')) {
                    $errors[] = "Nemáte oprávnění ke skupině ID $groupId";
                    continue;
                }
                $memberIds = $groupModel->getMemberAnimalIds($groupId);
                $dewId = $insertDeworming(null, $group['workplace_id'], (int)$groupId);
                $addJunction($dewId, $memberIds);
                $successCount++;
            }

            if ($successCount > 0) {
                http_response_code(200);
                $message = $successCount === 1 ? 'Odčervení vytvořeno' : "Odčervení vytvořeno ($successCount záznamů)";
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
