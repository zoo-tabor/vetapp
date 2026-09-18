<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/User.php';

class EnclosureController {

    public function create($workplaceId) {
        Auth::requireLogin();

        // Check edit permissions
        $userModel = new User();
        if (!$userModel->hasPermission(Auth::userId(), $workplaceId, 'animals', 'edit')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění editovat toto pracoviště']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $name = trim($_POST['name'] ?? '');
                $code = trim($_POST['code'] ?? '');
                $sampleType = $_POST['sample_type'] ?? 'individual';
                $notes = trim($_POST['notes'] ?? '');

                // Validate
                if (empty($name)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Název výběhu je povinný']);
                    return;
                }
                if (!in_array($sampleType, ['individual', 'mixed'], true)) {
                    $sampleType = 'individual';
                }

                // Create enclosure
                $workplaceModel = new Workplace();
                $result = $workplaceModel->createEnclosure([
                    'workplace_id' => $workplaceId,
                    'name' => $name,
                    'code' => $code !== '' ? $code : null,
                    'sample_type' => $sampleType,
                    'notes' => $notes !== '' ? $notes : null
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

    public function update($id) {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Neplatná metoda']);
            return;
        }

        try {
            $workplaceModel = new Workplace();
            $enclosure = $workplaceModel->getEnclosureById($id);
            if (!$enclosure) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Výběh nenalezen']);
                return;
            }

            // Kontrola oprávnění vůči pracovišti výběhu.
            $userModel = new User();
            if (!$userModel->hasPermission(Auth::userId(), $enclosure['workplace_id'], 'animals', 'edit')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění editovat toto pracoviště']);
                return;
            }

            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['code'] ?? '');
            $sampleType = $_POST['sample_type'] ?? 'individual';
            $notes = trim($_POST['notes'] ?? '');

            if (empty($name)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Název výběhu je povinný']);
                return;
            }
            if (!in_array($sampleType, ['individual', 'mixed'], true)) {
                $sampleType = 'individual';
            }

            $workplaceModel->updateEnclosure($id, [
                'name' => $name,
                'code' => $code !== '' ? $code : null,
                'sample_type' => $sampleType,
                'notes' => $notes !== '' ? $notes : null
            ]);

            http_response_code(200);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("EnclosureController::update error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba při úpravě výběhu: ' . $e->getMessage()]);
        }
    }

    public function delete($id) {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Neplatná metoda']);
            return;
        }

        try {
            $workplaceModel = new Workplace();
            $enclosure = $workplaceModel->getEnclosureById($id);
            if (!$enclosure) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Výběh nenalezen']);
                return;
            }

            $userModel = new User();
            if (!$userModel->hasPermission(Auth::userId(), $enclosure['workplace_id'], 'animals', 'edit')) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění editovat toto pracoviště']);
                return;
            }

            $workplaceModel->deleteEnclosure($id);

            http_response_code(200);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("EnclosureController::delete error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba při mazání výběhu: ' . $e->getMessage()]);
        }
    }
}
