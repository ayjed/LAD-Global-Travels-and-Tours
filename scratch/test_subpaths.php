<?php

$urls = [
    'https://portal.freesms.uk/assets/css/app.css',
    'https://portal.freesms.uk/assets/images/lad-logo-header.png',
    'https://portal.freesms.uk/admin/login',
    'https://portal.freesms.uk/contact',
    'https://portal.freesms.uk/nonexistent-test-404'
];

foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "{$url} -> HTTP {$code}\n";
}
