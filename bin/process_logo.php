<?php
declare(strict_types=1);

$source = 'C:/Users/DELL/.gemini/antigravity-ide/brain/b8bb87da-07f0-4432-bb01-ad0d97ac4db7/.user_uploaded/media_1792582339214.jpg';
$destDir = __DIR__ . '/../public/assets/images';

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

if (!file_exists($source)) {
    echo "Error: Source image not found at $source\n";
    exit(1);
}

$info = getimagesize($source);
echo "Source info: {$info[0]}x{$info[1]} {$info['mime']}\n";

// 1. Copy full original as logo.jpg
copy($source, $destDir . '/logo.jpg');
echo "Saved: public/assets/images/logo.jpg\n";

if (extension_loaded('gd')) {
    $img = imagecreatefromjpeg($source);
    if ($img !== false) {
        $w = imagesx($img);
        $h = imagesy($img);
        
        // 2. Save as high-res PNG
        imagepng($img, $destDir . '/logo.png', 8);
        echo "Saved: public/assets/images/logo.png\n";
        
        // 3. Create navbar/header optimized size (e.g., 200x200 or 120x120)
        $logoThumb = imagecreatetruecolor(160, 160);
        imagecopyresampled($logoThumb, $img, 0, 0, 0, 0, 160, 160, $w, $h);
        imagepng($logoThumb, $destDir . '/logo-160.png', 8);
        imagedestroy($logoThumb);
        echo "Saved: public/assets/images/logo-160.png\n";

        // 4. Create favicon (64x64 and 32x32)
        $fav64 = imagecreatetruecolor(64, 64);
        imagecopyresampled($fav64, $img, 0, 0, 0, 0, 64, 64, $w, $h);
        imagepng($fav64, dirname(__DIR__) . '/public/favicon.png', 9);
        imagedestroy($fav64);
        echo "Saved: public/favicon.png\n";
        
        $fav32 = imagecreatetruecolor(32, 32);
        imagecopyresampled($fav32, $img, 0, 0, 0, 0, 32, 32, $w, $h);
        imagepng($fav32, dirname(__DIR__) . '/public/favicon-32.png', 9);
        imagedestroy($fav32);
        echo "Saved: public/favicon-32.png\n";
        
        imagedestroy($img);
    }
}

echo "Logo processing completed successfully.\n";
