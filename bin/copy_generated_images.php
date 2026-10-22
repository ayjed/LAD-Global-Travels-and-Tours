<?php
declare(strict_types=1);

$artifactDir = 'C:/Users/DELL/.gemini/antigravity-ide/brain/b8bb87da-07f0-4432-bb01-ad0d97ac4db7';
$destDir = __DIR__ . '/../public/assets/images';

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

$mapping = [
    'hero-home.jpg' => 'hero_airplane_wing_*.jpg',
    'hero-about.jpg' => 'hero_about_traveler_*.jpg',
    'about-story.jpg' => 'about_airport_story_*.jpg',
    'hero-packages.jpg' => 'hero_packages_coast_*.jpg',
    'hero-faq.jpg' => 'hero_faq_passport_*.jpg',
    'hero-contact.jpg' => 'hero_contact_flatlay_*.jpg',
    'pkg-kenya.jpg' => 'pkg_kenya_safari_*.jpg',
    'pkg-dubai.jpg' => 'pkg_dubai_skyline_*.jpg',
    'pkg-rwanda.jpg' => 'pkg_rwanda_gorilla_*.jpg',
    'pkg-ghana.jpg' => 'pkg_ghana_coast_*.jpg',
];

foreach ($mapping as $target => $pattern) {
    $matches = glob($artifactDir . '/' . $pattern);
    if (!empty($matches)) {
        // take latest match
        sort($matches);
        $source = end($matches);
        copy($source, $destDir . '/' . $target);
        echo "Copied $source -> $destDir/$target\n";
    } else {
        echo "Warning: no matches for $pattern\n";
    }
}

echo "Image transfer complete.\n";
