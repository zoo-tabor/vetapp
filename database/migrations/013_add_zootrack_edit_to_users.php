<?php
/**
 * Add a global per-user ZooTrack edit capability.
 *
 * ZooTrack data is global (not workplace-scoped), so this is a single flag on the
 * users table rather than a per-workplace user_permissions row. Admins bypass it.
 * The flag is read into the session at login (app/core/Auth.php) and enforced by
 * the ZooTrack API on write actions.
 */
return function(PDO $pdo) {
    $pdo->exec("
        ALTER TABLE `users`
        ADD COLUMN `zootrack_edit` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_active`
    ");
};
