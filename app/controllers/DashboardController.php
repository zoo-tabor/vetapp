<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Workplace.php';

class DashboardController {
    
    public function index() {
        Auth::requireLogin();

        try {
            // Check which app the user is currently in
            $currentApp = $_SESSION['current_app'] ?? 'parasitology';

            // Route to appropriate controller
            if ($currentApp === 'biochemistry') {
                require_once __DIR__ . '/BiochemistryController.php';
                $controller = new BiochemistryController();
                $controller->index();
                return;
            }

            if ($currentApp === 'urineanalysis') {
                require_once __DIR__ . '/UrineAnalysisController.php';
                $controller = new UrineAnalysisController();
                $controller->index();
                return;
            }

            // Default to parasitology
            $userModel = new User();
            $workplaces = $userModel->getWorkplacePermissions(Auth::userId());

            View::render('dashboard/main', [
                'layout' => 'main',
                'title' => 'Přehled pracovišť',
                'workplaces' => $workplaces
            ]);
        } catch (Exception $e) {
            error_log("DashboardController::index error: " . $e->getMessage());
            die("Chyba při načítání dashboardu: " . $e->getMessage());
        }
    }
    
    public function workplace($id) {
        Auth::requireLogin();
        
        try {
            // Kontrola že ID je číslo
            if (!is_numeric($id)) {
                die('Neplatné ID pracoviště');
            }
            
            $userModel = new User();
            
            // Kontrola oprávnění - Admin má přístup všude
            if (Auth::role() !== 'admin') {
                if (!$userModel->hasPermission(Auth::userId(), $id, 'view')) {
                    die('Nemáte oprávnění k tomuto pracovišti');
                }
            }
            
            $workplaceModel = new Workplace();
            $workplace = $workplaceModel->findById($id);
            
            if (!$workplace) {
                die('Pracoviště nenalezeno');
            }
            
            // Načíst data
            $stats = $workplaceModel->getStats($id);
            $enclosures = $workplaceModel->getEnclosures($id);
            
            // Kontrola editačních práv - Admin má vždy právo editovat
            if (Auth::role() === 'admin') {
                $canEdit = true;
            } else {
                $canEdit = $userModel->hasPermission(Auth::userId(), $id, 'edit');
            }
            
            // Renderovat view
            View::render('dashboard/workplace', [
                'layout' => 'main',
                'title' => $workplace['name'],
                'workplace' => $workplace,
                'stats' => $stats,
                'enclosures' => $enclosures,
                'canEdit' => $canEdit
            ]);
            
        } catch (Exception $e) {
            error_log("DashboardController::workplace error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // V produkci
            if (!isset($_GET['debug'])) {
                die("Chyba při načítání pracoviště. Kontaktujte administrátora.");
            }
            
            // Debug režim
            echo "<pre>";
            echo "CHYBA: " . htmlspecialchars($e->getMessage()) . "\n\n";
            echo "STACK TRACE:\n" . htmlspecialchars($e->getTraceAsString());
            echo "</pre>";
            die();
        }
    }

    public function search($workplaceId) {
        Auth::requireLogin();

        try {
            $userModel = new User();
            $workplaceModel = new Workplace();

            // Check permissions
            if (!$userModel->hasPermission(Auth::userId(), $workplaceId)) {
                die('Nemáte oprávnění k tomuto pracovišti');
            }

            $workplace = $workplaceModel->findById($workplaceId);
            if (!$workplace) {
                die('Pracoviště nenalezeno');
            }

            require_once __DIR__ . '/../core/Database.php';
            $db = Database::getInstance()->getConnection();

            // Get all unique parasites found in this workplace
            $stmtParasites = $db->prepare("
                SELECT DISTINCT parasite_found
                FROM examinations
                WHERE workplace_id = ?
                AND parasite_found IS NOT NULL
                AND parasite_found != ''
                AND parasite_found != 'negative'
                AND parasite_found != 'neg.'
                ORDER BY parasite_found ASC
            ");
            $stmtParasites->execute([$workplaceId]);
            $parasites = $stmtParasites->fetchAll(PDO::FETCH_COLUMN);

            // Get all unique antiparasitic drugs used in this workplace
            $stmtDrugs = $db->prepare("
                SELECT DISTINCT medication
                FROM dewormings
                WHERE workplace_id = ?
                AND medication IS NOT NULL AND medication != ''
                ORDER BY medication ASC
            ");
            $stmtDrugs->execute([$workplaceId]);
            $drugs = $stmtDrugs->fetchAll(PDO::FETCH_COLUMN);

            View::render('dashboard/search', [
                'layout' => 'main',
                'title' => 'Vyhledávání - ' . $workplace['name'],
                'workplace' => $workplace,
                'parasites' => $parasites ?? [],
                'drugs' => $drugs ?? []
            ]);
        } catch (Exception $e) {
            error_log("DashboardController::search error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());

            die("Chyba při načítání vyhledávání: " . $e->getMessage());
        }
    }
}