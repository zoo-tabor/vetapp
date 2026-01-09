<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/User.php';

class EnclosureController {

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
            try {
                $name = trim($_POST['name'] ?? '');
                $notes = trim($_POST['description'] ?? ''); // Form uses 'description' but DB uses 'notes'

                // Validate
                if (empty($name)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Název výběhu je povinný']);
                    return;
                }

                // Create enclosure
                $workplaceModel = new Workplace();
                $result = $workplaceModel->createEnclosure([
                    'workplace_id' => $workplaceId,
                    'name' => $name,
                    'notes' => $notes
                ]);

                http_response_code(200);
                echo json_encode(['success' => true]);
            } catch (Exception $e) {
                error_log("EnclosureController::create error: " . $e->getMessage());
                error_log("Stack trace: " . $e->getTraceAsString());
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Chyba při vytváření výběhu: ' . $e->getMessage()]);
            }
        }
    }
}
