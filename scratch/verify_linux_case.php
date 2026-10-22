<?php
declare(strict_types=1);

$classes = [
    'App\\Core\\Auth',
    'App\\Core\\Database',
    'App\\Core\\Env',
    'App\\Core\\RateLimiter',
    'App\\Core\\Response',
    'App\\Core\\Router',
    'App\\Core\\Security',
    'App\\Core\\Validator',
    'App\\Core\\View',
    'App\\Controllers\\AdminController',
    'App\\Controllers\\BookingController',
    'App\\Controllers\\PublicController',
    'App\\Services\\BookingService',
    'App\\Services\\EnquiryService',
    'App\\Services\\MailService',
    'App\\Services\\PackageService',
    'App\\Services\\PaystackService',
    'App\\Services\\SettingService',
    'App\\Services\\UploadService',
];

$baseDir = str_replace('\\', '/', realpath(__DIR__ . '/../app')) . '/';
$allOk = true;

foreach ($classes as $class) {
    $prefix = 'App\\';
    $relativeClass = substr($class, strlen($prefix));
    $parts = explode('\\', $relativeClass);
    $className = array_pop($parts);
    $lowerDir = implode('/', array_map('strtolower', $parts));
    $targetFile = $baseDir . $lowerDir . '/' . $className . '.php';

    // Verify against actual scandir of directory
    $dirToScan = $baseDir . $lowerDir;
    if (!is_dir($dirToScan)) {
        echo "[ERROR] Directory does not exist: $dirToScan\n";
        $allOk = false;
        continue;
    }

    $filesInDir = scandir($dirToScan);
    $expectedFile = $className . '.php';
    if (!in_array($expectedFile, $filesInDir, true)) {
        echo "[ERROR] File casing mismatch! Looking for '$expectedFile' in $lowerDir, found: " . implode(', ', $filesInDir) . "\n";
        $allOk = false;
    } else {
        echo "[MATCH] $class => app/$lowerDir/$expectedFile\n";
    }
}

if ($allOk) {
    echo "\n100% PERFECT MATCH on exact directory and file casing across all 19 classes!\n";
} else {
    exit(1);
}
