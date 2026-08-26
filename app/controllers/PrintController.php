<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/User.php';

class PrintController {

    public function history() {
        Auth::requireLogin();

        $workplaceId = $_GET['workplace_id'] ?? null;
        $printCount = min(max((int)($_GET['print_count'] ?? 5), 1), 10); // Limit between 1-10
        $type = $_GET['type'] ?? 'animals'; // 'animals' or 'enclosures'
        $ids = explode(',', $_GET['ids'] ?? '');

        if (empty($workplaceId) || empty($ids)) {
            die('Chybějící parametry: workplace_id=' . var_export($workplaceId, true) . ', ids=' . var_export($ids, true));
        }

        // Check view permissions
        $userModel = new User();
        if (!$userModel->hasPermission(Auth::userId(), $workplaceId, 'animals', 'view')) {
            die('Nemáte oprávnění k tomuto pracovišti');
        }

        $workplaceModel = new Workplace();
        $workplace = $workplaceModel->findById($workplaceId);

        $animalModel = new Animal();
        $printData = [];

        if ($type === 'animals') {
            // Get data for individual animals
            foreach ($ids as $animalId) {
                $animal = $animalModel->getDetail($animalId);
                if ($animal && $animal['workplace_id'] == $workplaceId) {
                    // Get all examinations (přes junction – i skupinové), pak seskupit v PHP
                    $allExaminations = $animalModel->query("
                        SELECT e.*, pg.name AS group_name
                        FROM examination_animals ea
                        JOIN examinations e ON e.id = ea.examination_id
                        LEFT JOIN parasitology_groups pg ON e.group_id = pg.id
                        WHERE ea.animal_id = ?
                        ORDER BY e.examination_date DESC, e.institution, e.id DESC
                    ", [$animalId]);

                    // Group examinations by date + institution
                    $groupedExams = [];
                    foreach ($allExaminations as $exam) {
                        $key = $exam['examination_date'] . '|' . ($exam['institution'] ?? '');
                        if (!isset($groupedExams[$key])) {
                            $groupedExams[$key] = [];
                        }
                        $groupedExams[$key][] = $exam;
                    }

                    // Take only the first $printCount groups
                    $limitedGroups = array_slice($groupedExams, 0, $printCount, true);

                    // Flatten back to a single array of examinations
                    $examinations = [];
                    foreach ($limitedGroups as $group) {
                        $examinations = array_merge($examinations, $group);
                    }

                    $dewormings = $animalModel->query("
                        SELECT d.*, pg.name AS group_name
                        FROM deworming_animals da
                        JOIN dewormings d ON d.id = da.deworming_id
                        LEFT JOIN parasitology_groups pg ON d.group_id = pg.id
                        WHERE da.animal_id = ?
                        ORDER BY d.deworming_date DESC, d.id DESC
                        LIMIT ?
                    ", [$animalId, $printCount]);

                    $printData[] = [
                        'animal' => $animal,
                        'examinations' => $examinations,
                        'dewormings' => $dewormings,
                        'conclusion' => '' // Will be filled by user in print view
                    ];
                }
            }
        } else {
            // Get data for enclosures
            foreach ($ids as $enclosureId) {
                // Get all animals in this enclosure
                $animals = $animalModel->query("
                    SELECT * FROM animals
                    WHERE workplace_id = ? AND current_enclosure_id = ? AND is_active = 1
                    ORDER BY name, identifier
                ", [$workplaceId, $enclosureId]);

                $enclosure = $workplaceModel->query("
                    SELECT * FROM enclosures WHERE id = ?
                ", [$enclosureId])[0] ?? null;

                foreach ($animals as $animal) {
                    // Get all examinations (přes junction – i skupinové), pak seskupit v PHP
                    $allExaminations = $animalModel->query("
                        SELECT e.*, pg.name AS group_name
                        FROM examination_animals ea
                        JOIN examinations e ON e.id = ea.examination_id
                        LEFT JOIN parasitology_groups pg ON e.group_id = pg.id
                        WHERE ea.animal_id = ?
                        ORDER BY e.examination_date DESC, e.institution, e.id DESC
                    ", [$animal['id']]);

                    // Group examinations by date + institution
                    $groupedExams = [];
                    foreach ($allExaminations as $exam) {
                        $key = $exam['examination_date'] . '|' . ($exam['institution'] ?? '');
                        if (!isset($groupedExams[$key])) {
                            $groupedExams[$key] = [];
                        }
                        $groupedExams[$key][] = $exam;
                    }

                    // Take only the first $printCount groups
                    $limitedGroups = array_slice($groupedExams, 0, $printCount, true);

                    // Flatten back to a single array of examinations
                    $examinations = [];
                    foreach ($limitedGroups as $group) {
                        $examinations = array_merge($examinations, $group);
                    }

                    $dewormings = $animalModel->query("
                        SELECT d.*, pg.name AS group_name
                        FROM deworming_animals da
                        JOIN dewormings d ON d.id = da.deworming_id
                        LEFT JOIN parasitology_groups pg ON d.group_id = pg.id
                        WHERE da.animal_id = ?
                        ORDER BY d.deworming_date DESC, d.id DESC
                        LIMIT ?
                    ", [$animal['id'], $printCount]);

                    $printData[] = [
                        'animal' => $animal,
                        'enclosure' => $enclosure,
                        'examinations' => $examinations,
                        'dewormings' => $dewormings,
                        'conclusion' => ''
                    ];
                }
            }
        }

        View::render('print/history', [
            'layout' => null, // No layout for print view
            'workplace' => $workplace,
            'printData' => $printData,
            'printCount' => $printCount,
            'type' => $type
        ]);
    }
}
