<?php

$urls = [
    'https://portal.freesms.uk/',
    'https://portal.freesms.uk/index.php',
    'https://portal.freesms.uk/assets/css/app.css',
    'https://portal.freesms.uk/assets/images/lad-logo-header.png',
    'https://portal.freesms.uk/robots.txt',
    'https://portal.freesms.uk/favicon.ico',
    'https://portal.freesms.uk/.env',
    'https://portal.freesms.uk/storage/logs/app.log',
    'https://portal.freesms.uk/nonexistent-file-123.css',
];

foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $size = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

    echo sprintf("%-60s | Code: %s | Size: %6d | Type: %s\n", $url, $code, $size, $contentType);
}
