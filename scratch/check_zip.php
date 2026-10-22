<?php
$zip = new ZipArchive();
if ($zip->open('lad_travels_cpanel_upload.zip') === true) {
    $content = $zip->getFromName('app/services/SettingService.php');
    echo "SettingService in zip length: " . strlen((string)$content) . "\n";
    echo "Has formatWhatsAppUrl: " . (strpos((string)$content, 'formatWhatsAppUrl') !== false ? 'YES' : 'NO') . "\n";
    echo "Has formatPhoneLink: " . (strpos((string)$content, 'formatPhoneLink') !== false ? 'YES' : 'NO') . "\n";
    
    // Check .htaccess
    $htaccess = $zip->getFromName('.htaccess');
    echo ".htaccess in zip: " . (strlen((string)$htaccess) > 0 ? 'YES' : 'NO') . "\n";

    // Check .env
    $env = $zip->getFromName('.env');
    echo ".env in zip length: " . strlen((string)$env) . "\n";

    $zip->close();
} else {
    echo "Could not open zip\n";
}
