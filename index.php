<?php
/**
 * Entry point aplikace - WEDOS Version (bez public složky)
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Vypnuto pro produkci
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');

// Nastavení cest - BEZ public složky!
define('ROOT_PATH', __DIR__);
define('APP_PATH', ROOT_PATH . '/app');

// Načtení .env souboru
require_once APP_PATH . '/helpers/env.php';
loadEnv(ROOT_PATH . '/.env');

// Načtení konfigurace
$config = require APP_PATH . '/config/config.php';
date_default_timezone_set($config['timezone']);

// Načtení core tříd
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/core/Router.php';
require_once APP_PATH . '/core/Auth.php';
require_once APP_PATH . '/core/View.php';

// Spuštění session
Auth::init();

// Načtení pomocných funkcí
require_once APP_PATH . '/helpers/functions.php';

// Derive current_app from URL so the navbar always reflects the active section.
// This runs on every request so navigating directly to any section URL is enough.
$_uri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
if (strpos($_uri, '/biochemistry') === 0) {
    $_SESSION['current_app'] = 'biochemistry';
} elseif (strpos($_uri, '/urineanalysis') === 0) {
    $_SESSION['current_app'] = 'urineanalysis';
} elseif (strpos($_uri, '/vaccination-plan') === 0) {
    $_SESSION['current_app'] = 'vaccination';
} elseif (strpos($_uri, '/warehouse') === 0) {
    $_SESSION['current_app'] = 'warehouse';
} elseif (strpos($_uri, '/animals') === 0) {
    $_SESSION['current_app'] = 'animals';
} elseif (strpos($_uri, '/parasitology') === 0) {
    $_SESSION['current_app'] = 'parasitology';
}
// Admin / user / login / etc. leave the session unchanged so the last section stays highlighted.
unset($_uri);

// Global exception handler
set_exception_handler(function(Throwable $e) {
    error_log("Uncaught exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    if (!headers_sent()) {
        http_response_code(500);
    }
    require_once APP_PATH . '/views/errors/500.php';
    exit;
});

// Vytvoření routeru
$router = new Router();

// ============================================
// ROUTES
// ============================================

// Auth routes
$router->get('/login', function() {
    if (Auth::check()) {
        header('Location: /animals');
        exit;
    }
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->showLogin();
});

$router->post('/login', function() {
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->login();
});

$router->get('/logout', function() {
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->logout();
});

// Dashboard routes
$router->get('/', function() {
    require_once APP_PATH . '/controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->index();
});

$router->get('/workplace/:id', function($id) {
    require_once APP_PATH . '/controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->workplace($id);
});

// Animal routes
$router->get('/workplace/:workplace_id/animals', function($workplaceId) {
    require_once APP_PATH . '/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->list($workplaceId);
});

$router->get('/workplace/:workplace_id/animals/create', function($workplaceId) {
    require_once APP_PATH . '/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->create($workplaceId);
});

$router->post('/workplace/:workplace_id/animals/create', function($workplaceId) {
    require_once APP_PATH . '/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->create($workplaceId);
});

$router->get('/workplace/:workplace_id/animals/:id', function($workplaceId, $id) {
    require_once APP_PATH . '/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->detail($id);
});

$router->post('/animals/:id/update-next-test', function($id) {
    require_once APP_PATH . '/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->updateNextTest($id);
});

$router->post('/animals/:id/update', function($id) {
    require_once APP_PATH . '/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->update($id);
});

$router->post('/animals/:id/weight', function($id) {
    require_once APP_PATH . '/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->addWeight($id);
});

$router->post('/animals/:id/protection', function($id) {
    require_once APP_PATH . '/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->updateProtection($id);
});

$router->post('/animals/:id/notes', function($id) {
    require_once APP_PATH . '/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->updateNotes($id);
});

// Enclosure routes
$router->post('/workplace/:workplace_id/enclosures/create', function($workplaceId) {
    require_once APP_PATH . '/controllers/EnclosureController.php';
    $controller = new EnclosureController();
    $controller->create($workplaceId);
});

// Examination routes
$router->post('/workplace/:workplace_id/examinations/create', function($workplaceId) {
    require_once APP_PATH . '/controllers/ExaminationController.php';
    $controller = new ExaminationController();
    $controller->create($workplaceId);
});

$router->get('/examinations/details', function() {
    require_once APP_PATH . '/controllers/ExaminationController.php';
    $controller = new ExaminationController();
    $controller->getDetails();
});

$router->get('/examinations/by-animals', function() {
    require_once APP_PATH . '/controllers/ExaminationController.php';
    $controller = new ExaminationController();
    $controller->getByAnimals();
});

$router->post('/examinations/update', function() {
    require_once APP_PATH . '/controllers/ExaminationController.php';
    $controller = new ExaminationController();
    $controller->update();
});

$router->post('/examinations/delete', function() {
    require_once APP_PATH . '/controllers/ExaminationController.php';
    $controller = new ExaminationController();
    $controller->delete();
});

// Deworming routes
$router->post('/dewormings/create', function() {
    require_once APP_PATH . '/controllers/DewormingController.php';
    $controller = new DewormingController();
    $controller->create();
});

// Print routes
$router->get('/print/history', function() {
    require_once APP_PATH . '/controllers/PrintController.php';
    $controller = new PrintController();
    $controller->history();
});

// Examination detail (připraveno)
$router->get('/examinations/:id', function($id) {
    echo "Detail vyšetření #" . $id . " - připraveno k implementaci";
});

// Search routes
$router->get('/workplace/:id/search', function($id) {
    require_once APP_PATH . '/controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->search($id);
});

// Admin routes
$router->get('/admin/settings', function() {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->settings();
});

$router->get('/admin/users/:id', function($id) {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->getUser($id);
});

$router->post('/admin/users/create', function() {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->createUser();
});

$router->post('/admin/users/:id', function($id) {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->updateUser($id);
});

$router->get('/admin/users/:id/permissions', function($id) {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->getUserPermissions($id);
});

$router->post('/admin/users/:id/permissions', function($id) {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->saveUserPermissions($id);
});

$router->post('/admin/users/:id/resend-password', function($id) {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->resendPasswordSetup($id);
});

$router->post('/admin/users/:id/delete', function($id) {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->deleteUser($id);
});

// Animal keeper assignment routes
$router->get('/admin/animals/:id/keeper', function($id) {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->getAnimalKeeper($id);
});

$router->post('/admin/animals/:id/keeper', function($id) {
    require_once APP_PATH . '/controllers/AdminController.php';
    $controller = new AdminController();
    $controller->updateAnimalKeeper($id);
});

// Password setup routes
$router->get('/setup-password', function() {
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->setupPassword();
});

$router->post('/setup-password', function() {
    require_once APP_PATH . '/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->processPasswordSetup();
});

// User settings routes
$router->get('/user/settings', function() {
    require_once APP_PATH . '/controllers/UserController.php';
    $controller = new UserController();
    $controller->settings();
});

$router->post('/user/settings/update', function() {
    require_once APP_PATH . '/controllers/UserController.php';
    $controller = new UserController();
    $controller->updateSettings();
});

// App switching routes
$router->get('/app/switch/:app', function($app) {
    require_once APP_PATH . '/controllers/AppController.php';
    $controller = new AppController();
    $controller->switchApp($app);
});

// Parasitology routes
$router->get('/parasitology', function() {
    $_SESSION['current_app'] = 'parasitology';
    require_once APP_PATH . '/controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->index();
});

$router->get('/parasitology/workplace/:id', function($id) {
    $_SESSION['current_app'] = 'parasitology';
    require_once APP_PATH . '/controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->workplace($id);
});

// Biochemistry routes
$router->get('/biochemistry/workplace/:id', function($id) {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->workplace($id);
});

$router->get('/biochemistry/workplace/:id/graph', function($id) {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->workplaceSearch($id);
});

$router->get('/biochemistry/workplace/:id/advanced-search', function($id) {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->advancedSearch($id);
});

$router->get('/biochemistry/animal/:id', function($id) {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->animal($id);
});

$router->get('/biochemistry/animal/:id/comprehensive-table', function($id) {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->comprehensiveTable($id);
});

$router->get('/biochemistry/animal/:id/print', function($id) {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->printPreview($id);
});

$router->get('/biochemistry/animal/:id/graph', function($id) {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->search($id);
});

$router->post('/biochemistry/animal/:id/graph', function($id) {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->graph($id);
});

$router->post('/biochemistry/tests/create', function() {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->createTest();
});

$router->get('/api/biochemistry/search-animal', function() {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->searchAnimalApi();
});

$router->get('/api/biochemistry/search-parameter', function() {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->searchParameterApi();
});

$router->get('/biochemistry/reference-ranges', function() {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->referenceRanges();
});

$router->post('/biochemistry/reference-ranges/save', function() {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->saveReferenceRanges();
});

$router->post('/biochemistry/reference-ranges/add-source', function() {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->addReferenceSource();
});

$router->post('/biochemistry/result/:id/update', function($id) {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->updateResult($id);
});

// Biochemistry Import routes
$router->get('/biochemistry/import', function() {
    require_once APP_PATH . '/controllers/BiochemistryImportController.php';
    $controller = new BiochemistryImportController();
    $controller->index();
});

$router->post('/biochemistry/import/upload', function() {
    require_once APP_PATH . '/controllers/BiochemistryImportController.php';
    $controller = new BiochemistryImportController();
    $controller->upload();
});

$router->get('/biochemistry/import/preview', function() {
    require_once APP_PATH . '/controllers/BiochemistryImportController.php';
    $controller = new BiochemistryImportController();
    $controller->preview();
});

$router->post('/biochemistry/import/assign-animal', function() {
    require_once APP_PATH . '/controllers/BiochemistryImportController.php';
    $controller = new BiochemistryImportController();
    $controller->assignAnimal();
});

$router->post('/biochemistry/import/execute', function() {
    require_once APP_PATH . '/controllers/BiochemistryImportController.php';
    $controller = new BiochemistryImportController();
    $controller->execute();
});

// Biochemistry index route
$router->get('/biochemistry', function() {
    require_once APP_PATH . '/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->index();
});

// Urine Analysis routes
$router->get('/urineanalysis', function() {
    require_once APP_PATH . '/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->index();
});

$router->get('/urineanalysis/workplace/:id', function($id) {
    require_once APP_PATH . '/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->workplace($id);
});

$router->get('/urineanalysis/workplace/:id/graph', function($id) {
    require_once APP_PATH . '/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->workplaceSearch($id);
});

$router->get('/urineanalysis/animal/:id', function($id) {
    require_once APP_PATH . '/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->animal($id);
});

$router->get('/urineanalysis/animal/:id/comprehensive-table', function($id) {
    require_once APP_PATH . '/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->comprehensiveTable($id);
});

$router->get('/urineanalysis/animal/:id/graph', function($id) {
    require_once APP_PATH . '/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->search($id);
});

$router->post('/urineanalysis/animal/:id/graph', function($id) {
    require_once APP_PATH . '/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->showGraph($id);
});

$router->post('/urineanalysis/tests/create', function() {
    require_once APP_PATH . '/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->createTest();
});

$router->get('/urineanalysis/reference-ranges', function() {
    require_once APP_PATH . '/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->referenceRanges();
});

$router->post('/urineanalysis/reference-ranges/save', function() {
    require_once APP_PATH . '/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->saveReferenceRanges();
});

$router->post('/urineanalysis/result/:id/update', function($id) {
    require_once APP_PATH . '/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->updateResult($id);
});

// Vaccination Plan routes

// Category Management API routes (must come BEFORE other routes)
$router->get('/vaccination-plan/categories/:workplaceId', function($workplaceId) {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->getCategories($workplaceId);
});

$router->post('/vaccination-plan/categories/:workplaceId/add', function($workplaceId) {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->addCategory($workplaceId);
});

$router->post('/vaccination-plan/categories/:workplaceId/remove', function($workplaceId) {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->removeCategory($workplaceId);
});

$router->get('/vaccination-plan/categories-with-animals/:workplaceId', function($workplaceId) {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->getCategoriesWithAnimals($workplaceId);
});

// API routes for bulk animal category assignment
$router->get('/api/workplace/:workplaceId/animals', function($workplaceId) {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->getAnimalsForWorkplace($workplaceId);
});

$router->post('/vaccination-plan/bulk-assign-category', function() {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->bulkAssignCategory();
});

// Main vaccination plan routes
$router->get('/vaccination-plan', function() {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->index();
});

$router->get('/vaccination-plan/workplace/:id', function($id) {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->workplace($id);
});

$router->get('/vaccination-plan/planning-grid/:id', function($id) {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->planningGrid($id);
});

$router->post('/vaccination-plan/save', function() {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->save();
});

$router->post('/vaccination-plan/mark-completed/:id', function($id) {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->markCompleted($id);
});

$router->post('/vaccination-plan/batch-mark-completed', function() {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->batchMarkCompleted();
});

$router->post('/vaccination-plan/delete/:id', function($id) {
    require_once APP_PATH . '/controllers/VaccinationPlanController.php';
    $controller = new VaccinationPlanController();
    $controller->delete($id);
});

// Warehouse/Inventory routes
// IMPORTANT: Most specific routes MUST come first!

// Static routes first
$router->get('/warehouse/central', function() {
    require_once APP_PATH . '/controllers/WarehouseController.php';
    $controller = new WarehouseController();
    $controller->central();
});

$router->post('/warehouse/items/create', function() {
    require_once APP_PATH . '/controllers/WarehouseController.php';
    $controller = new WarehouseController();
    $controller->createItem();
});

$router->post('/warehouse/items/update', function() {
    require_once APP_PATH . '/controllers/WarehouseController.php';
    $controller = new WarehouseController();
    $controller->updateItem();
});

$router->post('/warehouse/movements/add', function() {
    require_once APP_PATH . '/controllers/WarehouseController.php';
    $controller = new WarehouseController();
    $controller->addMovement();
});

$router->post('/warehouse/consumption/set', function() {
    require_once APP_PATH . '/controllers/WarehouseController.php';
    $controller = new WarehouseController();
    $controller->setConsumption();
});

$router->post('/warehouse/inventory/save', function() {
    require_once APP_PATH . '/controllers/WarehouseController.php';
    $controller = new WarehouseController();
    $controller->saveInventory();
});

// Routes with parameters (more specific first)
$router->get('/warehouse/inventory/:id', function($id) {
    require_once APP_PATH . '/controllers/WarehouseController.php';
    $controller = new WarehouseController();
    $controller->inventory($id);
});

$router->get('/warehouse/item/:id', function($id) {
    require_once APP_PATH . '/controllers/WarehouseController.php';
    $controller = new WarehouseController();
    $controller->itemDetail($id);
});

$router->get('/warehouse/workplace/:id', function($id) {
    require_once APP_PATH . '/controllers/WarehouseController.php';
    $controller = new WarehouseController();
    $controller->workplace($id);
});

// Base warehouse route last
$router->get('/warehouse', function() {
    require_once APP_PATH . '/controllers/WarehouseController.php';
    $controller = new WarehouseController();
    $controller->index();
});

// Animals Database routes
$router->get('/animals', function() {
    $_SESSION['current_app'] = 'animals';
    require_once APP_PATH . '/controllers/AnimalDatabaseController.php';
    $controller = new AnimalDatabaseController();
    $controller->index();
});

$router->get('/animals/central', function() {
    $_SESSION['current_app'] = 'animals';
    require_once APP_PATH . '/controllers/AnimalDatabaseController.php';
    $controller = new AnimalDatabaseController();
    $controller->central();
});

$router->get('/animals/workplace/:id', function($id) {
    $_SESSION['current_app'] = 'animals';
    require_once APP_PATH . '/controllers/AnimalDatabaseController.php';
    $controller = new AnimalDatabaseController();
    $controller->workplace($id);
});

$router->get('/animals/detail/:id', function($id) {
    $_SESSION['current_app'] = 'animals';
    require_once APP_PATH . '/controllers/AnimalDatabaseController.php';
    $controller = new AnimalDatabaseController();
    $controller->detail($id);
});

// API routes
$router->get('/api/reference-ranges', function() {
    require_once APP_PATH . '/controllers/ApiController.php';
    $controller = new ApiController();
    $controller->getReferenceRanges();
});

$router->get('/api/urine-reference-ranges', function() {
    require_once APP_PATH . '/controllers/ApiController.php';
    $controller = new ApiController();
    $controller->getUrineReferenceRanges();
});

$router->get('/api/search/parasites', function() {
    require_once APP_PATH . '/controllers/ApiController.php';
    $controller = new ApiController();
    $controller->searchParasites();
});

$router->get('/api/search/drugs', function() {
    require_once APP_PATH . '/controllers/ApiController.php';
    $controller = new ApiController();
    $controller->searchDrugs();
});

// Migration runner route
$router->get('/migrate', function() {
    require_once APP_PATH . '/controllers/MigrateController.php';
    $controller = new MigrateController();
    $controller->run();
});

// ============================================
// DISPATCH
// ============================================

$router->dispatch();
