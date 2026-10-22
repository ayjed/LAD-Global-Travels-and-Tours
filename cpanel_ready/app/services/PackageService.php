<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;

/**
 * PackageService
 * Manages package data operations, slug generation, status transitions,
 * and deletion protection for historical booking records.
 */
class PackageService
{
    /**
     * Get all packages with optional filters.
     *
     * @param array $filters ['status' => 'published'|'draft'|'archived'|'all', 'search' => '', 'is_featured' => 1]
     * @return array
     */
    public static function getAll(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT p.*, 
                (SELECT COUNT(*) FROM bookings b WHERE b.package_id = p.id) as bookings_count 
                FROM packages p WHERE 1=1";
        $params = [];

        $status = $filters['status'] ?? 'all';
        if ($status !== 'all' && in_array($status, ['draft', 'published', 'archived'], true)) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.title LIKE :search1 OR p.destination_country LIKE :search2 OR p.destination_city LIKE :search3)";
            $searchTerm = '%' . trim((string)$filters['search']) . '%';
            $params[':search1'] = $searchTerm;
            $params[':search2'] = $searchTerm;
            $params[':search3'] = $searchTerm;
        }

        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $sql .= " AND p.is_featured = :is_featured";
            $params[':is_featured'] = (int)$filters['is_featured'];
        }

        $sql .= " ORDER BY p.is_featured DESC, p.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Find a package by ID.
     */
    public static function getById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT p.*, 
             (SELECT COUNT(*) FROM bookings b WHERE b.package_id = p.id) as bookings_count 
             FROM packages p WHERE p.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Find a package by slug.
     *
     * @param string $slug
     * @param bool $publishedOnly If true, only returns if status === 'published'
     * @return array|null
     */
    public static function getBySlug(string $slug, bool $publishedOnly = false): ?array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT p.* FROM packages p WHERE p.slug = :slug";
        if ($publishedOnly) {
            $sql .= " AND p.status = 'published'";
        }
        $sql .= " LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([':slug' => trim($slug)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Generate a URL-safe, unique slug from a package title.
     *
     * @param string $title
     * @param int|null $excludeId Exclude specific package ID when updating
     * @return string
     */
    public static function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        // Lowercase, replace non-alphanumeric with hyphen
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'package-' . bin2hex(random_bytes(3));
        }

        $pdo = Database::getConnection();
        $baseSlug = $slug;
        $counter = 1;

        while (true) {
            $sql = "SELECT COUNT(*) FROM packages WHERE slug = :slug";
            $params = [':slug' => $slug];

            if ($excludeId !== null) {
                $sql .= " AND id != :exclude_id";
                $params[':exclude_id'] = $excludeId;
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $count = (int)$stmt->fetchColumn();

            if ($count === 0) {
                return $slug;
            }

            $counter++;
            $slug = $baseSlug . '-' . $counter;
        }
    }

    /**
     * Create a new tour package.
     *
     * @param array $data
     * @return int Created package ID
     */
    public static function create(array $data): int
    {
        $title = trim($data['title'] ?? '');
        if ($title === '') {
            throw new RuntimeException("Package title is required.");
        }

        $destinationCountry = trim($data['destination_country'] ?? '');
        if ($destinationCountry === '') {
            throw new RuntimeException("Destination country is required.");
        }

        $summary = trim($data['summary'] ?? '');
        $description = trim($data['description'] ?? '');

        // Slug generation
        $slug = !empty($data['slug']) ? trim($data['slug']) : self::generateUniqueSlug($title);

        // Price & Currency
        $priceAmount = isset($data['price_amount']) && $data['price_amount'] !== '' 
            ? (float)$data['price_amount'] 
            : null;
        $priceCurrency = !empty($data['price_currency']) ? strtoupper(trim($data['price_currency'])) : 'NGN';
        $isPaymentEnabled = !empty($data['is_payment_enabled']) ? 1 : 0;

        if ($isPaymentEnabled === 1 && ($priceAmount === null || $priceAmount <= 0)) {
            throw new RuntimeException("Direct payment cannot be enabled on a package without a valid price greater than zero.");
        }

        $status = $data['status'] ?? 'draft';
        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            $status = 'draft';
        }

        $isFeatured = !empty($data['is_featured']) ? 1 : 0;
        $durationDays = !empty($data['duration_days']) ? (int)$data['duration_days'] : null;
        $startDate = !empty($data['travel_start_date']) ? $data['travel_start_date'] : null;
        $endDate = !empty($data['travel_end_date']) ? $data['travel_end_date'] : null;
        $capacity = !empty($data['capacity']) ? (int)$data['capacity'] : null;
        $bookingDeadline = !empty($data['booking_deadline']) ? $data['booking_deadline'] : null;

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO packages (
                title, slug, destination_country, destination_city,
                summary, description, cover_image, duration_days,
                travel_start_date, travel_end_date, price_amount, price_currency,
                inclusions, exclusions, itinerary, capacity, booking_deadline,
                is_payment_enabled, status, is_featured
            ) VALUES (
                :title, :slug, :country, :city,
                :summary, :description, :cover_image, :duration,
                :start_date, :end_date, :price_amount, :price_currency,
                :inclusions, :exclusions, :itinerary, :capacity, :booking_deadline,
                :is_payment_enabled, :status, :is_featured
            )"
        );

        $stmt->execute([
            ':title'              => $title,
            ':slug'               => $slug,
            ':country'            => $destinationCountry,
            ':city'               => !empty($data['destination_city']) ? trim($data['destination_city']) : null,
            ':summary'            => $summary,
            ':description'        => $description,
            ':cover_image'        => !empty($data['cover_image']) ? $data['cover_image'] : null,
            ':duration'           => $durationDays,
            ':start_date'         => $startDate,
            ':end_date'           => $endDate,
            ':price_amount'       => $priceAmount,
            ':price_currency'     => $priceCurrency,
            ':inclusions'         => !empty($data['inclusions']) ? trim($data['inclusions']) : null,
            ':exclusions'         => !empty($data['exclusions']) ? trim($data['exclusions']) : null,
            ':itinerary'          => !empty($data['itinerary']) ? trim($data['itinerary']) : null,
            ':capacity'           => $capacity,
            ':booking_deadline'   => $bookingDeadline,
            ':is_payment_enabled' => $isPaymentEnabled,
            ':status'             => $status,
            ':is_featured'        => $isFeatured
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Update an existing tour package.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update(int $id, array $data): bool
    {
        $existing = self::getById($id);
        if (!$existing) {
            throw new RuntimeException("Package with ID {$id} not found.");
        }

        $title = trim($data['title'] ?? $existing['title']);
        $destinationCountry = trim($data['destination_country'] ?? $existing['destination_country']);

        // Check if slug changed or needs generation
        $slug = !empty($data['slug']) 
            ? trim($data['slug']) 
            : ($title !== $existing['title'] ? self::generateUniqueSlug($title, $id) : $existing['slug']);

        $priceAmount = isset($data['price_amount']) && $data['price_amount'] !== '' 
            ? (float)$data['price_amount'] 
            : null;
        $priceCurrency = !empty($data['price_currency']) ? strtoupper(trim($data['price_currency'])) : 'NGN';
        $isPaymentEnabled = isset($data['is_payment_enabled']) ? (int)(bool)$data['is_payment_enabled'] : (int)$existing['is_payment_enabled'];

        if ($isPaymentEnabled === 1 && ($priceAmount === null || $priceAmount <= 0)) {
            throw new RuntimeException("Direct payment cannot be enabled on a package without a valid price greater than zero.");
        }

        $status = $data['status'] ?? $existing['status'];
        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            $status = $existing['status'];
        }

        $summary = array_key_exists('summary', $data) ? trim((string)$data['summary']) : $existing['summary'];
        $description = array_key_exists('description', $data) ? trim((string)$data['description']) : $existing['description'];
        $coverImage = !empty($data['cover_image']) ? $data['cover_image'] : $existing['cover_image'];
        $duration = array_key_exists('duration_days', $data) ? (!empty($data['duration_days']) ? (int)$data['duration_days'] : null) : $existing['duration_days'];
        $startDate = array_key_exists('travel_start_date', $data) ? (!empty($data['travel_start_date']) ? $data['travel_start_date'] : null) : $existing['travel_start_date'];
        $endDate = array_key_exists('travel_end_date', $data) ? (!empty($data['travel_end_date']) ? $data['travel_end_date'] : null) : $existing['travel_end_date'];
        $inclusions = array_key_exists('inclusions', $data) ? (!empty($data['inclusions']) ? trim($data['inclusions']) : null) : $existing['inclusions'];
        $exclusions = array_key_exists('exclusions', $data) ? (!empty($data['exclusions']) ? trim($data['exclusions']) : null) : $existing['exclusions'];
        $itinerary = array_key_exists('itinerary', $data) ? (!empty($data['itinerary']) ? trim($data['itinerary']) : null) : $existing['itinerary'];
        $capacity = array_key_exists('capacity', $data) ? (!empty($data['capacity']) ? (int)$data['capacity'] : null) : $existing['capacity'];
        $bookingDeadline = array_key_exists('booking_deadline', $data) ? (!empty($data['booking_deadline']) ? $data['booking_deadline'] : null) : $existing['booking_deadline'];
        $isFeatured = array_key_exists('is_featured', $data) ? (!empty($data['is_featured']) ? 1 : 0) : (int)$existing['is_featured'];

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE packages SET
                title = :title,
                slug = :slug,
                destination_country = :country,
                destination_city = :city,
                summary = :summary,
                description = :description,
                cover_image = :cover_image,
                duration_days = :duration,
                travel_start_date = :start_date,
                travel_end_date = :end_date,
                price_amount = :price_amount,
                price_currency = :price_currency,
                inclusions = :inclusions,
                exclusions = :exclusions,
                itinerary = :itinerary,
                capacity = :capacity,
                booking_deadline = :booking_deadline,
                is_payment_enabled = :is_payment_enabled,
                status = :status,
                is_featured = :is_featured
            WHERE id = :id"
        );

        return $stmt->execute([
            ':id'                 => $id,
            ':title'              => $title,
            ':slug'               => $slug,
            ':country'            => $destinationCountry,
            ':city'               => !empty($data['destination_city']) ? trim($data['destination_city']) : $existing['destination_city'],
            ':summary'            => $summary,
            ':description'        => $description,
            ':cover_image'        => $coverImage,
            ':duration'           => $duration,
            ':start_date'         => $startDate,
            ':end_date'           => $endDate,
            ':price_amount'       => $priceAmount !== null ? $priceAmount : $existing['price_amount'],
            ':price_currency'     => $priceCurrency,
            ':inclusions'         => $inclusions,
            ':exclusions'         => $exclusions,
            ':itinerary'          => $itinerary,
            ':capacity'           => $capacity,
            ':booking_deadline'   => $bookingDeadline,
            ':is_payment_enabled' => $isPaymentEnabled,
            ':status'             => $status,
            ':is_featured'        => $isFeatured
        ]);
    }

    /**
     * Transition package status ('draft', 'published', 'archived').
     */
    public static function setStatus(int $id, string $status): bool
    {
        if (!in_array($status, ['draft', 'published', 'archived'], true)) {
            throw new RuntimeException("Invalid package status: {$status}");
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE packages SET status = :status WHERE id = :id");
        return $stmt->execute([':status' => $status, ':id' => $id]);
    }

    /**
     * Check if a package is tied to existing booking or payment records.
     */
    public static function hasBookings(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE package_id = :id");
        $stmt->execute([':id' => $id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Safely delete a package.
     * Enforces SOT requirement: Protects historical paid/booked packages from destructive deletion.
     *
     * @throws RuntimeException If package has linked bookings
     */
    public static function delete(int $id): bool
    {
        if (self::hasBookings($id)) {
            throw new RuntimeException(
                "Cannot delete this package because it is linked to historical booking and payment records. You must archive it instead."
            );
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM packages WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Fetch secondary gallery images for a package.
     */
    public static function getGalleryImages(int $packageId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM package_images WHERE package_id = :id ORDER BY display_order ASC, id ASC"
        );
        $stmt->execute([':id' => $packageId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add a gallery image to a package.
     */
    public static function addGalleryImage(int $packageId, string $imagePath, ?string $altText = null, int $order = 0): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO package_images (package_id, image_path, alt_text, display_order) 
             VALUES (:pkg_id, :path, :alt, :order)"
        );
        $stmt->execute([
            ':pkg_id' => $packageId,
            ':path'   => $imagePath,
            ':alt'    => $altText,
            ':order'  => $order
        ]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Delete a single gallery image.
     */
    public static function deleteGalleryImage(int $imageId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM package_images WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $imageId]);
        $img = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$img) {
            return false;
        }

        // Remove file if stored in /uploads/
        if (!empty($img['image_path']) && str_starts_with($img['image_path'], '/uploads/')) {
            $fullPath = dirname(__DIR__, 2) . '/public' . $img['image_path'];
            if (file_exists($fullPath) && is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        $delStmt = $pdo->prepare("DELETE FROM package_images WHERE id = :id");
        return $delStmt->execute([':id' => $imageId]);
    }
}
