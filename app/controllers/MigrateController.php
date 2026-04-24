<?php
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../helpers/env.php';

class MigrateController {

    public function run() {
        $migrateKey = env('MIGRATE_KEY', '');
        if (empty($migrateKey) || ($_GET['key'] ?? '') !== $migrateKey) {
            http_response_code(403);
            die('Neplatný klíč. Přidejte MIGRATE_KEY do .env a předejte ?key=hodnota');
        }

        $pdo = Database::getInstance()->getConnection();

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `migration` VARCHAR(255) NOT NULL UNIQUE,
                `batch` INT UNSIGNED NOT NULL,
                `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $executed  = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
        $nextBatch = (int) $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations")->fetchColumn();

        $files = glob(ROOT_PATH . '/database/migrations/*.php');
        sort($files);

        $pending   = 0;
        $succeeded = 0;

        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html><html lang="cs"><head><meta charset="UTF-8"><title>Migrace</title>';
        echo '<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px}';
        echo 'h1{color:#1a3d2e}.ok{color:#16a34a;background:#f0fdf4;padding:10px;margin:6px 0;border-left:4px solid #16a34a}';
        echo '.err{color:#dc2626;background:#fef2f2;padding:10px;margin:6px 0;border-left:4px solid #dc2626}';
        echo '.skip{color:#6b7280;padding:4px 0}.info{color:#2563eb;background:#eff6ff;padding:10px;margin:10px 0;border-left:4px solid #2563eb}';
        echo 'pre{background:#f3f4f6;padding:10px;overflow-x:auto;font-size:12px}</style></head><body>';
        echo '<h1>Migrace databáze</h1>';

        foreach ($files as $file) {
            $name = basename($file, '.php');

            if (in_array($name, $executed)) {
                echo "<div class=\"skip\">⏭️ Přeskočeno: {$name}</div>\n";
                continue;
            }

            $pending++;
            echo "<div>🔄 Spouštím: {$name}</div>\n";
            ob_flush(); flush();

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

            ob_flush(); flush();
        }

        echo "<div class=\"info\"><strong>Shrnutí:</strong><br>";
        echo "Celkem souborů: " . count($files) . "<br>";
        echo "Již provedeno: " . count($executed) . "<br>";
        echo "Pending: {$pending}<br>Úspěšně provedeno: {$succeeded}</div>";

        if ($pending === 0) {
            echo "<div class=\"ok\">✅ Databáze je aktuální.</div>";
        } elseif ($succeeded === $pending) {
            echo "<div class=\"ok\">✅ Všechny migrace provedeny úspěšně.</div>";
            echo '<p><a href="/">→ Zpět do aplikace</a></p>';
        }

        echo '</body></html>';
    }
}
