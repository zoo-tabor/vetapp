<?php
require_once __DIR__ . '/../core/Model.php';

class User extends Model {
    
    protected $table = 'users';
    
    public function findByUsername($username) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        return $stmt->fetch();
    }
    
    public function getAllActive() {
        return $this->findAll(['is_active' => 1], 'full_name ASC');
    }
    
    public function getWorkplacePermissions($userId) {
        $sql = "
            SELECT w.*, uwp.can_view, uwp.can_edit
            FROM workplaces w
            INNER JOIN user_workplace_permissions uwp ON w.id = uwp.workplace_id
            WHERE uwp.user_id = ? AND w.is_active = 1
        ";
        return $this->query($sql, [$userId]);
    }
    
    public function hasPermission($userId, $workplaceId, $permission = 'view') {
        $column = $permission === 'edit' ? 'can_edit' : 'can_view';
        
        $sql = "
            SELECT $column
            FROM user_workplace_permissions
            WHERE user_id = ? AND workplace_id = ?
        ";
        $result = $this->query($sql, [$userId, $workplaceId]);
        
        return !empty($result) && $result[0][$column] == 1;
    }
    
    public function setWorkplacePermission($userId, $workplaceId, $canView, $canEdit) {
        $sql = "
            INSERT INTO user_workplace_permissions (user_id, workplace_id, can_view, can_edit)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE can_view = ?, can_edit = ?
        ";
        return $this->execute($sql, [$userId, $workplaceId, $canView, $canEdit, $canView, $canEdit]);
    }

    public function getAllUsers() {
        return $this->query("SELECT * FROM users ORDER BY created_at DESC");
    }

    public function usernameExists($username) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() !== false;
    }

    public function createUser($username, $password = null, $fullName = null, $email = null, $role = 'user_read', $isActive = 1) {
        // If password is provided, hash it. Otherwise use a temporary hash
        $passwordHash = $password ? password_hash($password, PASSWORD_DEFAULT) : password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

        $sql = "
            INSERT INTO users (username, password_hash, full_name, email, role, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $this->execute($sql, [$username, $passwordHash, $fullName, $email, $role, $isActive]);

        return $this->db->lastInsertId();
    }

    public function generatePasswordResetToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+48 hours'));

        $sql = "
            UPDATE users
            SET password_reset_token = ?, password_reset_expires = ?
            WHERE id = ?
        ";

        $this->execute($sql, [$token, $expires, $userId]);

        return $token;
    }

    public function findByResetToken($token) {
        $sql = "
            SELECT * FROM users
            WHERE password_reset_token = ?
            AND password_reset_expires > NOW()
        ";

        $results = $this->query($sql, [$token]);
        return $results[0] ?? null;
    }

    public function setPasswordFromToken($token, $newPassword) {
        $user = $this->findByResetToken($token);

        if (!$user) {
            return false;
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $sql = "
            UPDATE users
            SET password_hash = ?, password_reset_token = NULL, password_reset_expires = NULL
            WHERE id = ?
        ";

        return $this->execute($sql, [$passwordHash, $user['id']]);
    }

    public function updateUser($userId, $username, $password = null, $fullName = null, $email = null, $role = 'user_read', $isActive = 1) {
        if ($password) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "
                UPDATE users
                SET username = ?, password_hash = ?, full_name = ?, email = ?, role = ?, is_active = ?
                WHERE id = ?
            ";
            return $this->execute($sql, [$username, $passwordHash, $fullName, $email, $role, $isActive, $userId]);
        } else {
            $sql = "
                UPDATE users
                SET username = ?, full_name = ?, email = ?, role = ?, is_active = ?
                WHERE id = ?
            ";
            return $this->execute($sql, [$username, $fullName, $email, $role, $isActive, $userId]);
        }
    }

    public function getUserPermissions($userId) {
        $sql = "
            SELECT workplace_id, can_view, can_edit
            FROM user_workplace_permissions
            WHERE user_id = ?
        ";
        return $this->query($sql, [$userId]);
    }

    public function saveUserPermissions($userId, $permissions) {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Delete all existing permissions for this user
            $this->execute("DELETE FROM user_workplace_permissions WHERE user_id = ?", [$userId]);

            // Insert new permissions
            foreach ($permissions as $perm) {
                $workplaceId = $perm['workplace_id'];
                $canView = $perm['can_view'] ? 1 : 0;
                $canEdit = $perm['can_edit'] ? 1 : 0;

                // Only insert if at least one permission is granted
                if ($canView || $canEdit) {
                    $this->setWorkplacePermission($userId, $workplaceId, $canView, $canEdit);
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
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Set created_by to NULL in all related tables before deleting user
            // This preserves the records but removes the user reference

            // Try each table individually with detailed logging
            try {
                $this->execute("UPDATE animal_status_history SET created_by = NULL WHERE created_by = ?", [$userId]);
                error_log("User deletion: Updated animal_status_history for user $userId");
            } catch (Exception $e) {
                error_log("User deletion: FAILED updating animal_status_history for user $userId: " . $e->getMessage());
                throw new Exception("Failed to update animal_status_history: " . $e->getMessage());
            }

            try {
                $this->execute("UPDATE examinations SET created_by = NULL WHERE created_by = ?", [$userId]);
                error_log("User deletion: Updated examinations for user $userId");
            } catch (Exception $e) {
                error_log("User deletion: FAILED updating examinations for user $userId: " . $e->getMessage());
                throw new Exception("Failed to update examinations: " . $e->getMessage());
            }

            try {
                $this->execute("UPDATE dewormings SET created_by = NULL WHERE created_by = ?", [$userId]);
                error_log("User deletion: Updated dewormings for user $userId");
            } catch (Exception $e) {
                error_log("User deletion: FAILED updating dewormings for user $userId: " . $e->getMessage());
                throw new Exception("Failed to update dewormings: " . $e->getMessage());
            }

            // Skip tables that don't have created_by column
            error_log("User deletion: Skipping animals table (no created_by column)");
            error_log("User deletion: Skipping enclosures table (no created_by column)");
            error_log("User deletion: Skipping workplaces table (no created_by column)");

            try {
                $this->execute("UPDATE scheduled_checks SET created_by = NULL WHERE created_by = ?", [$userId]);
                error_log("User deletion: Updated scheduled_checks for user $userId");
            } catch (Exception $e) {
                error_log("User deletion: FAILED updating scheduled_checks for user $userId: " . $e->getMessage());
                throw new Exception("Failed to update scheduled_checks: " . $e->getMessage());
            }

            try {
                $this->execute("UPDATE audit_log SET user_id = NULL WHERE user_id = ?", [$userId]);
                error_log("User deletion: Updated audit_log for user $userId");
            } catch (Exception $e) {
                error_log("User deletion: FAILED updating audit_log for user $userId: " . $e->getMessage());
                throw new Exception("Failed to update audit_log: " . $e->getMessage());
            }

            // Delete user permissions
            try {
                $this->execute("DELETE FROM user_workplace_permissions WHERE user_id = ?", [$userId]);
                error_log("User deletion: Deleted permissions for user $userId");
            } catch (Exception $e) {
                error_log("User deletion: FAILED deleting permissions for user $userId: " . $e->getMessage());
                throw new Exception("Failed to delete permissions: " . $e->getMessage());
            }

            // Delete the user
            try {
                $this->execute("DELETE FROM users WHERE id = ?", [$userId]);
                error_log("User deletion: Deleted user $userId");
            } catch (Exception $e) {
                error_log("User deletion: FAILED deleting user $userId: " . $e->getMessage());
                throw new Exception("Failed to delete user: " . $e->getMessage());
            }

            $this->db->commit();
            error_log("User deletion: Successfully deleted user $userId");
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("User deletion: Transaction rolled back for user $userId");
            throw $e;
        }
    }
}