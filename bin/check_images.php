<?php
$imgs = glob(__DIR__ . '/../public/assets/images/*.{jpg,png}', GLOB_BRACE);
foreach ($imgs as $img) {
    $sz = getimagesize($img);
    printf("%-25s %10s %8d KB\n", basename($img), ($sz ? "{$sz[0]}x{$sz[1]}" : 'unknown'), round(filesize($img) / 1024));
}
