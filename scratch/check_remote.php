<?php

$urls = [
    'https://portal.freesms.uk/test.php',
    'https://portal.freesms.uk/phpinfo.php',
    'https://portal.freesms.uk/lad_global_production.sql',
    'https://portal.freesms.uk/CPANEL_DEPLOYMENT_INSTRUCTIONS.md',
    'https://portal.freesms.uk/public/',
    'https://portal.freesms.uk/public/index.php',
    'https://portal.freesms.uk/public/assets/css/app.css',
];

foreach ($urls as $u) {
    $c = curl_init($u);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_HEADER, true);
    curl_setopt($c, CURLOPT_TIMEOUT, 6);
    $r = curl_exec($c);
    $code = curl_getinfo($c, CURLINFO_HTTP_CODE);
    echo "$u => $code\n";
}
