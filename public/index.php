<?php
/**
 * Entry point aplikace
 */

// Zapnout error reporting pro development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Nastavit časové pásmo
date_default_timezone_set('Europe/Prague');

// Načíst autoloader (v produkci by se použil Composer autoloader)
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/View.php';

// Inicializovat session
Auth::init();

// Vytvořit router
$router = new Router();

// === ROUTES ===

// Auth routes
$router->get('/login', function() {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->showLogin();
});

$router->post('/login', function() {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->login();
});

$router->get('/logout', function() {
    require_once __DIR__ . '/../app/controllers/AuthController.php';
    $controller = new AuthController();
    $controller->logout();
});

// Dashboard routes
$router->get('/', function() {
    require_once __DIR__ . '/../app/controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->index();
});

$router->get('/workplace/:id', function($id) {
    require_once __DIR__ . '/../app/controllers/DashboardController.php';
    $controller = new DashboardController();
    $controller->workplace($id);
});

// TEST: This route should appear - version 2024-12-19-v2
$router->get('/print/history', function() {
    require_once __DIR__ . '/../app/controllers/PrintController.php';
    $controller = new PrintController();
    $controller->history();
});

// Animal routes
$router->get('/workplace/:workplaceId/animals', function($workplaceId) {
    require_once __DIR__ . '/../app/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->list($workplaceId);
});

$router->get('/workplace/:workplaceId/animals/create', function($workplaceId) {
    require_once __DIR__ . '/../app/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->create($workplaceId);
});

$router->post('/workplace/:workplaceId/animals/create', function($workplaceId) {
    require_once __DIR__ . '/../app/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->create($workplaceId);
});

$router->get('/workplace/:workplaceId/animals/:id', function($workplaceId, $id) {
    require_once __DIR__ . '/../app/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->detail($id);
});

// Enclosure routes
$router->post('/workplace/:workplaceId/enclosures/create', function($workplaceId) {
    require_once __DIR__ . '/../app/controllers/EnclosureController.php';
    $controller = new EnclosureController();
    $controller->create($workplaceId);
});

// Examination routes
$router->post('/workplace/:workplaceId/examinations/create', function($workplaceId) {
    require_once __DIR__ . '/../app/controllers/ExaminationController.php';
    $controller = new ExaminationController();
    $controller->create($workplaceId);
});

// Deworming routes
$router->post('/dewormings/create', function() {
    require_once __DIR__ . '/../app/controllers/DewormingController.php';
    $controller = new DewormingController();
    $controller->create();
});

// Animal update routes
$router->post('/animals/:id/update-next-test', function($id) {
    require_once __DIR__ . '/../app/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->updateNextTest($id);
});

$router->post('/animals/:id/update', function($id) {
    require_once __DIR__ . '/../app/controllers/AnimalController.php';
    $controller = new AnimalController();
    $controller->update($id);
});

// Examination routes
$router->get('/examinations/details', function() {
    require_once __DIR__ . '/../app/controllers/ExaminationController.php';
    $controller = new ExaminationController();
    $controller->getDetails();
});

$router->post('/examinations/update', function() {
    require_once __DIR__ . '/../app/controllers/ExaminationController.php';
    $controller = new ExaminationController();
    $controller->update();
});

$router->post('/examinations/delete', function() {
    require_once __DIR__ . '/../app/controllers/ExaminationController.php';
    $controller = new ExaminationController();
    $controller->delete();
});

// Search route (připraveno pro budoucí použití)
$router->get('/workplace/:workplaceId/search', function($workplaceId) {
    echo "Vyhledávání v pracovišti #$workplaceId - připraveno pro implementaci";
});

// Biochemistry routes
$router->get('/biochemistry', function() {
    require_once __DIR__ . '/../app/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->index();
});

$router->get('/biochemistry/workplace/:id', function($id) {
    require_once __DIR__ . '/../app/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->workplace($id);
});

$router->get('/biochemistry/animal/:id', function($id) {
    require_once __DIR__ . '/../app/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->animal($id);
});

$router->post('/biochemistry/tests/create', function() {
    require_once __DIR__ . '/../app/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->createTest();
});

$router->get('/biochemistry/reference-ranges', function() {
    require_once __DIR__ . '/../app/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->referenceRanges();
});

$router->post('/biochemistry/reference-ranges/save', function() {
    require_once __DIR__ . '/../app/controllers/BiochemistryController.php';
    $controller = new BiochemistryController();
    $controller->saveReferenceRanges();
});

// Biochemistry Import routes
$router->get('/biochemistry/import', function() {
    require_once __DIR__ . '/../app/controllers/BiochemistryImportController.php';
    $controller = new BiochemistryImportController();
    $controller->index();
});

$router->post('/biochemistry/import/upload', function() {
    require_once __DIR__ . '/../app/controllers/BiochemistryImportController.php';
    $controller = new BiochemistryImportController();
    $controller->upload();
});

$router->get('/biochemistry/import/preview', function() {
    require_once __DIR__ . '/../app/controllers/BiochemistryImportController.php';
    $controller = new BiochemistryImportController();
    $controller->preview();
});

$router->post('/biochemistry/import/execute', function() {
    require_once __DIR__ . '/../app/controllers/BiochemistryImportController.php';
    $controller = new BiochemistryImportController();
    $controller->execute();
});

// Urine Analysis routes
$router->get('/urineanalysis', function() {
    error_log("URINEANALYSIS ROUTE CALLED!");
    require_once __DIR__ . '/../app/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->index();
});

$router->get('/urineanalysis/workplace/:id', function($id) {
    require_once __DIR__ . '/../app/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->workplace($id);
});

$router->get('/urineanalysis/animal/:id', function($id) {
    require_once __DIR__ . '/../app/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->animal($id);
});

$router->post('/urineanalysis/tests/create', function() {
    require_once __DIR__ . '/../app/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->createTest();
});

$router->get('/urineanalysis/reference-ranges', function() {
    require_once __DIR__ . '/../app/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->referenceRanges();
});

$router->post('/urineanalysis/reference-ranges/save', function() {
    require_once __DIR__ . '/../app/controllers/UrineAnalysisController.php';
    $controller = new UrineAnalysisController();
    $controller->saveReferenceRanges();
});

// App switching route
$router->get('/switch-app/:app', function($app) {
    require_once __DIR__ . '/../app/controllers/AppController.php';
    $controller = new AppController();
    $controller->switchApp($app);
});

// Zpracovat request
$router->dispatch();