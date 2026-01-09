<?php
require_once __DIR__ . '/../core/Model.php';

class Examination extends Model {
    
    protected $table = 'examinations';
    
    public function getDetail($id) {
        $sql = "
            SELECT 
                e.*,
                a.name as animal_name,
                a.identifier as animal_identifier,
                en.name as enclosure_name,
                u.full_name as created_by_name
            FROM examinations e
            INNER JOIN animals a ON e.animal_id = a.id
            LEFT JOIN enclosures en ON e.enclosure_id = en.id
            LEFT JOIN users u ON e.created_by = u.id
            WHERE e.id = ?
        ";
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function getParasites($examinationId) {
        $sql = "
            SELECT 
                ep.*,
                p.scientific_name,
                p.common_name,
                p.category
            FROM examination_parasites ep
            INNER JOIN parasites p ON ep.parasite_id = p.id
            WHERE ep.examination_id = ?
        ";
        return $this->query($sql, [$examinationId]);
    }
    
    public function addParasites($examinationId, $parasites) {
        foreach ($parasites as $parasite) {
            $sql = "
                INSERT INTO examination_parasites (examination_id, parasite_id, intensity, notes)
                VALUES (?, ?, ?, ?)
            ";
            $this->execute($sql, [
                $examinationId,
                $parasite['parasite_id'],
                $parasite['intensity'] ?? null,
                $parasite['notes'] ?? null
            ]);
        }
        return true;
    }
    
    public function removeParasites($examinationId) {
        $sql = "DELETE FROM examination_parasites WHERE examination_id = ?";
        return $this->execute($sql, [$examinationId]);
    }
    
    public function getRecentByWorkplace($workplaceId, $days = 30) {
        $sql = "
            SELECT
                e.*,
                a.name as animal_name,
                a.identifier as animal_identifier,
                en.name as enclosure_name
            FROM examinations e
            INNER JOIN animals a ON e.animal_id = a.id
            LEFT JOIN enclosures en ON e.enclosure_id = en.id
            WHERE e.workplace_id = ?
            AND e.examination_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            ORDER BY e.examination_date DESC
        ";
        return $this->query($sql, [$workplaceId, $days]);
    }

    public function createExamination($data) {
        // Get animal's current enclosure and workplace
        $enclosureId = null;
        $workplaceId = null;

        if (isset($data['animal_id'])) {
            $animalSql = "SELECT current_enclosure_id, workplace_id FROM animals WHERE id = ?";
            $animalResult = $this->query($animalSql, [$data['animal_id']]);
            if (!empty($animalResult)) {
                $enclosureId = $animalResult[0]['current_enclosure_id'];
                $workplaceId = $animalResult[0]['workplace_id'];
            }
        }

        $sql = "
            INSERT INTO examinations (
                animal_id,
                workplace_id,
                enclosure_id,
                examination_date,
                sample_type,
                institution,
                parasite_found,
                finding_status,
                intensity,
                notes,
                created_by
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";

        return $this->execute($sql, [
            $data['animal_id'],
            $workplaceId,
            $enclosureId,
            $data['examination_date'],
            $data['sample_type'],
            $data['institution'] ?? null,
            $data['parasite_found'] ?? null,
            $data['finding_status'],
            $data['intensity'] ?? null,
            $data['notes'] ?? null,
            $data['created_by']
        ]);
    }

    public function createExaminationForEnclosure($data) {
        require_once __DIR__ . '/Animal.php';
        $animalModel = new Animal();

        // Get all animals in the enclosure
        $sql = "
            SELECT id FROM animals
            WHERE current_enclosure_id = ?
            AND current_status = 'active'
        ";
        $animals = $this->query($sql, [$data['enclosure_id']]);

        // Create examination for each animal
        foreach ($animals as $animal) {
            $examData = [
                'animal_id' => $animal['id'],
                'examination_date' => $data['examination_date'],
                'sample_type' => $data['sample_type'],
                'institution' => $data['institution'] ?? null,
                'parasite_found' => $data['parasite_found'] ?? null,
                'finding_status' => $data['finding_status'],
                'intensity' => $data['intensity'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => $data['created_by']
            ];
            $this->createExamination($examData);
        }

        return true;
    }
}