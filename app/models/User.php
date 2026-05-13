<?php
require_once __DIR__ . '/../core/Model.php';

class User extends Model {

    protected $table = 'users';

    const SECTIONS = [
        'animals'      => 'Seznam zvířat',
        'parasitology' => 'Parazitologie',
        'biochemistry' => 'Biochemie a hematologie',
        'urine'        => 'Analýza moči',
        'vaccination'  => 'Vakcinační plán',
        'warehouse'    => 'Sklad',
        'lexikon'      => 'Lexikon',
    ];

    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function getAllActive() {
        return $this->findAll(['is_active' => 1], 'full_name ASC');
    }

    // Check view or edit permission for a specific section+workplace
    public function hasPermission($userId, $workplaceId, $section, $permission = 'view') {
        require_once __DIR__ . '/../core/Auth.php';
        if (Auth::isAdmin()) return true;

        $column = $permission === 'edit' ? 'can_edit' : 'can_view';
        $sql = "
            SELECT $column FROM user_permissions
            WHERE user_id = ? AND workplace_id = ? AND section = ?
        ";
        $result = $this->query($sql, [$userId, $workplaceId, $section]);
        return !empty($result) && $result[0][$column] == 1;
    }

    // Returns array of section keys the user can view in at least one workplace
    public function getAccessibleSections($userId) {
        require_once __DIR__ . '/../core/Auth.php';
        if (Auth::isAdmin()) return array_keys(self::SECTIONS);

        $sql = "SELECT DISTINCT section FROM user_permissions WHERE user_id = ? AND can_view = 1";
        $rows = $this->query($sql, [$userId]);
        return array_column($rows, 'section');
    }

    // Returns workplaces for the user — admin gets all
    public function getWorkplacePermissions($userId, $section = null) {
        require_once __DIR__ . '/../core/Auth.php';
        if (Auth::isAdmin()) {
            $sql = "SELECT *, 1 as can_view, 1 as can_edit FROM workplaces WHERE is_active = 1 ORDER BY name ASC";
            return $this->query($sql);
        }

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

    public function getAllUsers() {
        return $this->query("SELECT * FROM users ORDER BY created_at DESC");
    }

    public function usernameExists($username) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() !== false;
    }

    public function createUser($username, $password = null, $fullName = null, $email = null, $role = 'user', $isActive = 1) {
        $passwordHash = $password
            ? password_hash($password, PASSWORD_DEFAULT)
            : password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, password_hash, full_name, email, role, is_active) VALUES (?, ?, ?, ?, ?, ?)";
        $this->execute($sql, [$username, $passwordHash, $fullName, $email, $role, $isActive]);
        return $this->db->lastInsertId();
    }

    public function generatePasswordResetToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $this->execute(
            "UPDATE users SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?",
            [$token, $expires, $userId]
        );
        return $token;
    }

    public function findByResetToken($token) {
        $sql = "SELECT * FROM users WHERE password_reset_token = ? AND password_reset_expires > NOW()";
        $results = $this->query($sql, [$token]);
        return $results[0] ?? null;
    }

    public function setPasswordFromToken($token, $newPassword) {
        $user = $this->findByResetToken($token);
        if (!$user) return false;
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->execute(
            "UPDATE users SET password_hash = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?",
            [$passwordHash, $user['id']]
        );
    }

    public function updateUser($userId, $username, $password = null, $fullName = null, $email = null, $role = 'user', $isActive = 1) {
        if ($password) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            return $this->execute(
                "UPDATE users SET username = ?, password_hash = ?, full_name = ?, email = ?, role = ?, is_active = ? WHERE id = ?",
                [$username, $passwordHash, $fullName, $email, $role, $isActive, $userId]
            );
        }
        return $this->execute(
            "UPDATE users SET username = ?, full_name = ?, email = ?, role = ?, is_active = ? WHERE id = ?",
            [$username, $fullName, $email, $role, $isActive, $userId]
        );
    }

    // Returns flat array of {workplace_id, section, can_view, can_edit} for the user
    public function getUserPermissions($userId) {
        $sql = "
            SELECT workplace_id, section, can_view, can_edit
            FROM user_permissions
            WHERE user_id = ?
            ORDER BY workplace_id, section
        ";
        return $this->query($sql, [$userId]);
    }

    // Expects array of {workplace_id, section, can_view, can_edit}
    public function saveUserPermissions($userId, $permissions) {
        $this->db->beginTransaction();
        try {
            $this->execute("DELETE FROM user_permissions WHERE user_id = ?", [$userId]);

            $stmt = $this->db->prepare("
                INSERT INTO user_permissions (user_id, workplace_id, section, can_view, can_edit)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE can_view = VALUES(can_view), can_edit = VALUES(can_edit)
            ");

            foreach ($permissions as $perm) {
                $canEdit = $perm['can_edit'] ? 1 : 0;
                $canView = ($perm['can_view'] || $canEdit) ? 1 : 0; // edit implies view
                if ($canView || $canEdit) {
                    $stmt->execute([
                        $userId,
                        $perm['workplace_id'],
                        $perm['section'],
                        $canView,
                        $canEdit,
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteUser($userId) {
        $this->db->beginTransaction();
        try {
            $this->execute("UPDATE animal_status_history SET created_by = NULL WHERE created_by = ?", [$userId]);
            $this->execute("UPDATE examinations SET created_by = NULL WHERE created_by = ?", [$userId]);
            $this->execute("UPDATE dewormings SET created_by = NULL WHERE created_by = ?", [$userId]);
            $this->execute("UPDATE scheduled_checks SET created_by = NULL WHERE created_by = ?", [$userId]);
            $this->execute("UPDATE audit_log SET user_id = NULL WHERE user_id = ?", [$userId]);
            $this->execute("DELETE FROM user_permissions WHERE user_id = ?", [$userId]);
            $this->execute("DELETE FROM users WHERE id = ?", [$userId]);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
