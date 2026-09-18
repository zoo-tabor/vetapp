<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/User.php';

class EnclosureController {

    // Detail výběhu: info + editace přímo na stránce + zvířata ve výběhu.
    public function detail($id) {
        Auth::requireLogin();

        $workplaceModel = new Workplace();
        $userModel = new User();

        $enclosure = $workplaceModel->getEnclosureById($id);
        if (!$enclosure) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Chyba',
                'message' => 'Výběh nenalezen'
            ]);
            return;
        }

        if (!$userModel->hasPermission(Auth::userId(), $enclosure['workplace_id'], 'animals')) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Přístup odepřen',
                'message' => 'Nemáte oprávnění k tomuto pracovišti'
            ]);
            return;
        }

        $canEdit = Auth::isAdmin() || $userModel->hasPermission(Auth::userId(), $enclosure['workplace_id'], 'animals', 'edit');
        $workplace = $workplaceModel->findById($enclosure['workplace_id']);

        // Aktivní zvířata ve výběhu.
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT id, name, species, identifier, gender, current_status
            FROM animals
            WHERE current_enclosure_id = ? AND current_status = 'active'
            ORDER BY species, name
        ");
        $stmt->execute([$id]);
        $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        View::render('enclosures/detail', [
            'layout' => 'main',
            'title' => 'Výběh - ' . $enclosure['name'],
            'enclosure' => $enclosure,
            'workplace' => $workplace,
            'animals' => $animals,
            'canEdit' => $canEdit
        ]);
    }

    // Interaktivní mapa výběhů pro dané pracoviště (geometrie z assets/data/zoo-mapa.json).
    public function map($workplaceId) {
        Auth::requireLogin();

        $workplaceModel = new Workplace();
        $userModel = new User();

        if (!$userModel->hasPermission(Auth::userId(), $workplaceId, 'animals')) {
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

        // Geometrie mapy.
        $mapFile = ROOT_PATH . '/assets/data/zoo-mapa.json';
        $mapJson = is_file($mapFile) ? json_decode(file_get_contents($mapFile), true) : null;
        if (!$mapJson || empty($mapJson['enclosures'])) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Mapa není dostupná',
                'message' => 'Pro toto pracoviště není k dispozici mapa výběhů.'
            ]);
            return;
        }

        $db = Database::getInstance()->getConnection();

        // Výběhy z DB pro toto pracoviště: code => [id, name].
        $stmt = $db->prepare("SELECT id, code, name FROM enclosures WHERE workplace_id = ? AND is_active = 1");
        $stmt->execute([$workplaceId]);
        $encByCode = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $e) {
            if ($e['code'] !== null && $e['code'] !== '') {
                $encByCode[(string)$e['code']] = $e;
            }
        }

        // Aktivní zvířata s výběhem, seskupená podle enclosure id.
        $stmt = $db->prepare("
            SELECT id, name, species, gender, current_enclosure_id
            FROM animals
            WHERE workplace_id = ? AND current_status = 'active' AND current_enclosure_id IS NOT NULL
            ORDER BY species, name
        ");
        $stmt->execute([$workplaceId]);
        $animalsByEnc = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $a) {
            $animalsByEnc[(int)$a['current_enclosure_id']][] = $a;
        }

        // Spoj geometrii s DB (živá data).
        $enclosures = [];
        foreach ($mapJson['enclosures'] as $g) {
            if (empty($g['d'])) continue;
            $kod = (string)($g['kod'] ?? '');
            $dbEnc = $encByCode[$kod] ?? null;
            $encId = $dbEnc ? (int)$dbEnc['id'] : null;
            $enclosures[] = [
                'kod' => $kod,
                'name' => $dbEnc['name'] ?? ($g['name'] ?? ('Výběh ' . $kod)),
                'd' => $g['d'],
                'label' => $g['label'] ?? null,
                'clickable' => !empty($g['clickable']) && $encId !== null,
                'encId' => $encId,
                'animals' => $encId ? array_values($animalsByEnc[$encId] ?? []) : [],
            ];
        }

        View::render('enclosures/map', [
            'layout' => 'main',
            'title' => 'Mapa výběhů - ' . $workplace['name'],
            'workplace' => $workplace,
            'mapWidth' => $mapJson['width'] ?? 3386,
            'mapHeight' => $mapJson['height'] ?? 2042,
            'enclosures' => $enclosures
        ]);
    }

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
