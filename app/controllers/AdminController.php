<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/Animal.php';

class AdminController {

    public function settings() {
        Auth::requireLogin();

        // Only admins can access this page
        if (!Auth::isAdmin()) {
            http_response_code(403);
            die('Nemáte oprávnění k této stránce');
        }

        $userModel = new User();
        $workplaceModel = new Workplace();
        $users = $userModel->getAllUsers();
        $workplaces = $workplaceModel->getAll();

        // Get all animals with workplace names
        $db = \Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT
                a.id,
                a.name,
                a.identifier,
                a.species,
                a.workplace_id,
                w.name as workplace_name,
                ac.name as category_name,
                e.name as enclosure_name
            FROM animals a
            JOIN workplaces w ON a.workplace_id = w.id
            LEFT JOIN animal_categories ac ON a.animal_category_id = ac.id
            LEFT JOIN enclosures e ON a.current_enclosure_id = e.id
            WHERE a.current_status = 'active'
            ORDER BY w.name, ac.name, a.species, a.name
        ");
        $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Přiřazení ošetřovatelé (M:N) najednou pro všechna zvířata.
        $animalModel = new Animal();
        $keepersMap = $animalModel->getKeepersMap(array_column($animals, 'id'));
        foreach ($animals as &$__animal) {
            $__animal['keepers'] = $keepersMap[(int)$__animal['id']] ?? [];
        }
        unset($__animal);

        View::render('admin/settings', [
            'title' => 'Administrace systému',
            'users' => $users,
            'workplaces' => $workplaces,
            'animals' => $animals
        ]);
    }

    public function getUser($userId) {
        Auth::requireLogin();

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Uživatel nenalezen']);
            return;
        }

        echo json_encode(['success' => true, 'user' => $user]);
    }

    public function createUser() {
        Auth::requireLogin();

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Pouze POST metoda']);
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $zootrackEdit = isset($_POST['zootrack_edit']) ? 1 : 0;

        // Validate required fields
        if (empty($username) || empty($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Uživatelské jméno a e-mail jsou povinné']);
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Neplatná e-mailová adresa']);
            return;
        }

        $userModel = new User();

        // Check if username already exists
        if ($userModel->usernameExists($username)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Uživatelské jméno již existuje']);
            return;
        }

        try {
            require_once __DIR__ . '/../core/Email.php';

            // Create user without password (temporary hash will be used)
            $userId = $userModel->createUser($username, null, $fullName, $email, $role, $isActive, $zootrackEdit);

            if ($userId) {
                // Generate password reset token
                $token = $userModel->generatePasswordResetToken($userId);

                // Send email with password setup link
                $emailSent = Email::sendPasswordSetup($email, $fullName, $token);

                if ($emailSent) {
                    echo json_encode([
                        'success' => true,
                        'user_id' => $userId,
                        'message' => 'Uživatel vytvořen. E-mail s odkazem pro nastavení hesla byl odeslán.'
                    ]);
                } else {
                    // User created but email failed
                    echo json_encode([
                        'success' => true,
                        'user_id' => $userId,
                        'warning' => 'Uživatel vytvořen, ale nepodařilo se odeslat e-mail. Zkuste znovu odeslat odkaz.'
                    ]);
                }
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Nepodařilo se vytvořit uživatele']);
            }
        } catch (Exception $e) {
            error_log("AdminController::createUser error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba při vytváření uživatele: ' . $e->getMessage()]);
        }
    }

    public function updateUser($userId) {
        Auth::requireLogin();

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Pouze POST metoda']);
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Uživatel nenalezen']);
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $password = $_POST['password'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $zootrackEdit = isset($_POST['zootrack_edit']) ? 1 : 0;

        // Validate required fields
        if (empty($username)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Uživatelské jméno je povinné']);
            return;
        }

        // Check if username is taken by another user
        if ($username !== $user['username'] && $userModel->usernameExists($username)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Uživatelské jméno již existuje']);
            return;
        }

        try {
            $success = $userModel->updateUser($userId, $username, $password, $fullName, $email, $role, $isActive, $zootrackEdit);

            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Nepodařilo se aktualizovat uživatele']);
            }
        } catch (Exception $e) {
            error_log("AdminController::updateUser error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba při aktualizaci uživatele: ' . $e->getMessage()]);
        }
    }

    public function getUserPermissions($userId) {
        Auth::requireLogin();

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Uživatel nenalezen']);
            return;
        }

        $workplaceModel = new Workplace();
        $workplaces = $workplaceModel->getAll();
        $permissions = $userModel->getUserPermissions($userId);

        echo json_encode([
            'success' => true,
            'user' => $user,
            'workplaces' => $workplaces,
            'permissions' => $permissions
        ]);
    }

    public function saveUserPermissions($userId) {
        Auth::requireLogin();

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Pouze POST metoda']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $permissions = $input['permissions'] ?? [];

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Uživatel nenalezen']);
            return;
        }

        try {
            $success = $userModel->saveUserPermissions($userId, $permissions);

            if ($success) {
                echo json_encode(['success' => true]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Nepodařilo se uložit oprávnění']);
            }
        } catch (Exception $e) {
            error_log("AdminController::saveUserPermissions error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba při ukládání oprávnění: ' . $e->getMessage()]);
        }
    }

    public function resendPasswordSetup($userId) {
        Auth::requireLogin();

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            return;
        }

        $userModel = new User();
        $user = $userModel->findById($userId);

        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Uživatel nenalezen']);
            return;
        }

        if (empty($user['email'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Uživatel nemá nastavenou e-mailovou adresu']);
            return;
        }

        try {
            require_once __DIR__ . '/../core/Email.php';

            // Generate new password reset token
            $token = $userModel->generatePasswordResetToken($userId);

            // Send email
            $emailSent = Email::sendPasswordSetup($user['email'], $user['full_name'], $token);

            if ($emailSent) {
                echo json_encode(['success' => true, 'message' => 'E-mail s odkazem pro nastavení hesla byl odeslán']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Nepodařilo se odeslat e-mail']);
            }
        } catch (Exception $e) {
            error_log("AdminController::resendPasswordSetup error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba při odesílání e-mailu: ' . $e->getMessage()]);
        }
    }

    public function deleteUser($userId) {
        // Catch any errors and always return JSON
        try {
            // Start output buffering to prevent any accidental output
            ob_start();

            Auth::requireLogin();

            // Set JSON header immediately and disable caching
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

            if (!Auth::isAdmin()) {
                ob_end_clean();
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění'], JSON_UNESCAPED_UNICODE);
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                ob_end_clean();
                http_response_code(405);
                echo json_encode(['success' => false, 'error' => 'Pouze POST metoda'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $userModel = new User();
            $user = $userModel->findById($userId);

            if (!$user) {
                ob_end_clean();
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Uživatel nenalezen'], JSON_UNESCAPED_UNICODE);
                return;
            }

            // Prevent deleting yourself
            if ($userId == Auth::userId()) {
                ob_end_clean();
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Nemůžete smazat svůj vlastní účet'], JSON_UNESCAPED_UNICODE);
                return;
            }

            $success = $userModel->deleteUser($userId);

            ob_end_clean();
            if ($success) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Uživatel byl úspěšně smazán'], JSON_UNESCAPED_UNICODE);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Nepodařilo se smazat uživatele'], JSON_UNESCAPED_UNICODE);
            }
        } catch (Exception $e) {
            // Clean any output buffer
            if (ob_get_level()) ob_end_clean();

            $errorMsg = "AdminController::deleteUser error for user ID $userId: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine();
            error_log($errorMsg);
            error_log("Stack trace: " . $e->getTraceAsString());

            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba při mazání uživatele: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getAnimalKeeper($animalId) {
        Auth::requireLogin();

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            return;
        }

        try {
            $db = \Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id FROM animals WHERE id = ?");
            $stmt->execute([$animalId]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Zvíře nenalezeno']);
                return;
            }

            $animalModel = new Animal();
            echo json_encode([
                'success' => true,
                'keeper_ids' => $animalModel->getKeeperUserIds($animalId)
            ]);
        } catch (Exception $e) {
            error_log("AdminController::getAnimalKeeper error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba při načítání přiřazení']);
        }
    }

    public function updateAnimalKeeper($animalId) {
        Auth::requireLogin();

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Pouze POST metoda']);
            return;
        }

        // Ošetřovatelé jako pole user_id (prázdné pole = nepřiřazeno).
        $rawUserIds = $_POST['assigned_users'] ?? [];
        if (!is_array($rawUserIds)) {
            $rawUserIds = ($rawUserIds === '' ? [] : [$rawUserIds]);
        }
        $userIds = array_values(array_unique(array_filter(
            array_map('intval', $rawUserIds),
            fn($v) => $v > 0
        )));

        try {
            $db = \Database::getInstance()->getConnection();

            // Verify animal exists
            $stmt = $db->prepare("SELECT id FROM animals WHERE id = ?");
            $stmt->execute([$animalId]);
            if (!$stmt->fetch()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Zvíře nenalezeno']);
                return;
            }

            // Ověřit, že všichni vybraní uživatelé existují a jsou aktivní.
            if (!empty($userIds)) {
                $placeholders = implode(',', array_fill(0, count($userIds), '?'));
                $stmt = $db->prepare("SELECT id FROM users WHERE id IN ($placeholders) AND is_active = 1");
                $stmt->execute($userIds);
                $validIds = array_map(fn($r) => (int)$r['id'], $stmt->fetchAll(PDO::FETCH_ASSOC));
                if (count($validIds) !== count($userIds)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Některý vybraný uživatel neexistuje nebo není aktivní']);
                    return;
                }
            }

            $animalModel = new Animal();
            $animalModel->setKeepers($animalId, $userIds);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("AdminController::updateAnimalKeeper error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba při aktualizaci přiřazení: ' . $e->getMessage()]);
        }
    }

    /**
     * Hromadné přiřazení ošetřovatelů k více vybraným zvířatům.
     * POST animal_ids[], user_ids[], mode (add|replace).
     * Vrací aktualizované ošetřovatele pro dotčená zvířata (pro UI bez reloadu).
     */
    public function bulkAssignKeepers() {
        Auth::requireLogin();

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Nemáte oprávnění']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Pouze POST metoda']);
            return;
        }

        $animalIds = $_POST['animal_ids'] ?? [];
        $userIds   = $_POST['user_ids'] ?? [];
        $mode      = ($_POST['mode'] ?? 'add') === 'replace' ? 'replace' : 'add';

        if (!is_array($animalIds)) { $animalIds = $animalIds === '' ? [] : [$animalIds]; }
        if (!is_array($userIds))   { $userIds   = $userIds === '' ? [] : [$userIds]; }

        $animalIds = array_values(array_unique(array_filter(array_map('intval', $animalIds), fn($v) => $v > 0)));
        $userIds   = array_values(array_unique(array_filter(array_map('intval', $userIds), fn($v) => $v > 0)));

        if (empty($animalIds)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nevybrali jste žádné zvíře']);
            return;
        }

        // U režimu „přidat" musí být vybrán aspoň jeden ošetřovatel.
        if ($mode === 'add' && empty($userIds)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Vyberte alespoň jednoho ošetřovatele']);
            return;
        }

        try {
            $db = \Database::getInstance()->getConnection();

            // Ověřit, že všichni vybraní uživatelé existují a jsou aktivní.
            if (!empty($userIds)) {
                $placeholders = implode(',', array_fill(0, count($userIds), '?'));
                $stmt = $db->prepare("SELECT id FROM users WHERE id IN ($placeholders) AND is_active = 1");
                $stmt->execute($userIds);
                $validIds = array_map(fn($r) => (int)$r['id'], $stmt->fetchAll(PDO::FETCH_ASSOC));
                if (count($validIds) !== count($userIds)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'error' => 'Některý vybraný uživatel neexistuje nebo není aktivní']);
                    return;
                }
            }

            $animalModel = new Animal();
            $animalModel->bulkAssignKeepers($animalIds, $userIds, $mode);

            // Vrátit aktualizované přiřazení pro dotčená zvířata (UI bez reloadu).
            echo json_encode([
                'success' => true,
                'count'   => count($animalIds),
                'keepers' => $animalModel->getKeepersMap($animalIds)
            ]);
        } catch (Exception $e) {
            error_log("AdminController::bulkAssignKeepers error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Chyba při hromadném přiřazení: ' . $e->getMessage()]);
        }
    }
}
