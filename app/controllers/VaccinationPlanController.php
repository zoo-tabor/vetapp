<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../models/VaccinationPlan.php';

class VaccinationPlanController {

    public function index() {
        Auth::requireLogin();

        // Get user's workplaces
        $userModel = new User();
        $workplaces = $userModel->getWorkplacePermissions(Auth::userId(), 'vaccination');

        View::render('vaccination_plan/dashboard', [
            'layout' => 'main',
            'title' => 'Vakcinační plán - Dashboard',
            'workplaces' => $workplaces
        ]);
    }

    public function workplace($id) {
        Auth::requireLogin();

        $userModel = new User();
        $workplaceModel = new Workplace();
        $vaccinationModel = new VaccinationPlan();

        // Check permissions
        if (!$userModel->hasPermission(Auth::userId(), $id, 'vaccination')) {
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

        // Auto-update overdue status
        $vaccinationModel->updateOverdueStatus();

        // Get vaccination plans separated by status
        $overduePlans = $vaccinationModel->getByWorkplace($id, ['status' => 'overdue']);
        $upcomingPlans = $vaccinationModel->getUpcoming($id, 90); // Next 90 days
        $completedPlans = $vaccinationModel->getByWorkplace($id, ['status' => 'completed']);

        // Get statistics
        $stats = $vaccinationModel->getStats($id);

        $canEdit = $userModel->hasPermission(Auth::userId(), $id, 'vaccination', 'edit');

        View::render('vaccination_plan/workplace', [
            'layout' => 'main',
            'title' => 'Vakcinační plán - ' . $workplace['name'],
            'workplace' => $workplace,
            'upcomingPlans' => $upcomingPlans,
            'overduePlans' => $overduePlans,
            'completedPlans' => $completedPlans,
            'stats' => $stats,
            'canEdit' => $canEdit
        ]);
    }

    /**
     * Planning grid view (like .docx)
     */
    public function planningGrid($id) {
        Auth::requireLogin();

        $userModel = new User();
        $workplaceModel = new Workplace();
        $vaccinationModel = new VaccinationPlan();

        // Check permissions
        if (!$userModel->hasPermission(Auth::userId(), $id, 'vaccination')) {
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

        $year = $_GET['year'] ?? date('Y');

        // Get animals grouped by category with their vaccination plans
        $animalsByCategory = $vaccinationModel->getByWorkplaceGroupedByCategory($id, $year);

        // Get available vaccines from warehouse
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT item_code as id, name
            FROM warehouse_items
            WHERE category = 'Vakcíny'
            ORDER BY name
        ");
        $stmt->execute();
        $vaccines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Get vaccine colors
        $stmt = $db->prepare("SELECT * FROM vaccine_type_colors");
        $stmt->execute();
        $vaccineColors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $canEdit = $userModel->hasPermission(Auth::userId(), $id, 'vaccination', 'edit');

        View::render('vaccination_plan/planning_grid', [
            'layout' => 'main',
            'title' => 'Plán vakcinací ' . $year . ' - ' . $workplace['name'],
            'workplace' => $workplace,
            'animalsByCategory' => $animalsByCategory,
            'vaccines' => $vaccines,
            'vaccineColors' => $vaccineColors,
            'year' => $year,
            'canEdit' => $canEdit
        ]);
    }

    /**
     * Add/Update vaccination plan
     */
    public function save() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vaccination-plan');
            exit;
        }

        $vaccinationModel = new VaccinationPlan();
        $animalModel = new Animal();

        // Get animal to check workplace permission
        $animal = $animalModel->findById($_POST['animal_id']);
        if (!$animal) {
            $_SESSION['error'] = 'Zvíře nenalezeno';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/vaccination-plan'));
            exit;
        }

        $userModel = new User();
        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'vaccination', 'edit')) {
            $_SESSION['error'] = 'Nemáte oprávnění upravovat vakcinační plány';
            header('Location: /vaccination-plan/workplace/' . $animal['workplace_id']);
            exit;
        }

        $data = [
            'animal_id' => $_POST['animal_id'],
            'vaccine_id' => $_POST['vaccine_id'] ?? null,
            'vaccine_name' => $_POST['vaccine_name'],
            'planned_date' => $_POST['planned_date'],
            'month_planned' => $_POST['month_planned'] ?? null,
            'vaccination_interval_days' => $_POST['vaccination_interval_days'] ?? null,
            'requires_booster' => isset($_POST['requires_booster']) ? 1 : 0,
            'booster_days' => $_POST['booster_days'] ?? null,
            'animal_category' => $animal['animal_category'],
            'notes' => $_POST['notes'] ?? null,
            'created_by' => Auth::userId()
        ];

        $result = $vaccinationModel->create($data);

        if ($result) {
            $_SESSION['success'] = 'Vakcinační plán byl úspěšně vytvořen';
        } else {
            $_SESSION['error'] = 'Chyba při vytváření vakcinačního plánu';
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/vaccination-plan/workplace/' . $animal['workplace_id']));
        exit;
    }

    /**
     * Mark vaccination as completed
     */
    public function markCompleted($id) {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vaccination-plan');
            exit;
        }

        $vaccinationModel = new VaccinationPlan();
        $plan = $vaccinationModel->findById($id);

        if (!$plan) {
            $_SESSION['error'] = 'Vakcinační plán nenalezen';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/vaccination-plan'));
            exit;
        }

        // Check permissions
        $userModel = new User();
        $animalModel = new Animal();
        $animal = $animalModel->findById($plan['animal_id']);

        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'vaccination', 'edit')) {
            $_SESSION['error'] = 'Nemáte oprávnění dokončit vakcinaci';
            header('Location: /vaccination-plan');
            exit;
        }

        $data = [
            'administered_date' => $_POST['administered_date'] ?? date('Y-m-d'),
            'administered_by' => Auth::userId(),
            'completion_notes' => $_POST['completion_notes'] ?? ''
        ];

        $result = $vaccinationModel->markAsCompleted($id, $data);

        if ($result) {
            $_SESSION['success'] = 'Vakcinace byla označena jako dokončená';
        } else {
            $_SESSION['error'] = 'Chyba při dokončování vakcinace';
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/vaccination-plan/workplace/' . $animal['workplace_id']));
        exit;
    }

    /**
     * Batch mark multiple vaccinations as completed
     */
    public function batchMarkCompleted() {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vaccination-plan');
            exit;
        }

        $planIds = $_POST['plan_ids'] ?? [];
        if (empty($planIds) || !is_array($planIds)) {
            $_SESSION['error'] = 'Nebyla vybrána žádná vakcinace';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/vaccination-plan'));
            exit;
        }

        $vaccinationModel = new VaccinationPlan();
        $userModel = new User();

        // Verify permissions for all plans
        foreach ($planIds as $planId) {
            $plan = $vaccinationModel->findById($planId);
            if (!$plan) {
                $_SESSION['error'] = 'Jeden nebo více vakcinačních plánů nenalezeno';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/vaccination-plan'));
                exit;
            }

            $animalModel = new Animal();
            $animal = $animalModel->findById($plan['animal_id']);

            if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'vaccination', 'edit')) {
                $_SESSION['error'] = 'Nemáte oprávnění dokončit některé vakcinace';
                header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/vaccination-plan'));
                exit;
            }
        }

        $data = [
            'administered_date' => $_POST['administered_date'] ?? date('Y-m-d'),
            'administered_by' => Auth::userId(),
            'completion_notes' => $_POST['completion_notes'] ?? ''
        ];

        $result = $vaccinationModel->batchMarkAsCompleted($planIds, $data);

        if ($result) {
            $_SESSION['success'] = 'Vybrané vakcinace byly označeny jako dokončené (' . count($planIds) . ' ks)';
        } else {
            $_SESSION['error'] = 'Chyba při hromadném dokončování vakcinací';
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/vaccination-plan'));
        exit;
    }

    /**
     * Delete vaccination plan
     */
    public function delete($id) {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /vaccination-plan');
            exit;
        }

        $vaccinationModel = new VaccinationPlan();
        $plan = $vaccinationModel->findById($id);

        if (!$plan) {
            $_SESSION['error'] = 'Vakcinační plán nenalezen';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/vaccination-plan'));
            exit;
        }

        // Check permissions
        $userModel = new User();
        $animalModel = new Animal();
        $animal = $animalModel->findById($plan['animal_id']);

        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'vaccination', 'edit')) {
            $_SESSION['error'] = 'Nemáte oprávnění smazat vakcinační plán';
            header('Location: /vaccination-plan');
            exit;
        }

        $result = $vaccinationModel->delete($id);

        if ($result) {
            $_SESSION['success'] = 'Vakcinační plán byl smazán';
        } else {
            $_SESSION['error'] = 'Chyba při mazání vakcinačního plánu';
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/vaccination-plan/workplace/' . $animal['workplace_id']));
        exit;
    }

    // Get categories for a workplace
    public function getCategories($workplaceId) {
        Auth::requireLogin();
        header('Content-Type: application/json');

        try {
            $db = Database::getInstance()->getConnection();

            // Get all categories from animal_categories table with animal counts
            $stmt = $db->prepare("
                SELECT
                    ac.name as category,
                    COUNT(a.id) as animal_count
                FROM animal_categories ac
                LEFT JOIN animals a ON a.animal_category_id = ac.id
                    AND a.workplace_id = ?
                    AND a.current_status = 'active'
                WHERE ac.workplace_id = ?
                GROUP BY ac.id, ac.name
                ORDER BY ac.name
            ");
            $stmt->execute([$workplaceId, $workplaceId]);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'categories' => $categories
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Chyba při načítání kategorií: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Add category to animal_categories table
    public function addCategory($workplaceId) {
        Auth::requireLogin();
        header('Content-Type: application/json');

        try {
            $userModel = new User();
            if (!$userModel->hasPermission(Auth::userId(), $workplaceId, 'vaccination', 'edit')) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Nemáte oprávnění upravovat toto pracoviště'
                ]);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $category = trim($input['category'] ?? '');

            if (empty($category)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Kategorie nemůže být prázdná'
                ]);
                exit;
            }

            $db = Database::getInstance()->getConnection();

            // Insert category into animal_categories table (IGNORE if already exists)
            $stmt = $db->prepare("
                INSERT INTO animal_categories (workplace_id, name)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE name = name
            ");
            $stmt->execute([$workplaceId, $category]);

            echo json_encode([
                'success' => true,
                'message' => 'Kategorie byla úspěšně přidána'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Chyba: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Remove category from animal_categories table (foreign key will set animals to NULL)
    public function removeCategory($workplaceId) {
        Auth::requireLogin();
        header('Content-Type: application/json');

        try {
            $userModel = new User();
            if (!$userModel->hasPermission(Auth::userId(), $workplaceId, 'vaccination', 'edit')) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Nemáte oprávnění upravovat toto pracoviště'
                ]);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $category = trim($input['category'] ?? '');

            if (empty($category)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Kategorie nemůže být prázdná'
                ]);
                exit;
            }

            $db = Database::getInstance()->getConnection();

            // Delete from animal_categories table
            // Foreign key constraint will automatically set animal_category_id to NULL for affected animals
            $stmt = $db->prepare("
                DELETE FROM animal_categories
                WHERE workplace_id = ? AND name = ?
            ");
            $stmt->execute([$workplaceId, $category]);

            echo json_encode([
                'success' => true,
                'message' => 'Kategorie byla úspěšně odebrána'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Chyba při odebírání kategorie: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Get animals for a workplace (API endpoint)
    public function getAnimalsForWorkplace($workplaceId) {
        Auth::requireLogin();
        header('Content-Type: application/json');

        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("
                SELECT
                    a.id,
                    a.name,
                    a.identifier,
                    a.species,
                    ac.name as animal_category
                FROM animals a
                LEFT JOIN animal_categories ac ON a.animal_category_id = ac.id
                WHERE a.workplace_id = ? AND a.current_status = 'active'
                ORDER BY ac.name, a.name
            ");
            $stmt->execute([$workplaceId]);
            $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'animals' => $animals
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Chyba při načítání zvířat: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Get categories with their animals nested
    public function getCategoriesWithAnimals($workplaceId) {
        Auth::requireLogin();
        header('Content-Type: application/json');

        try {
            $db = Database::getInstance()->getConnection();

            // Get all categories from animal_categories table
            $stmt = $db->prepare("
                SELECT id, name
                FROM animal_categories
                WHERE workplace_id = ?
                ORDER BY name
            ");
            $stmt->execute([$workplaceId]);
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // For each category, get its animals
            foreach ($categories as &$category) {
                $stmt = $db->prepare("
                    SELECT id, name, identifier, species
                    FROM animals
                    WHERE workplace_id = ?
                      AND animal_category_id = ?
                      AND current_status = 'active'
                    ORDER BY name
                ");
                $stmt->execute([$workplaceId, $category['id']]);
                $category['animals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            // Get uncategorized animals
            $stmt = $db->prepare("
                SELECT id, name, identifier, species
                FROM animals
                WHERE workplace_id = ?
                  AND (animal_category_id IS NULL OR animal_category_id = 0)
                  AND current_status = 'active'
                ORDER BY name
            ");
            $stmt->execute([$workplaceId]);
            $uncategorized = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'categories' => $categories,
                'uncategorized' => $uncategorized
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Chyba při načítání dat: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    // Bulk assign category to multiple animals
    public function bulkAssignCategory() {
        Auth::requireLogin();
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $animalIds = $input['animal_ids'] ?? [];
            $category = trim($input['category'] ?? '');
            $workplaceId = $input['workplace_id'] ?? null;

            if (empty($animalIds) || !is_array($animalIds)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Nebyly vybrány žádné zvířata'
                ]);
                exit;
            }

            if (empty($category)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Kategorie nemůže být prázdná'
                ]);
                exit;
            }

            if (!$workplaceId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Chybí workplace_id'
                ]);
                exit;
            }

            $userModel = new User();
            if (!$userModel->hasPermission(Auth::userId(), $workplaceId, 'vaccination', 'edit')) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'error' => 'Nemáte oprávnění upravovat toto pracoviště'
                ]);
                exit;
            }

            $db = Database::getInstance()->getConnection();

            // Get or create category ID
            $stmt = $db->prepare("
                SELECT id FROM animal_categories
                WHERE workplace_id = ? AND name = ?
            ");
            $stmt->execute([$workplaceId, $category]);
            $categoryId = $stmt->fetchColumn();

            if (!$categoryId) {
                $stmt = $db->prepare("
                    INSERT INTO animal_categories (workplace_id, name)
                    VALUES (?, ?)
                ");
                $stmt->execute([$workplaceId, $category]);
                $categoryId = $db->lastInsertId();
            }

            // Update all selected animals
            $placeholders = str_repeat('?,', count($animalIds) - 1) . '?';
            $stmt = $db->prepare("
                UPDATE animals
                SET animal_category_id = ?
                WHERE id IN ($placeholders) AND workplace_id = ?
            ");

            $params = array_merge([$categoryId], $animalIds, [$workplaceId]);
            $stmt->execute($params);

            echo json_encode([
                'success' => true,
                'message' => 'Kategorie byla úspěšně přiřazena ' . count($animalIds) . ' zvířatům'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Chyba při přiřazování kategorie: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}
