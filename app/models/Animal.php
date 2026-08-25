<?php
require_once __DIR__ . '/../core/Model.php';

class Animal extends Model {
    
    protected $table = 'animals';

    public function findByCode($code) {
        $sql = "SELECT * FROM animals WHERE identifier = ?";
        $result = $this->query($sql, [$code]);
        return $result[0] ?? null;
    }

    /* ---- Ošetřovatelé (vztah M:N přes animal_keepers) --------------------
       Čtecí metody jsou odolné vůči stavu před migrací 021 (tabulka
       animal_keepers ještě nemusí existovat) – vrátí prázdno místo chyby. */

    /** ID ošetřovatelů (users.id) přiřazených ke zvířeti. */
    public function getKeeperUserIds($animalId) {
        try {
            $rows = $this->query(
                "SELECT user_id FROM animal_keepers WHERE animal_id = ?",
                [(int)$animalId]
            );
            return array_map(fn($r) => (int)$r['user_id'], $rows);
        } catch (Exception $e) {
            return [];
        }
    }

    /** ID zvířat, kterých je daný uživatel ošetřovatelem. */
    public function getAnimalIdsByKeeper($userId) {
        try {
            $rows = $this->query(
                "SELECT animal_id FROM animal_keepers WHERE user_id = ?",
                [(int)$userId]
            );
            return array_map(fn($r) => (int)$r['animal_id'], $rows);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Ošetřovatelé pro sadu zvířat najednou (bez N+1).
     * Vrací [animal_id => [ ['id','username','full_name'], ... ]].
     */
    public function getKeepersMap(array $animalIds) {
        $animalIds = array_values(array_filter(array_map('intval', $animalIds), fn($v) => $v > 0));
        if (empty($animalIds)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($animalIds), '?'));
        try {
            $rows = $this->query(
                "SELECT ak.animal_id, u.id, u.username, u.full_name
                 FROM animal_keepers ak
                 JOIN users u ON u.id = ak.user_id
                 WHERE ak.animal_id IN ($placeholders)
                 ORDER BY u.full_name, u.username",
                $animalIds
            );
        } catch (Exception $e) {
            return [];
        }

        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['animal_id']][] = [
                'id' => (int)$r['id'],
                'username' => $r['username'],
                'full_name' => $r['full_name'],
            ];
        }
        return $map;
    }

    /** Nastaví ošetřovatele zvířete (nahradí celou množinu). */
    public function setKeepers($animalId, array $userIds) {
        $animalId = (int)$animalId;
        $userIds = array_values(array_unique(array_filter(
            array_map('intval', $userIds),
            fn($v) => $v > 0
        )));

        $this->db->beginTransaction();
        try {
            $del = $this->db->prepare("DELETE FROM animal_keepers WHERE animal_id = ?");
            $del->execute([$animalId]);

            if (!empty($userIds)) {
                $ins = $this->db->prepare(
                    "INSERT IGNORE INTO animal_keepers (animal_id, user_id) VALUES (?, ?)"
                );
                foreach ($userIds as $uid) {
                    $ins->execute([$animalId, $uid]);
                }
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Hromadné přiřazení ošetřovatelů k více zvířatům najednou.
     *
     * $mode = 'add'     → přidá vybrané ošetřovatele (stávající zůstávají)
     * $mode = 'replace' → nastaví přesně vybrané ošetřovatele (ostatní smaže)
     *
     * Vše v jedné transakci (buď se provede celé, nebo nic).
     */
    public function bulkAssignKeepers(array $animalIds, array $userIds, $mode = 'add') {
        $animalIds = array_values(array_unique(array_filter(array_map('intval', $animalIds), fn($v) => $v > 0)));
        $userIds   = array_values(array_unique(array_filter(array_map('intval', $userIds), fn($v) => $v > 0)));
        if (empty($animalIds)) {
            return;
        }

        $this->db->beginTransaction();
        try {
            if ($mode === 'replace') {
                $placeholders = implode(',', array_fill(0, count($animalIds), '?'));
                $this->db->prepare("DELETE FROM animal_keepers WHERE animal_id IN ($placeholders)")
                         ->execute($animalIds);
            }

            if (!empty($userIds)) {
                $ins = $this->db->prepare(
                    "INSERT IGNORE INTO animal_keepers (animal_id, user_id) VALUES (?, ?)"
                );
                foreach ($animalIds as $aid) {
                    foreach ($userIds as $uid) {
                        $ins->execute([$aid, $uid]);
                    }
                }
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getByWorkplace($workplaceId, $filters = []) {
        $sql = "
            SELECT
                a.*,
                e.name as enclosure_name,
                latest_exam.examination_date as last_examination,
                latest_exam.finding_status as last_finding
            FROM animals a
            LEFT JOIN enclosures e ON a.current_enclosure_id = e.id
            LEFT JOIN (
                SELECT
                    animal_id,
                    examination_date,
                    finding_status,
                    ROW_NUMBER() OVER (PARTITION BY animal_id ORDER BY examination_date DESC) as rn
                FROM examinations
            ) latest_exam ON a.id = latest_exam.animal_id AND latest_exam.rn = 1
            WHERE a.workplace_id = ?
        ";
        
        $params = [$workplaceId];
        
        // Filtry
        if (!empty($filters['status'])) {
            $sql .= " AND a.current_status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['enclosure_id'])) {
            $sql .= " AND a.current_enclosure_id = ?";
            $params[] = $filters['enclosure_id'];
        }
        
        if (!empty($filters['species'])) {
            $sql .= " AND a.species LIKE ?";
            $params[] = '%' . $filters['species'] . '%';
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (a.name LIKE ? OR a.identifier LIKE ? OR a.species LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY a.name ASC, a.identifier ASC";
        
        return $this->query($sql, $params);
    }
    
    public function getDetail($id) {
        $sql = "
            SELECT
                a.*,
                e.name as enclosure_name,
                w.name as workplace_name,
                ac.name as animal_category
            FROM animals a
            LEFT JOIN enclosures e ON a.current_enclosure_id = e.id
            LEFT JOIN workplaces w ON a.workplace_id = w.id
            LEFT JOIN animal_categories ac ON a.animal_category_id = ac.id
            WHERE a.id = ?
        ";
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }
    
    public function getExaminations($animalId, $limit = null) {
        $sql = "
            SELECT
                e.*,
                en.name as enclosure_name,
                u.full_name as created_by_name,
                d.id as deworming_id,
                d.deworming_date,
                d.medication,
                d.dosage,
                d.administration_route,
                d.reason as deworming_reason
            FROM examinations e
            LEFT JOIN enclosures en ON e.enclosure_id = en.id
            LEFT JOIN users u ON e.created_by = u.id
            LEFT JOIN dewormings d ON d.related_examination_id = e.id
            WHERE e.animal_id = ?
            ORDER BY e.examination_date DESC
        ";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }

        return $this->query($sql, [$animalId]);
    }
    
    public function getDewormings($animalId, $limit = null) {
        $sql = "
            SELECT 
                d.*,
                u.full_name as created_by_name
            FROM dewormings d
            LEFT JOIN users u ON d.created_by = u.id
            WHERE d.animal_id = ?
            ORDER BY d.deworming_date DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        return $this->query($sql, [$animalId]);
    }
    
    public function getScheduledChecks($animalId, $status = null) {
        $sql = "
            SELECT 
                sc.*,
                u.full_name as created_by_name
            FROM scheduled_checks sc
            LEFT JOIN users u ON sc.created_by = u.id
            WHERE sc.animal_id = ?
        ";
        
        $params = [$animalId];
        
        if ($status) {
            $sql .= " AND sc.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY sc.scheduled_date ASC";
        
        return $this->query($sql, $params);
    }
    
    public function getStatusHistory($animalId) {
        $sql = "
            SELECT
                ash.*,
                u.full_name as created_by_name,
                fw.name as from_workplace_name,
                tw.name as to_workplace_name,
                fe.name as from_enclosure_name,
                te.name as to_enclosure_name
            FROM animal_status_history ash
            LEFT JOIN users u ON ash.created_by = u.id
            LEFT JOIN workplaces fw ON ash.from_workplace_id = fw.id
            LEFT JOIN workplaces tw ON ash.to_workplace_id = tw.id
            LEFT JOIN enclosures fe ON ash.from_enclosure_id = fe.id
            LEFT JOIN enclosures te ON ash.to_enclosure_id = te.id
            WHERE ash.animal_id = ?
            ORDER BY ash.status_date DESC, ash.created_at DESC
        ";

        return $this->query($sql, [$animalId]);
    }

    public function getExaminationHistory($workplaceId, $filters = []) {
        // Get animals with all their examinations
        $sql = "
            SELECT
                a.id as animal_id,
                a.name,
                a.identifier,
                a.species,
                a.breed,
                a.current_status,
                a.next_check_date,
                e.name as enclosure_name
            FROM animals a
            LEFT JOIN enclosures e ON a.current_enclosure_id = e.id
            WHERE a.workplace_id = ?
        ";

        $params = [$workplaceId];

        // Apply filters
        if (!empty($filters['status'])) {
            $sql .= " AND a.current_status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['enclosure_id'])) {
            $sql .= " AND a.current_enclosure_id = ?";
            $params[] = $filters['enclosure_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (a.name LIKE ? OR a.identifier LIKE ? OR a.species LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY a.name ASC, a.identifier ASC";

        $animals = $this->query($sql, $params);

        // Get all examinations for all animals in one optimized query
        if (!empty($animals)) {
            $animalIds = array_column($animals, 'animal_id');
            $placeholders = str_repeat('?,', count($animalIds) - 1) . '?';

            $examSql = "
                SELECT
                    e.animal_id,
                    e.id,
                    e.examination_date,
                    e.finding_status,
                    e.notes,
                    e.sample_type,
                    e.institution,
                    e.parasite_found,
                    e.intensity,
                    en.name as location,
                    d.id as deworming_id,
                    d.deworming_date,
                    d.medication,
                    d.dosage,
                    d.administration_route,
                    d.reason as deworming_reason,
                    GROUP_CONCAT(
                        CONCAT(p.scientific_name,
                        CASE WHEN ep.intensity IS NOT NULL THEN CONCAT(' (', ep.intensity, ')') ELSE '' END,
                        CASE WHEN ep.notes IS NOT NULL THEN CONCAT(' - ', ep.notes) ELSE '' END)
                        SEPARATOR '; '
                    ) as parasites_found
                FROM examinations e
                LEFT JOIN enclosures en ON e.enclosure_id = en.id
                LEFT JOIN examination_parasites ep ON e.id = ep.examination_id
                LEFT JOIN parasites p ON ep.parasite_id = p.id
                LEFT JOIN dewormings d ON d.related_examination_id = e.id
                WHERE e.animal_id IN ($placeholders)
                GROUP BY e.id, e.animal_id
                ORDER BY e.animal_id, e.examination_date DESC
            ";

            $allExaminations = $this->query($examSql, $animalIds);

            // Group examinations by animal_id
            $examinationsLookup = [];
            foreach ($allExaminations as $exam) {
                $examinationsLookup[$exam['animal_id']][] = $exam;
            }

            // Assign examinations to each animal
            foreach ($animals as &$animal) {
                $animal['examinations'] = $examinationsLookup[$animal['animal_id']] ?? [];
            }
        }

        return $animals;
    }
}