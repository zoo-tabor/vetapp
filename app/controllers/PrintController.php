<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/View.php';
require_once __DIR__ . '/../models/Animal.php';
require_once __DIR__ . '/../models/Workplace.php';
require_once __DIR__ . '/../models/User.php';

class PrintController {

    public function history() {
        error_log("PrintController::history() called");
        error_log("GET params: " . print_r($_GET, true));

        Auth::requireLogin();

        $workplaceId = $_GET['workplace_id'] ?? null;
        $printCount = min(max((int)($_GET['print_count'] ?? 5), 1), 10); // Limit between 1-10
        $type = $_GET['type'] ?? 'animals'; // 'animals' or 'enclosures'
        $ids = explode(',', $_GET['ids'] ?? '');

        error_log("Parsed: workplace=$workplaceId, count=$printCount, type=$type, ids=" . implode(',', $ids));

        if (empty($workplaceId) || empty($ids)) {
            error_log("Missing parameters - returning error");
            die('Chybějící parametry: workplace_id=' . var_export($workplaceId, true) . ', ids=' . var_export($ids, true));
        }

        // Check view permissions
        $userModel = new User();
        if (!$userModel->hasPermission(Auth::userId(), $workplaceId, 'view')) {
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
                    // Get all examinations, then group by date+institution in PHP
                    $allExaminations = $animalModel->query("
                        SELECT * FROM examinations
                        WHERE animal_id = ?
                        ORDER BY examination_date DESC, institution, id DESC
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
                        SELECT * FROM dewormings
                        WHERE animal_id = ?
                        ORDER BY deworming_date DESC, id DESC
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
                    // Get all examinations, then group by date+institution in PHP
                    $allExaminations = $animalModel->query("
                        SELECT * FROM examinations
                        WHERE animal_id = ?
                        ORDER BY examination_date DESC, institution, id DESC
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
                        SELECT * FROM dewormings
                        WHERE animal_id = ?
                        ORDER BY deworming_date DESC, id DESC
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
