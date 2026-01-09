<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Database.php';

class ApiController {

    public function getReferenceRanges() {
        Auth::requireLogin();

        header('Content-Type: application/json');

        $testType = $_GET['test_type'] ?? null;
        $parameter = $_GET['parameter'] ?? null;
        $species = $_GET['species'] ?? null;
        $source = $_GET['source'] ?? null;

        if (!$testType || !$parameter || !$species || !$source) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("
                SELECT min_value, max_value, unit
                FROM reference_ranges
                WHERE test_type = ?
                  AND parameter_name = ?
                  AND species = ?
                  AND source = ?
                LIMIT 1
            ");

            $stmt->execute([$testType, $parameter, $species, $source]);
            $range = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($range) {
                echo json_encode($range);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Reference range not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function getUrineReferenceRanges() {
        Auth::requireLogin();

        header('Content-Type: application/json');

        $parameter = $_GET['parameter'] ?? null;
        $species = $_GET['species'] ?? null;
        $source = $_GET['source'] ?? null;

        if (!$parameter || !$species || !$source) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("
                SELECT reference_text, min_value, max_value, unit
                FROM urine_reference_ranges
                WHERE parameter_name = ?
                  AND species = ?
                  AND reference_source = ?
                LIMIT 1
            ");

            $stmt->execute([$parameter, $species, $source]);
            $range = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($range) {
                echo json_encode($range);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Reference range not found']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function searchParasites() {
        Auth::requireLogin();

        header('Content-Type: application/json');

        $workplaceId = $_GET['workplace_id'] ?? null;
        $parasite = $_GET['parasite'] ?? null;

        if (!$workplaceId || !$parasite) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("
                SELECT
                    e.examination_date,
                    e.sample_type,
                    e.intensity,
                    e.notes,
                    a.name as animal_name,
                    a.species,
                    e.parasite_found as parasite_name
                FROM examinations e
                JOIN animals a ON e.animal_id = a.id
                WHERE e.workplace_id = ?
                  AND e.parasite_found = ?
                ORDER BY e.examination_date DESC
            ");

            $stmt->execute([$workplaceId, $parasite]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['results' => $results]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }

    public function searchDrugs() {
        Auth::requireLogin();

        header('Content-Type: application/json');

        $workplaceId = $_GET['workplace_id'] ?? null;
        $drug = $_GET['drug'] ?? null;

        if (!$workplaceId || !$drug) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required parameters']);
            return;
        }

        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("
                SELECT
                    d.deworming_date,
                    d.dosage,
                    d.administration_route,
                    d.notes,
                    a.name as animal_name,
                    a.species
                FROM dewormings d
                JOIN animals a ON d.animal_id = a.id
                WHERE d.workplace_id = ?
                  AND d.medication = ?
                ORDER BY d.deworming_date DESC
            ");

            $stmt->execute([$workplaceId, $drug]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['results' => $results]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
    }
}
