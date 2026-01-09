<?php
/**
 * OPcache clearing script
 * Access this file via browser to clear PHP opcode cache
 * DELETE THIS FILE after use for security!
 */

// Security: Only allow from localhost or add password protection
// Uncomment and set password if needed:
// $password = 'your_secure_password_here';
// if (!isset($_GET['pwd']) || $_GET['pwd'] !== $password) {
//     die('Unauthorized');
// }

echo "<h1>OPcache Status</h1>";

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "<p style='color: green;'>✓ OPcache has been cleared successfully!</p>";

    // Show cache info
    $status = opcache_get_status();
    echo "<p>OPcache is enabled: " . ($status['opcache_enabled'] ? 'Yes' : 'No') . "</p>";
    echo "<p>Memory usage: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB</p>";
} else {
    echo "<p style='color: orange;'>⚠ OPcache is not enabled on this server.</p>";
}

echo "<br><a href='/'>← Back to application</a>";
echo "<br><br><strong>IMPORTANT:</strong> Delete this file (clear-cache.php) after use for security!";
