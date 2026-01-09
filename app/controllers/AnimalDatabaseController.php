<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/Animal.php';

class AnimalDatabaseController {

    public function index() {
        Auth::requireLogin();

        // Get user's workplaces
        $userModel = new User();
        $workplaceModel = new Workplace();

        $workplaces = $userModel->getWorkplacePermissions(Auth::userId());

        View::render('animals_database/dashboard', [
            'layout' => 'main',
            'title' => 'Seznam zvířat - Dashboard',
            'workplaces' => $workplaces
        ]);
    }

    public function central() {
        Auth::requireLogin();

        $db = Database::getInstance()->getConnection();
        $userModel = new User();

        // Get all animals from all workplaces the user has access to
        $userWorkplaces = $userModel->getWorkplacePermissions(Auth::userId());
        $workplaceIds = array_column($userWorkplaces, 'id');

        if (empty($workplaceIds)) {
            View::render('animals_database/central', [
                'layout' => 'main',
                'title' => 'Seznam zvířat - Centrální databáze',
                'animals' => [],
                'workplaces' => []
            ]);
            return;
        }

        // Get all animals from accessible workplaces
        $placeholders = str_repeat('?,', count($workplaceIds) - 1) . '?';
        $stmt = $db->prepare("
            SELECT
                a.*,
                w.name as workplace_name,
                w.code as workplace_code
            FROM animals a
            JOIN workplaces w ON a.workplace_id = w.id
            WHERE a.workplace_id IN ($placeholders)
            ORDER BY a.current_status, a.species, a.name
        ");
        $stmt->execute($workplaceIds);
        $allAnimals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Separate active and deceased animals
        $animals = [];
        $deceasedAnimals = [];
        foreach ($allAnimals as $animal) {
            if ($animal['current_status'] === 'deceased') {
                $deceasedAnimals[] = $animal;
            } else if ($animal['current_status'] === 'active') {
                $animals[] = $animal;
            }
        }

        // Get workplaces for filtering
        $workplaces = $userWorkplaces;

        View::render('animals_database/central', [
            'layout' => 'main',
            'title' => 'Seznam zvířat - Centrální databáze',
            'animals' => $animals,
            'deceasedAnimals' => $deceasedAnimals,
            'workplaces' => $workplaces,
            'canEdit' => Auth::isAdmin() || Auth::role() === 'user_edit'
        ]);
    }

    public function workplace($id) {
        Auth::requireLogin();

        $userModel = new User();
        $workplaceModel = new Workplace();
        $animalModel = new Animal();

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

        // Get all animals for this workplace
        $animals = $animalModel->getByWorkplace($id);

        // Get current user's username for filtering assigned animals
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([Auth::userId()]);
        $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentUsername = $currentUser['username'] ?? null;

        // Group animals by species (separate active, deceased, and my animals)
        $animalsBySpecies = [];
        $deceasedAnimalsBySpecies = [];
        $myAnimalsBySpecies = [];

        foreach ($animals as $animal) {
            $species = $animal['species'] ?: 'Nezadáno';

            // Check if this animal is assigned to current user
            if ($currentUsername && $animal['assigned_user'] === $currentUsername && $animal['current_status'] === 'active') {
                if (!isset($myAnimalsBySpecies[$species])) {
                    $myAnimalsBySpecies[$species] = [];
                }
                $myAnimalsBySpecies[$species][] = $animal;
            }

            if ($animal['current_status'] === 'deceased') {
                // Group deceased animals separately
                if (!isset($deceasedAnimalsBySpecies[$species])) {
                    $deceasedAnimalsBySpecies[$species] = [];
                }
                $deceasedAnimalsBySpecies[$species][] = $animal;
            } else {
                // Group active animals
                if (!isset($animalsBySpecies[$species])) {
                    $animalsBySpecies[$species] = [];
                }
                $animalsBySpecies[$species][] = $animal;
            }
        }

        // Get all enclosures for this workplace
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT * FROM enclosures
            WHERE workplace_id = ? AND is_active = 1
            ORDER BY name
        ");
        $stmt->execute([$id]);
        $enclosures = $stmt->fetchAll(PDO::FETCH_ASSOC);

        View::render('animals_database/workplace', [
            'layout' => 'main',
            'title' => 'Seznam zvířat - ' . $workplace['name'],
            'workplace' => $workplace,
            'animalsBySpecies' => $animalsBySpecies,
            'deceasedAnimalsBySpecies' => $deceasedAnimalsBySpecies,
            'myAnimalsBySpecies' => $myAnimalsBySpecies,
            'enclosures' => $enclosures,
            'canEdit' => $userModel->hasPermission(Auth::userId(), $id, 'edit')
        ]);
    }

    public function detail($id) {
        Auth::requireLogin();

        $db = Database::getInstance()->getConnection();
        $animalModel = new Animal();
        $userModel = new User();
        $workplaceModel = new Workplace();

        // Get animal details
        $animal = $animalModel->findById($id);
        if (!$animal) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Chyba',
                'message' => 'Zvíře nenalezeno'
            ]);
            return;
        }

        // Get workplace name
        $workplace = $workplaceModel->findById($animal['workplace_id']);
        $animal['workplace_name'] = $workplace['name'] ?? 'Neznámé pracoviště';

        // Check permissions
        if (!$userModel->hasPermission(Auth::userId(), $animal['workplace_id'])) {
            View::render('error', [
                'layout' => 'main',
                'title' => 'Přístup odepřen',
                'message' => 'Nemáte oprávnění k tomuto pracovišti'
            ]);
            return;
        }

        // Get parasitology data (examinations)
        $stmtExam = $db->prepare("
            SELECT
                e.*,
                w.name as workplace_name
            FROM examinations e
            LEFT JOIN workplaces w ON e.workplace_id = w.id
            WHERE e.animal_id = ?
            ORDER BY e.examination_date DESC
            LIMIT 5
        ");
        $stmtExam->execute([$id]);
        $examinations = $stmtExam->fetchAll(PDO::FETCH_ASSOC);

        // Get biochemistry data
        $stmtBiochem = $db->prepare("
            SELECT
                bt.*,
                COUNT(br.id) as result_count
            FROM biochemistry_tests bt
            LEFT JOIN biochemistry_results br ON bt.id = br.test_id
            WHERE bt.animal_id = ?
            GROUP BY bt.id
            ORDER BY bt.test_date DESC
            LIMIT 5
        ");
        $stmtBiochem->execute([$id]);
        $biochemistryTests = $stmtBiochem->fetchAll(PDO::FETCH_ASSOC);

        // Get hematology data
        $stmtHemato = $db->prepare("
            SELECT
                ht.*,
                COUNT(hr.id) as result_count
            FROM hematology_tests ht
            LEFT JOIN hematology_results hr ON ht.id = hr.test_id
            WHERE ht.animal_id = ?
            GROUP BY ht.id
            ORDER BY ht.test_date DESC
            LIMIT 5
        ");
        $stmtHemato->execute([$id]);
        $hematologyTests = $stmtHemato->fetchAll(PDO::FETCH_ASSOC);

        // Get urine analysis data
        $stmtUrine = $db->prepare("
            SELECT
                ut.*,
                COUNT(ur.id) as result_count
            FROM urine_tests ut
            LEFT JOIN urine_results ur ON ut.id = ur.test_id
            WHERE ut.animal_id = ?
            GROUP BY ut.id
            ORDER BY ut.test_date DESC
            LIMIT 5
        ");
        $stmtUrine->execute([$id]);
        $urineTests = $stmtUrine->fetchAll(PDO::FETCH_ASSOC);

        View::render('animals_database/detail', [
            'layout' => 'main',
            'title' => 'Detail zvířete - ' . ($animal['name'] ?: $animal['species']),
            'animal' => $animal,
            'examinations' => $examinations,
            'biochemistryTests' => $biochemistryTests,
            'hematologyTests' => $hematologyTests,
            'urineTests' => $urineTests,
            'canEdit' => $userModel->hasPermission(Auth::userId(), $animal['workplace_id'], 'edit')
        ]);
    }
}
