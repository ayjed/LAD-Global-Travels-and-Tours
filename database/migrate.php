<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — CLI DATABASE MIGRATION RUNNER
 * Executes numbered plain SQL migration files in sequence.
 */

if (php_sapi_name() !== 'cli') {
    die("Error: This migration runner can only be executed via the command line interface (CLI).\n");
}

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/bootstrap.php';

use App\Core\Database;

class MigrationRunner
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
        $this->ensureMigrationTableExists();
    }

    private function ensureMigrationTableExists(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL UNIQUE,
            executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $this->pdo->exec($sql);
    }

    public function run(): int
    {
        echo "====================================================================\n";
        echo "LAD GLOBAL TRAVELS — DATABASE MIGRATION RUNNER\n";
        echo "====================================================================\n";

        $migrationsDir = ROOT_PATH . '/database/migrations';
        $files = glob($migrationsDir . '/*.sql');
        sort($files);

        // Fetch already executed migrations
        $stmt = $this->pdo->query("SELECT migration FROM migrations");
        $executed = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $pending = 0;

        foreach ($files as $file) {
            $filename = basename($file);
            if (in_array($filename, $executed, true)) {
                echo "  [ALREADY EXECUTED] {$filename}\n";
                continue;
            }

            echo "  [MIGRATING] {$filename} ... ";
            $sql = file_get_contents($file);

            if ($sql === false || trim($sql) === '') {
                echo "SKIPPED (Empty file)\n";
                continue;
            }

            try {
                // Execute SQL script
                $this->pdo->exec($sql);

                // Record in migrations table
                $insertStmt = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
                $insertStmt->execute([':migration' => $filename]);

                echo "DONE\n";
                $pending++;
            } catch (\Throwable $e) {
                if ($this->pdo->inTransaction()) {
                    $this->pdo->rollBack();
                }
                echo "FAILED!\n";
                echo "Error in {$filename}: " . $e->getMessage() . "\n";
                return 1;
            }
        }

        echo "--------------------------------------------------------------------\n";
        echo "Migration run complete. {$pending} new migration(s) executed.\n";
        echo "--------------------------------------------------------------------\n";
        return 0;
    }
}

$runner = new MigrationRunner();
exit($runner->run());
