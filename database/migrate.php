<?php
/**
 * Database Migration Runner
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

require_once APP_PATH . '/helpers/env.php';
loadEnv(ROOT_PATH . '/.env');

require_once APP_PATH . '/core/Database.php';

// Security key from .env
$migrateKey = env('MIGRATE_KEY', '');
if (empty($migrateKey) || !isset($_GET['key']) || $_GET['key'] !== $migrateKey) {
    http_response_code(403);
    die('Neplatný klíč. Přidejte MIGRATE_KEY do .env a předejte ?key=hodnota');
}

$pdo = Database::getInstance()->getConnection();

// Create migrations tracking table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS `migrations` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `migration` VARCHAR(255) NOT NULL UNIQUE,
        `batch` INT UNSIGNED NOT NULL,
        `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");

// Get already-executed migrations
$executed = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

// Get next batch number
$nextBatch = (int) $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations")->fetchColumn();

// Load migration files
$files = glob(__DIR__ . '/migrations/*.php');
sort($files);

$pending  = 0;
$succeeded = 0;

?><!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Migrace databáze</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        h1 { color: #1a3d2e; }
        .ok   { color: #16a34a; background: #f0fdf4; padding: 10px; margin: 6px 0; border-left: 4px solid #16a34a; }
        .err  { color: #dc2626; background: #fef2f2; padding: 10px; margin: 6px 0; border-left: 4px solid #dc2626; }
        .skip { color: #6b7280; padding: 4px 0; }
        .info { color: #2563eb; background: #eff6ff; padding: 10px; margin: 10px 0; border-left: 4px solid #2563eb; }
        pre  { background: #f3f4f6; padding: 10px; overflow-x: auto; font-size: 12px; }
    </style>
</head>
<body>
<h1>Migrace databáze</h1>
<?php

foreach ($files as $file) {
    $name = basename($file, '.php');

    if (in_array($name, $executed)) {
        echo "<div class=\"skip\">⏭️ Přeskočeno: {$name}</div>\n";
        continue;
    }

    $pending++;
    echo "<div>🔄 Spouštím: {$name}</div>\n";
    flush();

    try {
        $migration = include $file;
        if (is_callable($migration)) {
            $migration($pdo);
        }

        $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$name, $nextBatch]);

        echo "<div class=\"ok\">✅ Hotovo: {$name}</div>\n";
        $succeeded++;
    } catch (Exception $e) {
        echo "<div class=\"err\">❌ Chyba ({$name}): " . htmlspecialchars($e->getMessage()) . "</div>\n";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
        break;
    }

    flush();
}

?>
<div class="info">
    <strong>Shrnutí:</strong><br>
    Celkem souborů: <?= count($files) ?><br>
    Již provedeno: <?= count($executed) ?><br>
    Pending: <?= $pending ?><br>
    Úspěšně provedeno: <?= $succeeded ?>
</div>
<?php if ($pending === 0): ?>
    <div class="ok">✅ Databáze je aktuální.</div>
<?php elseif ($succeeded === $pending): ?>
    <div class="ok">✅ Všechny migrace provedeny úspěšně.</div>
    <p><a href="/">→ Zpět do aplikace</a></p>
<?php endif; ?>
</body>
</html>
