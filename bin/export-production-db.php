<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS — PRODUCTION DATABASE EXPORTER
 * Exports clean, production-ready schema and essential seed data (packages, settings, admin).
 * Strictly excludes testing records and dummy customer bookings.
 */

if (php_sapi_name() !== 'cli') {
    die("CLI execution only\n");
}

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/bootstrap.php';

use App\Core\Database;

$pdo = Database::getConnection();

echo "=== Exporting Production Database Schema & Seed Data ===\n";

$sqlOutput = "-- =============================================================================\n";
$sqlOutput .= "-- LAD GLOBAL TRAVELS AND TOURS — PRODUCTION DATABASE EXPORT\n";
$sqlOutput .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
$sqlOutput .= "-- Target: MySQL 8.0+ / MariaDB 10.4+ (utf8mb4_unicode_ci)\n";
$sqlOutput .= "-- =============================================================================\n\n";
$sqlOutput .= "SET FOREIGN_KEY_CHECKS = 0;\n";
$sqlOutput .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
$sqlOutput .= "SET time_zone = '+00:00';\n\n";

// 1. Append Migrations 001 - 005
$migrationFiles = glob(ROOT_PATH . '/database/migrations/*.sql');
sort($migrationFiles);

foreach ($migrationFiles as $file) {
    $filename = basename($file);
    echo "Processing migration: {$filename}\n";
    $sqlOutput .= "-- -----------------------------------------------------------------------------\n";
    $sqlOutput .= "-- Table Structure: {$filename}\n";
    $sqlOutput .= "-- -----------------------------------------------------------------------------\n";
    $content = file_get_contents($file);
    $sqlOutput .= trim($content) . "\n\n";
}

// 2. Export Essential Production Settings
echo "Exporting system settings...\n";
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settings = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($settings)) {
    $sqlOutput .= "-- -----------------------------------------------------------------------------\n";
    $sqlOutput .= "-- System & Organization Settings\n";
    $sqlOutput .= "-- -----------------------------------------------------------------------------\n";
    $sqlOutput .= "INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES\n";
    $settingRows = [];
    foreach ($settings as $s) {
        $settingRows[] = sprintf(
            "(%s, %s, NOW())",
            $pdo->quote($s['setting_key']),
            $pdo->quote($s['setting_value'] ?? '')
        );
    }
    $sqlOutput .= implode(",\n", $settingRows) . "\nON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`), `updated_at` = NOW();\n\n";
}

// 3. Export Published Packages & Package Images
echo "Exporting published tour packages and galleries...\n";
$stmt = $pdo->query("SELECT * FROM packages WHERE status = 'published' ORDER BY id ASC");
$packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($packages)) {
    $sqlOutput .= "-- -----------------------------------------------------------------------------\n";
    $sqlOutput .= "-- Tour Packages\n";
    $sqlOutput .= "-- -----------------------------------------------------------------------------\n";
    $pkgIds = [];
    foreach ($packages as $pkg) {
        $pkgIds[] = (int)$pkg['id'];
        foreach ($pkg as $k => $val) {
            if (is_string($val)) {
                $val = str_replace(["???", "â€¢", "•"], ["-", "-", "-"], $val);
                $pkg[$k] = $val;
            }
        }
        $cols = array_keys($pkg);
        $escapedCols = array_map(fn($c) => "`{$c}`", $cols);
        $escapedVals = array_map(function($v) use ($pdo) {
            return $v === null ? 'NULL' : $pdo->quote((string)$v);
        }, array_values($pkg));

        $updateAssignments = [];
        foreach ($cols as $col) {
            if ($col !== 'id') {
                $updateAssignments[] = "`{$col}` = VALUES(`{$col}`)";
            }
        }

        $sqlOutput .= sprintf(
            "INSERT INTO `packages` (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s;\n",
            implode(', ', $escapedCols),
            implode(', ', $escapedVals),
            implode(', ', $updateAssignments)
        );
    }
    $sqlOutput .= "\n";

    if (!empty($pkgIds)) {
        $inClause = implode(',', $pkgIds);
        $imgStmt = $pdo->query("SELECT * FROM package_images WHERE package_id IN ({$inClause}) ORDER BY display_order ASC");
        $images = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($images)) {
            $sqlOutput .= "-- -----------------------------------------------------------------------------\n";
            $sqlOutput .= "-- Package Gallery Images\n";
            $sqlOutput .= "-- -----------------------------------------------------------------------------\n";
            foreach ($images as $img) {
                $cols = array_keys($img);
                $escapedCols = array_map(fn($c) => "`{$c}`", $cols);
                $escapedVals = array_map(function($v) use ($pdo) {
                    return $v === null ? 'NULL' : $pdo->quote((string)$v);
                }, array_values($img));

                $imgUpdateAssignments = [];
                foreach ($cols as $c) {
                    if ($c !== 'id') {
                        $imgUpdateAssignments[] = "`{$c}` = VALUES(`{$c}`)";
                    }
                }

                $sqlOutput .= sprintf(
                    "INSERT INTO `package_images` (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s;\n",
                    implode(', ', $escapedCols),
                    implode(', ', $escapedVals),
                    implode(', ', $imgUpdateAssignments)
                );
            }
            $sqlOutput .= "\n";
        }
    }
}

// 4. Export Super Administrator Account (ID 19 - clean state)
echo "Exporting primary administrator account...\n";
$stmt = $pdo->query("SELECT id, username, email, password_hash, created_at FROM admins WHERE username = 'admin' LIMIT 1");
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin) {
    $sqlOutput .= "-- -----------------------------------------------------------------------------\n";
    $sqlOutput .= "-- Initial Super Administrator Account\n";
    $sqlOutput .= "-- -----------------------------------------------------------------------------\n";
    $sqlOutput .= sprintf(
        "INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `failed_login_attempts`, `locked_until`, `created_at`) VALUES (%d, %s, %s, %s, 0, NULL, NOW()) ON DUPLICATE KEY UPDATE `username` = VALUES(`username`), `email` = VALUES(`email`), `password_hash` = VALUES(`password_hash`);\n\n",
        (int)$admin['id'],
        $pdo->quote($admin['username']),
        $pdo->quote($admin['email']),
        $pdo->quote($admin['password_hash'])
    );
}

$sqlOutput .= "SET FOREIGN_KEY_CHECKS = 1;\n";

// Save to database/lad_global_production.sql
$outPath = ROOT_PATH . '/database/lad_global_production.sql';
file_put_contents($outPath, $sqlOutput);

echo "[SUCCESS] Production SQL export written to: database/lad_global_production.sql (" . strlen($sqlOutput) . " bytes)\n";
