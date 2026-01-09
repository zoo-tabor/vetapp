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
        $workplaces = $userModel->getWorkplacePermissions(Auth::userId());

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

        // Auto-update overdue status
        $vaccinationModel->updateOverdueStatus();

        // Get vaccination plans separated by status
        $overduePlans = $vaccinationModel->getByWorkplace($id, ['status' => 'overdue']);
        $upcomingPlans = $vaccinationModel->getUpcoming($id, 90); // Next 90 days
        $completedPlans = $vaccinationModel->getByWorkplace($id, ['status' => 'completed']);

        // Get statistics
        $stats = $vaccinationModel->getStats($id);

        $canEdit = $userModel->hasPermission(Auth::userId(), $id, 'edit');

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

        $canEdit = $userModel->hasPermission(Auth::userId(), $id, 'edit');

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
        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit')) {
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

        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit')) {
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

            if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit')) {
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

        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit')) {
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
}
