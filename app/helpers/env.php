<?php
/**
 * Environment variable loader
 * Loads .env file and makes variables available via env() function
 * Compatible with hosting environments where putenv() is disabled
 */

function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }

            // Set environment variable (only $_ENV and $_SERVER, no putenv() for compatibility)
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

/**
 * Get environment variable with optional default
 */
function env($key, $default = null) {
    // Check $_ENV first
    if (isset($_ENV[$key])) {
        $value = $_ENV[$key];
    }
    // Then check $_SERVER
    elseif (isset($_SERVER[$key])) {
        $value = $_SERVER[$key];
    }
    // Finally try getenv() if available
    elseif (function_exists('getenv')) {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
    }
    // No value found
    else {
        return $default;
    }

    // Convert string booleans to actual booleans
    switch (strtolower($value)) {
        case 'true':
        case '(true)':
            return true;
        case 'false':
        case '(false)':
            return false;
        case 'null':
        case '(null)':
            return null;
    }

    return $value;
}
