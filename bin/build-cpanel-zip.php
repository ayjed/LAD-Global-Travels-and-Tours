<?php

declare(strict_types=1);

/**
 * LAD GLOBAL TRAVELS AND TOURS — CPANEL PRODUCTION ARCHIVE BUILDER
 * Assembles a clean, optimized distribution package and creates `lad_travels_cpanel_upload.zip`.
 */

if (php_sapi_name() !== 'cli') {
    die("CLI execution only\n");
}

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/bootstrap.php';

echo "====================================================================\n";
echo "LAD GLOBAL TRAVELS — BUILDING CPANEL PRODUCTION PACKAGE\n";
echo "====================================================================\n\n";

// 1. Ensure latest database export exists
echo "1. Generating production database export (lad_global_production.sql)...\n";
require_once ROOT_PATH . '/bin/export-production-db.php';

// 2. Prepare Staging Directory
$stagingDir = ROOT_PATH . '/cpanel_ready';
if (is_dir($stagingDir)) {
    echo "Cleaning previous staging directory...\n";
    deleteDirectory($stagingDir);
}
@mkdir($stagingDir, 0755, true);

echo "\n2. Copying production files to staging directory...\n";

// Helper recursive copy
function copyDirectory(string $src, string $dst, array $exclude = []): void
{
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    while (false !== ($file = readdir($dir))) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        if (in_array($file, $exclude, true)) {
            continue;
        }
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        if (is_dir($srcPath)) {
            copyDirectory($srcPath, $dstPath, $exclude);
        } else {
            copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
}

function deleteDirectory(string $dir): void
{
    if (!file_exists($dir)) {
        return;
    }
    $files = array_diff(scandir($dir) ?: [], ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? deleteDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

// Copy app/
echo " • Copying app/ (Controllers, Core, Services, Views)...\n";
copyDirectory(ROOT_PATH . '/app', $stagingDir . '/app');

// Also create uppercase directory aliases for strict Linux PSR-4 compatibility
copyDirectory(ROOT_PATH . '/app/core', $stagingDir . '/app/Core');
copyDirectory(ROOT_PATH . '/app/controllers', $stagingDir . '/app/Controllers');
copyDirectory(ROOT_PATH . '/app/services', $stagingDir . '/app/Services');

// Copy public/
echo " • Copying public/ (Assets, images, CSS, JS, front controller)...\n";
copyDirectory(ROOT_PATH . '/public', $stagingDir . '/public');

// Clean any test-generated uploads from staging (preserve secure directory structure only)
if (is_dir($stagingDir . '/public/uploads/packages')) {
    deleteDirectory($stagingDir . '/public/uploads/packages');
    @mkdir($stagingDir . '/public/uploads/packages', 0755, true);
    file_put_contents($stagingDir . '/public/uploads/packages/.gitkeep', "# package uploads\n");
}

// Copy database/
echo " • Copying database/ (Schemas, migrations & lad_global_production.sql)...\n";
copyDirectory(ROOT_PATH . '/database', $stagingDir . '/database');

// Copy storage/ (Clean structure only)
echo " • Setting up clean storage/ (logs, temp)...\n";
@mkdir($stagingDir . '/storage/logs', 0755, true);
@mkdir($stagingDir . '/storage/temp', 0755, true);
file_put_contents($stagingDir . '/storage/logs/.gitkeep', "# storage/logs\n");
file_put_contents($stagingDir . '/storage/temp/.gitkeep', "# storage/temp\n");

// Copy bin/ (Essential CLI maintenance tools only)
echo " • Copying bin/ (create-admin.php, unlock-admin.php)...\n";
@mkdir($stagingDir . '/bin', 0755, true);
copy(ROOT_PATH . '/bin/create-admin.php', $stagingDir . '/bin/create-admin.php');
copy(ROOT_PATH . '/bin/unlock-admin.php', $stagingDir . '/bin/unlock-admin.php');

// Copy Root Files
echo " • Copying root configuration files (.env.production.example, .htaccess, instructions)...\n";
copy(ROOT_PATH . '/.env.production', $stagingDir . '/.env.production.example');
copy(ROOT_PATH . '/.htaccess', $stagingDir . '/.htaccess');
copy(ROOT_PATH . '/CPANEL_DEPLOYMENT_INSTRUCTIONS.md', $stagingDir . '/CPANEL_DEPLOYMENT_INSTRUCTIONS.md');

// NOTE: We deliberately DO NOT create `.env` in the package to prevent accidental
// overwriting of existing production server configuration upon cPanel unzipping!

// Also place a direct copy of lad_global_production.sql in the package root for extra convenience!
copy(ROOT_PATH . '/database/lad_global_production.sql', $stagingDir . '/lad_global_production.sql');

echo "\n3. Creating ZIP archive: lad_travels_cpanel_upload.zip...\n";
$zipFile = ROOT_PATH . '/lad_travels_cpanel_upload.zip';
if (file_exists($zipFile)) {
    @unlink($zipFile);
}

// Compress using Windows native tar
$cmd = sprintf(
    'tar.exe -a -c -f "%s" -C "%s" *',
    $zipFile,
    $stagingDir
);
exec($cmd, $output, $returnCode);

if ($returnCode !== 0 || !file_exists($zipFile)) {
    echo "Tar failed, trying PowerShell Compress-Archive...\n";
    $psCmd = sprintf(
        'powershell.exe -NoProfile -Command "Compress-Archive -Path \'%s\\*\' -DestinationPath \'%s\' -Force"',
        $stagingDir,
        $zipFile
    );
    exec($psCmd, $output, $returnCode);
}

if (file_exists($zipFile)) {
    $sizeMB = round(filesize($zipFile) / (1024 * 1024), 2);
    echo "\n====================================================================\n";
    echo "[SUCCESS] cPanel Upload Package Created Successfully!\n";
    echo "Archive: lad_travels_cpanel_upload.zip ({$sizeMB} MB)\n";
    echo "Location: " . $zipFile . "\n";
    echo "Staging folder: cpanel_ready/ (also available for manual inspection/upload)\n";
    echo "====================================================================\n";
} else {
    echo "[ERROR] Failed to create ZIP archive.\n";
    exit(1);
}
