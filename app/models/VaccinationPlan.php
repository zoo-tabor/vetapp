<?php

require_once __DIR__ . '/../core/Database.php';

class VaccinationPlan {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all vaccination plans for a workplace with animal and vaccine details
     */
    public function getByWorkplace($workplaceId, $filters = []) {
        $sql = "
            SELECT
                vp.*,
                a.name as animal_name,
                a.identifier as animal_identifier,
                a.species as animal_species,
                a.animal_category,
                m.name as vaccine_name,
                m.unit as vaccine_unit,
                u_admin.full_name as administered_by_name,
                u_created.full_name as created_by_name
            FROM vaccination_plans vp
            JOIN animals a ON vp.animal_id = a.id
            LEFT JOIN warehouse_items m ON vp.vaccine_id = m.item_code
            LEFT JOIN users u_admin ON vp.administered_by = u_admin.id
            LEFT JOIN users u_created ON vp.created_by = u_created.id
            WHERE a.workplace_id = ?
        ";

        $params = [$workplaceId];

        // Filter by status
        if (!empty($filters['status'])) {
            $sql .= " AND vp.status = ?";
            $params[] = $filters['status'];
        }

        // Filter by animal category
        if (!empty($filters['animal_category'])) {
            $sql .= " AND a.animal_category = ?";
            $params[] = $filters['animal_category'];
        }

        // Filter by year
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(vp.planned_date) = ?";
            $params[] = $filters['year'];
        }

        $sql .= " ORDER BY vp.planned_date ASC, a.animal_category, a.name";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get vaccination plans grouped by animal category
     */
    public function getByWorkplaceGroupedByCategory($workplaceId, $year = null) {
        $year = $year ?? date('Y');

        $sql = "
            SELECT
                vp.*,
                a.id as animal_id,
                a.name as animal_name,
                a.identifier as animal_identifier,
                a.species as animal_species,
                a.animal_category,
                m.id as vaccine_id,
                m.name as vaccine_name,
                vt.color_hex as vaccine_color,
                vt.abbreviation as vaccine_abbr
            FROM animals a
            LEFT JOIN vaccination_plans vp ON a.id = vp.animal_id
                AND YEAR(vp.planned_date) = ?
                AND vp.status != 'cancelled'
            LEFT JOIN warehouse_items m ON vp.vaccine_id = m.item_code
            LEFT JOIN vaccine_type_colors vt ON vt.vaccine_type = m.name
            WHERE a.workplace_id = ? AND a.current_status = 'active'
            ORDER BY a.animal_category, a.name, vp.planned_date
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$year, $workplaceId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by category
        $grouped = [];
        foreach ($results as $row) {
            $category = $row['animal_category'] ?: 'Nezařazeno';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }

            $animalId = $row['animal_id'];
            if (!isset($grouped[$category][$animalId])) {
                $grouped[$category][$animalId] = [
                    'animal_id' => $row['animal_id'],
                    'animal_name' => $row['animal_name'],
                    'animal_identifier' => $row['animal_identifier'],
                    'animal_species' => $row['animal_species'],
                    'animal_category' => $row['animal_category'],
                    'vaccinations' => []
                ];
            }

            if ($row['id']) {
                $grouped[$category][$animalId]['vaccinations'][] = [
                    'id' => $row['id'],
                    'vaccine_id' => $row['vaccine_id'],
                    'vaccine_name' => $row['vaccine_name'],
                    'vaccine_color' => $row['vaccine_color'],
                    'vaccine_abbr' => $row['vaccine_abbr'],
                    'planned_date' => $row['planned_date'],
                    'month_planned' => $row['month_planned'],
                    'status' => $row['status']
                ];
            }
        }

        return $grouped;
    }

    /**
     * Get overdue vaccinations for a workplace
     */
    public function getOverdue($workplaceId) {
        return $this->getByWorkplace($workplaceId, ['status' => 'overdue']);
    }

    /**
     * Get upcoming vaccinations (next 30 days)
     */
    public function getUpcoming($workplaceId, $days = 30) {
        $sql = "
            SELECT
                vp.*,
                a.name as animal_name,
                a.identifier as animal_identifier,
                a.species as animal_species,
                a.assigned_user,
                m.name as vaccine_name
            FROM vaccination_plans vp
            JOIN animals a ON vp.animal_id = a.id
            LEFT JOIN warehouse_items m ON vp.vaccine_id = m.item_code
            WHERE a.workplace_id = ?
                AND vp.status = 'planned'
                AND vp.planned_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY vp.planned_date ASC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$workplaceId, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get statistics for a workplace
     */
    public function getStats($workplaceId) {
        $sql = "
            SELECT
                SUM(CASE WHEN vp.status = 'planned' THEN 1 ELSE 0 END) as planned_count,
                SUM(CASE WHEN vp.status = 'overdue' THEN 1 ELSE 0 END) as overdue_count,
                SUM(CASE WHEN vp.status = 'completed' AND YEAR(vp.administered_date) = YEAR(CURDATE()) THEN 1 ELSE 0 END) as completed_count
            FROM vaccination_plans vp
            JOIN animals a ON vp.animal_id = a.id
            WHERE a.workplace_id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$workplaceId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new vaccination plan
     */
    public function create($data) {
        $sql = "
            INSERT INTO vaccination_plans (
                animal_id, vaccine_id, vaccine_name, planned_date, month_planned,
                vaccination_interval_days, requires_booster, booster_days,
                animal_category, status, notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'planned', ?, ?)
        ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['animal_id'],
            $data['vaccine_id'] ?? null,
            $data['vaccine_name'],
            $data['planned_date'],
            $data['month_planned'] ?? null,
            $data['vaccination_interval_days'] ?? null,
            $data['requires_booster'] ?? false,
            $data['booster_days'] ?? null,
            $data['animal_category'] ?? null,
            $data['notes'] ?? null,
            $data['created_by']
        ]);

        if ($result) {
            $planId = $this->db->lastInsertId();

            // Create booster plan if required
            if (!empty($data['requires_booster']) && !empty($data['booster_days'])) {
                $boosterDate = date('Y-m-d', strtotime($data['planned_date'] . ' + ' . $data['booster_days'] . ' days'));
                $this->createBoosterPlan($planId, $data, $boosterDate);
            }

            return $planId;
        }

        return false;
    }

    /**
     * Create booster vaccination plan
     */
    private function createBoosterPlan($parentPlanId, $parentData, $boosterDate) {
        $sql = "
            INSERT INTO vaccination_plans (
                animal_id, vaccine_id, vaccine_name, planned_date,
                vaccination_interval_days, booster_plan_id,
                animal_category, status, notes, created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'planned', ?, ?)
        ";

        $notes = 'Přeočkování (booster) k vakcinaci #' . $parentPlanId;
        if (!empty($parentData['notes'])) {
            $notes .= ' - ' . $parentData['notes'];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $parentData['animal_id'],
            $parentData['vaccine_id'] ?? null,
            $parentData['vaccine_name'] . ' (Booster)',
            $boosterDate,
            $parentData['vaccination_interval_days'] ?? null,
            $parentPlanId,
            $parentData['animal_category'] ?? null,
            $notes,
            $parentData['created_by']
        ]);
    }

    /**
     * Mark vaccination as completed
     */
    public function markAsCompleted($id, $data) {
        $sql = "
            UPDATE vaccination_plans
            SET status = 'completed',
                administered_date = ?,
                administered_by = ?,
                notes = CONCAT(COALESCE(notes, ''), ?)
            WHERE id = ?
        ";

        $additionalNotes = !empty($data['completion_notes']) ? '\n[Dokončeno: ' . $data['completion_notes'] . ']' : '';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['administered_date'],
            $data['administered_by'],
            $additionalNotes,
            $id
        ]);
    }

    /**
     * Batch mark multiple vaccinations as completed
     */
    public function batchMarkAsCompleted($ids, $data) {
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';

        $sql = "
            UPDATE vaccination_plans
            SET status = 'completed',
                administered_date = ?,
                administered_by = ?,
                notes = CONCAT(COALESCE(notes, ''), ?)
            WHERE id IN ($placeholders)
        ";

        $additionalNotes = !empty($data['completion_notes']) ? '\n[Hromadné dokončení: ' . $data['completion_notes'] . ']' : '';

        $params = [
            $data['administered_date'],
            $data['administered_by'],
            $additionalNotes,
            ...$ids
        ];

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Update vaccination plan status (planned -> overdue)
     */
    public function updateOverdueStatus() {
        $sql = "
            UPDATE vaccination_plans
            SET status = 'overdue'
            WHERE status = 'planned'
                AND planned_date < CURDATE()
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute();
    }

    /**
     * Get vaccinations needing notifications
     */
    public function getNotificationDue($days) {
        $sql = "
            SELECT
                vp.*,
                a.name as animal_name,
                a.identifier as animal_identifier,
                a.assigned_user,
                a.workplace_id,
                m.name as vaccine_name,
                w.name as workplace_name
            FROM vaccination_plans vp
            JOIN animals a ON vp.animal_id = a.id
            JOIN workplaces w ON a.workplace_id = w.id
            LEFT JOIN warehouse_items m ON vp.vaccine_id = m.item_code
            WHERE vp.status = 'planned'
                AND vp.planned_date = DATE_ADD(CURDATE(), INTERVAL ? DAY)
                AND (
                    (? = 7 AND vp.notification_sent_7days = 0) OR
                    (? = 1 AND vp.notification_sent_1day = 0)
                )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$days, $days, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mark notification as sent
     */
    public function markNotificationSent($id, $days) {
        $column = $days == 7 ? 'notification_sent_7days' : 'notification_sent_1day';

        $sql = "UPDATE vaccination_plans SET $column = 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Get vaccination history for an animal
     */
    public function getHistoryByAnimal($animalId, $years = 6) {
        $sql = "
            SELECT
                vp.*,
                m.name as vaccine_name,
                u.full_name as administered_by_name
            FROM vaccination_plans vp
            LEFT JOIN warehouse_items m ON vp.vaccine_id = m.item_code
            LEFT JOIN users u ON vp.administered_by = u.id
            WHERE vp.animal_id = ?
                AND vp.status = 'completed'
                AND vp.administered_date >= DATE_SUB(CURDATE(), INTERVAL ? YEAR)
            ORDER BY vp.administered_date DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$animalId, $years]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete vaccination plan
     */
    public function delete($id) {
        $sql = "DELETE FROM vaccination_plans WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Find by ID
     */
    public function findById($id) {
        $sql = "
            SELECT
                vp.*,
                a.name as animal_name,
                a.identifier as animal_identifier,
                a.species as animal_species,
                a.animal_category,
                m.name as vaccine_name
            FROM vaccination_plans vp
            JOIN animals a ON vp.animal_id = a.id
            LEFT JOIN warehouse_items m ON vp.vaccine_id = m.item_code
            WHERE vp.id = ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
