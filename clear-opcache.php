<?php
/**
 * Clear PHP OpCache
 * Access this file to clear the opcode cache
 * Delete after use for security
 */

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OpCache cleared successfully!<br>";
} else {
    echo "ℹ️ OpCache is not enabled on this server.<br>";
}

if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "✅ APCu cache cleared successfully!<br>";
} else {
    echo "ℹ️ APCu is not enabled on this server.<br>";
}

echo "<br><strong>Please delete this file (clear-opcache.php) for security!</strong>";
