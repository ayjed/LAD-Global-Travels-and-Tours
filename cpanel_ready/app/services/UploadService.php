<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/**
 * UploadService
 * Handles safe file uploads with allow-list MIME verification via finfo,
 * file size enforcement, random filename generation, and path traversal protection.
 */
class UploadService
{
    /**
     * Allowed MIME types and their canonical extensions.
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png'  => ['png'],
        'image/webp' => ['webp']
    ];

    /**
     * Maximum allowed upload size (5 MB).
     */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /**
     * Upload an image file securely to a destination subfolder within public/uploads/.
     *
     * @param array $file The $_FILES entry (e.g. $_FILES['cover_image'])
     * @param string $subfolder Subdirectory under uploads (e.g. 'packages')
     * @return string|null The relative public path (e.g. '/uploads/packages/xxxx.jpg') or null if empty
     * @throws RuntimeException If validation fails
     */
    public static function uploadImage(array $file, string $subfolder = 'packages'): ?string
    {
        // 1. Check if a file was actually provided
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        // 2. Check for standard PHP upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException("Upload failed with error code: {$file['error']}");
        }

        // 3. Enforce file size limit
        if (!isset($file['size']) || $file['size'] > self::MAX_FILE_SIZE) {
            throw new RuntimeException("The uploaded file exceeds the 5 MB maximum size limit.");
        }

        // 4. Validate temporary file exists and is uploaded
        $tmpPath = $file['tmp_name'] ?? '';
        if (!is_uploaded_file($tmpPath)) {
            throw new RuntimeException("Invalid upload source: temporary file not found.");
        }

        // 5. Inspect MIME type using fileinfo (detects true binary signature)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if (!$finfo) {
            throw new RuntimeException("Server error: Unable to initialize fileinfo inspection.");
        }
        $mimeType = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        if (!array_key_exists($mimeType, self::ALLOWED_MIME_TYPES)) {
            throw new RuntimeException("Invalid file type ({$mimeType}). Only JPG, PNG, and WebP images are allowed.");
        }

        // 6. Verify client file extension matches permitted MIME types
        $originalExt = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $permittedExts = self::ALLOWED_MIME_TYPES[$mimeType];

        if (!in_array($originalExt, $permittedExts, true)) {
            // Default to canonical extension for the detected MIME type
            $extension = $permittedExts[0];
        } else {
            $extension = $originalExt === 'jpeg' ? 'jpg' : $originalExt;
        }

        // 7. Sanitize subfolder to prevent path traversal
        $cleanSubfolder = trim(str_replace(['..', '/', '\\'], '', $subfolder));
        if ($cleanSubfolder === '') {
            $cleanSubfolder = 'packages';
        }

        $targetDir = dirname(__DIR__, 2) . '/public/uploads/' . $cleanSubfolder;
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                throw new RuntimeException("Failed to create upload destination directory.");
            }
        }

        // 8. Generate cryptographically random filename
        $randomFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destinationPath = $targetDir . '/' . $randomFilename;

        // 9. Move uploaded file
        if (!move_uploaded_file($tmpPath, $destinationPath)) {
            throw new RuntimeException("Failed to save uploaded file to final destination.");
        }

        // Ensure permissions are safe (read/write for owner, read for web server)
        @chmod($destinationPath, 0644);

        return '/uploads/' . $cleanSubfolder . '/' . $randomFilename;
    }

    /**
     * Upload multiple images from a multiple file input (e.g. $_FILES['gallery_images']).
     *
     * @param array $filesEntry The $_FILES['gallery_images'] array
     * @param string $subfolder
     * @return array Array of relative public paths
     */
    public static function uploadMultipleImages(array $filesEntry, string $subfolder = 'packages'): array
    {
        $uploaded = [];
        if (empty($filesEntry['name']) || !is_array($filesEntry['name'])) {
            return $uploaded;
        }

        $count = count($filesEntry['name']);
        for ($i = 0; $i < $count; $i++) {
            if (($filesEntry['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $single = [
                'name'     => $filesEntry['name'][$i] ?? '',
                'type'     => $filesEntry['type'][$i] ?? '',
                'tmp_name' => $filesEntry['tmp_name'][$i] ?? '',
                'error'    => $filesEntry['error'][$i] ?? UPLOAD_ERR_OK,
                'size'     => $filesEntry['size'][$i] ?? 0,
            ];

            $path = self::uploadImage($single, $subfolder);
            if ($path !== null) {
                $uploaded[] = $path;
            }
        }

        return $uploaded;
    }
}
