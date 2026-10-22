<?php

$dir = __DIR__ . '/../app';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$files = [];
foreach ($rii as $file) {
    if ($file->isDir()) continue;
    $files[] = str_replace('\\', '/', $file->getPathname());
}

sort($files);
foreach ($files as $f) {
    $rel = substr($f, strlen(realpath(__DIR__ . '/..')) + 1);
    echo $rel . "\n";
}
