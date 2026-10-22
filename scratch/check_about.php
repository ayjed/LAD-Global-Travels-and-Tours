<?php
$c = curl_init('https://portal.freesms.uk/about');
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($c, CURLOPT_HEADER, true);
$r = curl_exec($c);
echo $r;
