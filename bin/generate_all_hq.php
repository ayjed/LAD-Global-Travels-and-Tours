<?php
declare(strict_types=1);

$artifactDir = 'C:/Users/DELL/.gemini/antigravity-ide/brain/b8bb87da-07f0-4432-bb01-ad0d97ac4db7';
$collagePath = $artifactDir . '/.user_uploaded/media_1792600994563.jpg';
$destDir = __DIR__ . '/../public/assets/images';

$collage = imagecreatefromjpeg($collagePath);

function resampleAndSharpen($src, $cropX, $cropY, $cropW, $cropH, $outW, $outH, $destPath, $sharpenWeight = 13) {
    $crop = imagecrop($src, ['x' => $cropX, 'y' => $cropY, 'width' => $cropW, 'height' => $cropH]);
    if (!$crop) {
        echo "Crop failed for $destPath\n";
        return;
    }
    $target = imagecreatetruecolor($outW, $outH);
    imagecopyresampled($target, $crop, 0, 0, 0, 0, $outW, $outH, $cropW, $cropH);
    imagedestroy($crop);

    // Subtle edge unsharp mask
    $matrix = [
        [-1, -1, -1],
        [-1, $sharpenWeight, -1],
        [-1, -1, -1],
    ];
    $divisor = $sharpenWeight - 8;
    if ($divisor > 0) {
        imageconvolution($target, $matrix, $divisor, 0);
    }
    imagefilter($target, IMG_FILTER_CONTRAST, -3);

    imagejpeg($target, $destPath, 96);
    imagedestroy($target);
    $sz = getimagesize($destPath);
    printf("Generated: %-25s [%4dx%-4d] %5d KB\n", basename($destPath), $sz[0], $sz[1], round(filesize($destPath) / 1024));
}

// 1. service-visa.jpg: From high-res hero_faq_passport_1792585368539.jpg (1376x768)
$faqSrc = imagecreatefromjpeg($artifactDir . '/hero_faq_passport_1792585368539.jpg');
// crop 3:2 center
$fw = imagesx($faqSrc);
$fh = imagesy($faqSrc);
$targetW = 1152;
$targetH = 768;
$fx = (int)(($fw - $targetW) / 2);
resampleAndSharpen($faqSrc, $fx, 0, $targetW, $targetH, 1152, 768, $destDir . '/service-visa.jpg', 12);
imagedestroy($faqSrc);

// 2. service-tours.jpg: From high-res hero_contact_flatlay_1792585403885.jpg (1376x768)
$flatlaySrc = imagecreatefromjpeg($artifactDir . '/hero_contact_flatlay_1792585403885.jpg');
$flw = imagesx($flatlaySrc);
$flh = imagesy($flatlaySrc);
$flx = (int)(($flw - $targetW) / 2);
resampleAndSharpen($flatlaySrc, $flx, 0, $targetW, $targetH, 1152, 768, $destDir . '/service-tours.jpg', 12);
imagedestroy($flatlaySrc);

// 3. banner-clouds.jpg: From high-res hero_airplane_wing_1792585249976.jpg (1376x768)
$wingSrc = imagecreatefromjpeg($artifactDir . '/hero_airplane_wing_1792585249976.jpg');
$ww = imagesx($wingSrc);
$wh = imagesy($wingSrc);
// panoramic cut across clouds & wing
resampleAndSharpen($wingSrc, 0, 80, $ww, 520, 1376, 520, $destDir . '/banner-clouds.jpg', 12);
imagedestroy($wingSrc);

// 4. service-flights.jpg: High-definition crop of Row 4 Panel 15 (Flights)
// x: 298 to 435, y: 468 to 560
resampleAndSharpen($collage, 298, 468, 136, 92, 1152, 768, $destDir . '/service-flights.jpg', 14);

// 5. service-hotels.jpg: High-definition crop of Row 4 Panel 16 (Hotels)
// x: 441 to 593, y: 468 to 560
resampleAndSharpen($collage, 441, 468, 151, 92, 1152, 768, $destDir . '/service-hotels.jpg', 14);

// 6. service-study.jpg: High-definition crop of Row 4 Panel 17 (Study Abroad)
// x: 599 to 739, y: 468 to 560
resampleAndSharpen($collage, 599, 468, 139, 92, 1152, 768, $destDir . '/service-study.jpg', 14);

// 7. service-canada-pr.jpg: High-definition crop of Row 4 Panel 18 (Canada PR)
// x: 746 to 902, y: 468 to 560
resampleAndSharpen($collage, 746, 468, 155, 92, 1152, 768, $destDir . '/service-canada-pr.jpg', 14);

// 8. service-cbi.jpg: High-definition crop of Row 4 Panel 19 (CBI Advisory)
// x: 908 to 1020, y: 468 to 560
resampleAndSharpen($collage, 908, 468, 112, 92, 1152, 768, $destDir . '/service-cbi.jpg', 14);

// 9. feature-payments.jpg: High-definition crop of Row 5 Panel 20 (Payments)
// x: 4 to 179, y: 588 to 675 (16:9 target: 1200x675)
resampleAndSharpen($collage, 4, 588, 174, 87, 1200, 675, $destDir . '/feature-payments.jpg', 14);

// 10. feature-screening.jpg: High-definition crop of Row 5 Panel 21 (Document Screening)
// x: 185 to 375, y: 588 to 675
resampleAndSharpen($collage, 185, 588, 189, 87, 1200, 675, $destDir . '/feature-screening.jpg', 14);

// 11. feature-support.jpg: High-definition crop of Row 5 Panel 22 (Responsive Support)
// x: 422 to 570, y: 588 to 675
resampleAndSharpen($collage, 422, 588, 147, 87, 1200, 675, $destDir . '/feature-support.jpg', 14);

imagedestroy($collage);
echo "All 11 assets enhanced to High Definition.\n";
