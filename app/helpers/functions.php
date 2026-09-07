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
        'user'  => 'Uživatel',
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
 * Get (or lazily create) the per-session CSRF token.
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token sent with a state-changing request.
 * Accepts the token from the _csrf POST field or the X-CSRF-Token header.
 */
function csrf_validate() {
    $session = $_SESSION['csrf_token'] ?? '';
    if ($session === '') {
        return false;
    }
    $sent = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($sent) && $sent !== '' && hash_equals($session, $sent);
}

/**
 * Sanitize a redirect target so it can only point to an internal (same-site) path.
 * Rejects absolute URLs and protocol-relative (//host) values -> returns $default.
 */
function internalPath($value, $default = '/') {
    $value = (string)$value;
    if ($value === '' || $value[0] !== '/') {
        return $default;
    }
    // Block "//host" and "/\host" (protocol-relative / backslash tricks)
    if (isset($value[1]) && ($value[1] === '/' || $value[1] === '\\')) {
        return $default;
    }
    return $value;
}

/**
 * Názvy parametrů označených jako "index kvality vzorku" (migrace 026).
 * Guarded – funguje i před spuštěním migrace (sloupec ještě nemusí existovat),
 * kdy vrátí prázdno a použije se jen fallback dle názvu. Cache v rámci requestu.
 */
function sampleQualityParamNames() {
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    try {
        require_once __DIR__ . '/../core/Database.php';
        $db = Database::getInstance()->getConnection();
        $rows = $db->query("SELECT name FROM lab_parameters WHERE is_quality_index = 1")
                   ->fetchAll(PDO::FETCH_COLUMN);
        $cache = array_map(function ($n) {
            return function_exists('mb_strtolower') ? mb_strtolower($n, 'UTF-8') : strtolower($n);
        }, $rows);
    } catch (Exception $e) {
        $cache = [];
    }
    return $cache;
}

/**
 * Je daný název parametru index kvality vzorku (lipemický/hemolytický)?
 * Rozhoduje primárně příznak is_quality_index z číselníku; fallback dle názvu
 * pokrývá stav před migrací i případné přejmenování.
 */
function isSampleQualityParam($name) {
    // Strhnout diakritiku + malá písmena, aby sedly i české názvy jako
    // "Lipémie" / "Hemolýza" (jinak by é/ý rozbily ASCII vzory níže).
    $n = strtr((string)$name, [
        'á'=>'a','č'=>'c','ď'=>'d','é'=>'e','ě'=>'e','í'=>'i','ň'=>'n','ó'=>'o','ř'=>'r','š'=>'s',
        'ť'=>'t','ú'=>'u','ů'=>'u','ý'=>'y','ž'=>'z',
        'Á'=>'a','Č'=>'c','Ď'=>'d','É'=>'e','Ě'=>'e','Í'=>'i','Ň'=>'n','Ó'=>'o','Ř'=>'r','Š'=>'s',
        'Ť'=>'t','Ú'=>'u','Ů'=>'u','Ý'=>'y','Ž'=>'z',
    ]);
    $n = function_exists('mb_strtolower') ? mb_strtolower($n, 'UTF-8') : strtolower($n);
    if ($n === '') {
        return false;
    }
    if (in_array($n, sampleQualityParamNames(), true)) {
        return true;
    }
    // Lipémie/Lipaemia index + Hemolýza/Haemolysis index (vč. anglických variant).
    return (strpos($n, 'lipem') !== false || strpos($n, 'lipaem') !== false
         || strpos($n, 'hemol') !== false || strpos($n, 'aemol') !== false);
}

/**
 * Single permission gate helper (admin bypass + per-workplace section check).
 * Returns bool; controllers decide how to respond (HTML vs JSON).
 */
function userCan($workplaceId, $section, $perm = 'view') {
    require_once __DIR__ . '/../core/Auth.php';
    require_once __DIR__ . '/../models/User.php';
    if (Auth::isAdmin()) {
        return true;
    }
    $userModel = new User();
    return $userModel->hasPermission(Auth::userId(), $workplaceId, $section, $perm);
}

/**
 * Seznam laboratoří (referenčních zdrojů) pro nabídky v UI.
 *
 * Základ je číselník reference_sources; pokud tabulka ještě neexistuje nebo je
 * prázdná, použije se výchozí čtveřice. $used doplní zdroje reálně přiřazené
 * konkrétním odběrům, aby se z nabídky nikdy neztratil zdroj daného testu
 * (např. laboratoř zavedená ručně mimo číselník).
 */
function labReferenceSources(array $used = []) {
    static $catalog = null;
    if ($catalog === null) {
        try {
            require_once __DIR__ . '/../core/Database.php';
            $db = Database::getInstance()->getConnection();
            $catalog = $db->query("SELECT source_name FROM reference_sources ORDER BY source_name ASC")
                          ->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            $catalog = [];
        }
        if (empty($catalog)) {
            $catalog = ['Idexx', 'Laboklin', 'Synlab', 'ZIMS'];
        }
    }

    $all = $catalog;
    foreach ($used as $source) {
        $source = trim((string)$source);
        if ($source !== '' && !in_array($source, $all, true)) {
            $all[] = $source;
        }
    }
    sort($all, SORT_NATURAL | SORT_FLAG_CASE);
    return $all;
}

/**
 * Laboratoře (referenční zdroje) použité v dané sadě odběrů – bez duplicit,
 * v pořadí výskytu.
 */
function labUsedSources(array $tests) {
    $sources = [];
    foreach ($tests as $test) {
        $source = trim((string)($test['reference_source'] ?? ''));
        if ($source !== '' && !in_array($source, $sources, true)) {
            $sources[] = $source;
        }
    }
    return $sources;
}

/**
 * Textový zápis jedněch referenčních mezí (podporuje i otevřenou mez).
 */
function labRefRangeText($range) {
    if (!$range) {
        return '';
    }
    $hasMin = isset($range['min_value']) && $range['min_value'] !== null && $range['min_value'] !== '';
    $hasMax = isset($range['max_value']) && $range['max_value'] !== null && $range['max_value'] !== '';
    if ($hasMin && $hasMax) {
        return $range['min_value'] . ' - ' . $range['max_value'];
    }
    if ($hasMin) {
        return '> ' . $range['min_value'];
    }
    if ($hasMax) {
        return '< ' . $range['max_value'];
    }
    return '';
}

/**
 * HTML buňky "Referenční meze" pro sloupec sdílený více odběry.
 * Když všechny laboratoře dávají stejné meze, vypíše se jeden řádek; jinak se
 * meze rozepíší po laboratořích (sloučit je do jednoho čísla by lhalo).
 * Vrací hotové HTML – volající už neescapuje.
 */
function labRefCellHtml(array $rangesBySource, array $sources) {
    $lines = [];
    foreach ($sources as $source) {
        $lines[$source] = labRefRangeText($rangesBySource[$source] ?? null);
    }
    if (empty($lines)) {
        return '';
    }

    $distinct = array_unique(array_values($lines));
    if (count($distinct) === 1) {
        return htmlspecialchars(reset($distinct));
    }

    $html = '';
    foreach ($lines as $source => $text) {
        $html .= '<span class="ref-line"><span class="ref-line-source">'
              . htmlspecialchars($source) . ':</span> '
              . htmlspecialchars($text === '' ? '-' : $text) . '</span>';
    }
    return $html;
}
