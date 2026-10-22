<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/bootstrap.php';

$classes = [
    \App\Core\Auth::class,
    \App\Core\Database::class,
    \App\Core\Env::class,
    \App\Core\RateLimiter::class,
    \App\Core\Response::class,
    \App\Core\Router::class,
    \App\Core\Security::class,
    \App\Core\Validator::class,
    \App\Core\View::class,
    \App\Controllers\AdminController::class,
    \App\Controllers\BookingController::class,
    \App\Controllers\PublicController::class,
    \App\Services\BookingService::class,
    \App\Services\EnquiryService::class,
    \App\Services\MailService::class,
    \App\Services\PackageService::class,
    \App\Services\PaystackService::class,
    \App\Services\SettingService::class,
    \App\Services\UploadService::class,
];

echo "Testing autoloading of all " . count($classes) . " application classes:\n";
$allOk = true;
foreach ($classes as $cls) {
    if (class_exists($cls)) {
        echo " [OK] " . $cls . "\n";
    } else {
        echo " [FAIL] " . $cls . "\n";
        $allOk = false;
    }
}

if ($allOk) {
    echo "\nAll application classes resolved and autoloaded successfully!\n";
} else {
    echo "\nSome classes failed to autoload!\n";
    exit(1);
}
