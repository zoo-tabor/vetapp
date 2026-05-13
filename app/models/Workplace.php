<?php
require_once __DIR__ . '/../core/Model.php';

class Workplace extends Model {
    
    protected $table = 'workplaces';
    
    public function getAllActive() {
        return $this->findAll(['is_active' => 1], 'name ASC');
    }

    public function getAll() {
        $sql = "SELECT * FROM workplaces WHERE is_active = 1 ORDER BY name ASC";
        return $this->query($sql);
    }

    public function getById($id) {
        $sql = "SELECT * FROM workplaces WHERE id = ? AND is_active = 1";
        $result = $this->query($sql, [$id]);
        return $result[0] ?? null;
    }

    // Check if user has view access to a specific section in a workplace
    public function hasAccess($userId, $workplaceId, $section = null) {
        if ($section) {
            $sql = "
                SELECT can_view FROM user_permissions
                WHERE user_id = ? AND workplace_id = ? AND section = ? AND can_view = 1
            ";
            $result = $this->query($sql, [$userId, $workplaceId, $section]);
        } else {
            $sql = "
                SELECT can_view FROM user_permissions
                WHERE user_id = ? AND workplace_id = ? AND can_view = 1
                LIMIT 1
            ";
            $result = $this->query($sql, [$userId, $workplaceId]);
        }
        return !empty($result);
    }

    // Workplaces where user has view access (any section, or specific section)
    public function getUserWorkplaces($userId, $section = null) {
        if ($section) {
            $sql = "
                SELECT w.*, up.can_view, up.can_edit
                FROM workplaces w
                INNER JOIN user_permissions up ON w.id = up.workplace_id
                WHERE up.user_id = ? AND up.section = ? AND up.can_view = 1 AND w.is_active = 1
                ORDER BY w.name ASC
            ";
            return $this->query($sql, [$userId, $section]);
        }
        $sql = "
            SELECT DISTINCT w.*, MAX(up.can_view) as can_view, MAX(up.can_edit) as can_edit
            FROM workplaces w
            INNER JOIN user_permissions up ON w.id = up.workplace_id
            WHERE up.user_id = ? AND up.can_view = 1 AND w.is_active = 1
            GROUP BY w.id
            ORDER BY w.name ASC
        ";
        return $this->query($sql, [$userId]);
    }
    
    public function getEnclosures($workplaceId) {
        $sql = "
            SELECT * FROM enclosures
            WHERE workplace_id = ? AND is_active = 1
            ORDER BY name ASC
        ";
        return $this->query($sql, [$workplaceId]);
    }

    public function createEnclosure($data) {
        $sql = "
            INSERT INTO enclosures (workplace_id, name, notes, is_active)
            VALUES (?, ?, ?, 1)
        ";
        return $this->execute($sql, [
            $data['workplace_id'],
            $data['name'],
            $data['notes'] ?? null
        ]);
    }
    
    public function getStats($workplaceId) {
        // Opravený SQL dotaz s lepším error handlingem
        try {
            $sql = "
                SELECT 
                    COALESCE(COUNT(DISTINCT a.id), 0) as total_animals,
                    COALESCE(COUNT(DISTINCT CASE WHEN a.current_status = 'active' THEN a.id END), 0) as active_animals,
                    COALESCE(COUNT(DISTINCT e.id), 0) as total_examinations,
                    COALESCE(COUNT(DISTINCT CASE WHEN e.examination_date >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN e.id END), 0) as recent_examinations
                FROM animals a
                LEFT JOIN examinations e ON a.id = e.animal_id
                WHERE a.workplace_id = ?
            ";
            $result = $this->query($sql, [$workplaceId]);
            
            if (!empty($result) && isset($result[0])) {
                return $result[0];
            }
            
            // Vrátit výchozí hodnoty pokud dotaz selže
            return [
                'total_animals' => 0,
                'active_animals' => 0,
                'total_examinations' => 0,
                'recent_examinations' => 0
            ];
        } catch (Exception $e) {
            // Log error
            error_log("Workplace::getStats error: " . $e->getMessage());
            
            // Vrátit výchozí hodnoty
            return [
                'total_animals' => 0,
                'active_animals' => 0,
                'total_examinations' => 0,
                'recent_examinations' => 0
            ];
        }
    }
}