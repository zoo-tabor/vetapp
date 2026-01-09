<?php
/**
 * Pomocné funkce pro celou aplikaci
 */

/**
 * Escapovat HTML pro bezpečný výstup
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Formátovat datum do českého formátu
 */
function formatDate($date, $format = 'd.m.Y') {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

/**
 * Formátovat datum a čas do českého formátu
 */
function formatDateTime($datetime, $format = 'd.m.Y H:i') {
    if (!$datetime) return '-';
    return date($format, strtotime($datetime));
}

/**
 * Vypočítat věk z data narození
 */
function calculateAge($birthDate) {
    if (!$birthDate) return null;
    
    $birth = new DateTime($birthDate);
    $today = new DateTime('today');
    $age = $birth->diff($today)->y;
    
    return $age;
}

/**
 * Získat české označení stavu zvířete
 */
function getAnimalStatusLabel($status) {
    $labels = [
        'active' => 'Aktivní',
        'transferred' => 'Přesunuto',
        'deceased' => 'Uhynulo',
        'removed' => 'Vyřazeno'
    ];
    
    return $labels[$status] ?? 'Neznámý';
}

/**
 * Získat badge třídu pro stav zvířete
 */
function getAnimalStatusBadge($status) {
    $badges = [
        'active' => 'success',
        'transferred' => 'info',
        'deceased' => 'danger',
        'removed' => 'secondary'
    ];
    
    return $badges[$status] ?? 'secondary';
}

/**
 * Získat české označení pohlaví
 */
function getGenderLabel($gender) {
    $labels = [
        'male' => 'Samec',
        'female' => 'Samice',
        'unknown' => 'Neznámé'
    ];
    
    return $labels[$gender] ?? 'Neznámé';
}

/**
 * Získat české označení typu vzorku
 */
function getSampleTypeLabel($type) {
    $labels = [
        'individual' => 'Individuální',
        'mixed' => 'Směsný'
    ];
    
    return $labels[$type] ?? 'Neznámý';
}

/**
 * Získat české označení nálezu
 */
function getFindingStatusLabel($status) {
    $labels = [
        'positive' => 'Pozitivní',
        'negative' => 'Negativní'
    ];
    
    return $labels[$status] ?? 'Neznámý';
}

/**
 * Získat badge třídu pro nález
 */
function getFindingStatusBadge($status) {
    $badges = [
        'positive' => 'warning',
        'negative' => 'success'
    ];
    
    return $badges[$status] ?? 'secondary';
}

/**
 * Získat české označení role uživatele
 */
function getRoleLabel($role) {
    $labels = [
        'admin' => 'Administrátor',
        'user_edit' => 'Uživatel - editace',
        'user_read' => 'Uživatel - čtení'
    ];
    
    return $labels[$role] ?? 'Neznámá role';
}

/**
 * Zkrátit text na určitý počet znaků
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    
    return mb_substr($text, 0, $length) . $suffix;
}

/**
 * Vytvořit URL s parametry
 */
function url($path, $params = []) {
    $config = require __DIR__ . '/../config/config.php';
    $url = rtrim($config['app_url'], '/') . '/' . ltrim($path, '/');
    
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    return $url;
}

/**
 * Přesměrovat na jinou stránku
 */
function redirect($path, $params = []) {
    header('Location: ' . url($path, $params));
    exit;
}

/**
 * Vrátit JSON odpověď
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Validovat email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitizovat string (odstranit nebezpečné znaky)
 */
function sanitizeString($string) {
    return filter_var($string, FILTER_SANITIZE_STRING);
}

/**
 * Získat hodnotu z pole, nebo vrátit výchozí
 */
function arrayGet($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

/**
 * Zkontrolovat, zda je požadavek POST
 */
function isPost() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Zkontrolovat, zda je požadavek GET
 */
function isGet() {
    return $_SERVER['REQUEST_METHOD'] === 'GET';
}

/**
 * Získat POST hodnotu
 */
function post($key, $default = null) {
    return isset($_POST[$key]) ? $_POST[$key] : $default;
}

/**
 * Získat GET hodnotu
 */
function get($key, $default = null) {
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}

/**
 * Nastavit flash zprávu do session
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Získat a smazat flash zprávu ze session
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Debug výpis (pouze v debug módu)
 */
function dd($data) {
    $config = require __DIR__ . '/../config/config.php';
    
    if ($config['debug']) {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
        die();
    }
}

/**
 * Logovat chybu do souboru
 */
function logError($message, $context = []) {
    $logFile = __DIR__ . '/../../logs/error.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? json_encode($context) : '';
    $logMessage = "[$timestamp] $message $contextStr" . PHP_EOL;
    
    error_log($logMessage, 3, $logFile);
}

/**
 * Vygenerovat náhodný token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Formátovat číslo s mezerami jako oddělovačem tisíců
 */
function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, ',', ' ');
}

/**
 * Získat aktuální URL
 */
function currentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

/**
 * Zkontrolovat, zda uživatel má oprávnění
 */
function hasPermission($permission) {
    require_once __DIR__ . '/../core/Auth.php';
    
    if ($permission === 'admin') {
        return Auth::isAdmin();
    }
    
    if ($permission === 'edit') {
        return Auth::canEdit();
    }
    
    return Auth::check();
}