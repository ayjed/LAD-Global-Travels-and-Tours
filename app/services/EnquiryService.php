<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use RuntimeException;
use Throwable;

/**
 * EnquiryService
 * Manages customer consultation enquiries, service/package associations,
 * internal admin consultation notes, and status lifecycles.
 */
class EnquiryService
{
    /**
     * Allowed enquiry statuses.
     */
    public const VALID_STATUSES = ['new', 'contacted', 'quoted', 'closed', 'cancelled'];

    /**
     * Create and persist a new customer enquiry.
     *
     * @param array $data Input form data
     * @return array ['id' => int, 'reference' => string]
     * @throws RuntimeException If required fields are missing or invalid
     */
    public static function create(array $data): array
    {
        $fullName = trim($data['full_name'] ?? '');
        if ($fullName === '' || strlen($fullName) < 3) {
            throw new RuntimeException("Full name is required (minimum 3 characters).");
        }

        $email = strtolower(trim($data['email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("A valid email address is required.");
        }

        $phone = trim($data['phone'] ?? '');
        if ($phone === '' || strlen($phone) < 7) {
            throw new RuntimeException("A valid contact telephone or WhatsApp number is required.");
        }

        $serviceType = trim($data['service_type'] ?? 'other');
        $message = trim($data['message'] ?? '');
        if ($message === '' || strlen($message) < 5) {
            throw new RuntimeException("Please provide a message or describe your travel inquiry.");
        }

        // Generate unique reference (e.g. ENQ-A1B2C3D4)
        $pdo = Database::getConnection();
        $reference = self::generateUniqueReference($pdo);

        // Resolve optional package association
        $packageId = null;
        if (!empty($data['package_id'])) {
            $packageId = (int)$data['package_id'];
        } elseif (!empty($data['package_slug'])) {
            $stmt = $pdo->prepare("SELECT id FROM packages WHERE slug = :slug LIMIT 1");
            $stmt->execute([':slug' => trim($data['package_slug'])]);
            $pkgRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($pkgRow) {
                $packageId = (int)$pkgRow['id'];
            }
        }

        $destination = !empty($data['destination']) ? trim($data['destination']) : null;
        $preferredDate = !empty($data['preferred_date']) ? $data['preferred_date'] : null;
        $travellersCount = !empty($data['travellers_count']) ? max(1, (int)$data['travellers_count']) : null;

        $stmt = $pdo->prepare(
            "INSERT INTO enquiries (
                reference, full_name, email, phone, service_type,
                package_id, destination, preferred_date, travellers_count,
                message, status
            ) VALUES (
                :ref, :name, :email, :phone, :service,
                :package_id, :destination, :preferred_date, :travellers,
                :message, 'new'
            )"
        );

        $stmt->execute([
            ':ref'            => $reference,
            ':name'           => $fullName,
            ':email'          => $email,
            ':phone'          => $phone,
            ':service'        => $serviceType,
            ':package_id'     => $packageId,
            ':destination'    => $destination,
            ':preferred_date' => $preferredDate,
            ':travellers'     => $travellersCount,
            ':message'        => $message
        ]);

        $enquiryId = (int)$pdo->lastInsertId();

        // Best-effort transactional email alert & receipt (never throws)
        try {
            $enquiryRecord = self::getById($enquiryId);
            if ($enquiryRecord) {
                MailService::sendEnquiryAdminAlert($enquiryRecord);
                MailService::sendEnquiryCustomerReceipt($enquiryRecord);
            }
        } catch (Throwable $e) {
            error_log('[EnquiryService] Notification dispatch caught error: ' . $e->getMessage());
        }

        return [
            'id' => $enquiryId,
            'reference' => $reference
        ];
    }

    /**
     * Retrieve all enquiries with optional filtering.
     *
     * @param array $filters ['status' => 'new'|'contacted'|..., 'service_type' => '...', 'search' => '...']
     * @return array
     */
    public static function getAll(array $filters = []): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT e.*, 
                pkg.title as package_title, pkg.slug as package_slug,
                (SELECT COUNT(*) FROM enquiry_notes n WHERE n.enquiry_id = e.id) as notes_count
                FROM enquiries e
                LEFT JOIN packages pkg ON e.package_id = pkg.id
                WHERE 1=1";
        $params = [];

        $status = $filters['status'] ?? 'all';
        if ($status !== 'all' && in_array($status, self::VALID_STATUSES, true)) {
            $sql .= " AND e.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($filters['service_type']) && $filters['service_type'] !== 'all') {
            $sql .= " AND e.service_type = :service_type";
            $params[':service_type'] = $filters['service_type'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (e.reference LIKE :search1 OR e.full_name LIKE :search2 OR e.email LIKE :search3 OR e.phone LIKE :search4 OR e.destination LIKE :search5)";
            $term = '%' . trim((string)$filters['search']) . '%';
            $params[':search1'] = $term;
            $params[':search2'] = $term;
            $params[':search3'] = $term;
            $params[':search4'] = $term;
            $params[':search5'] = $term;
        }

        $sql .= " ORDER BY e.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch an enquiry by ID with package details and notes count.
     */
    public static function getById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT e.*, 
             pkg.title as package_title, pkg.slug as package_slug,
             (SELECT COUNT(*) FROM enquiry_notes n WHERE n.enquiry_id = e.id) as notes_count
             FROM enquiries e
             LEFT JOIN packages pkg ON e.package_id = pkg.id
             WHERE e.id = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Fetch an enquiry by reference code.
     */
    public static function getByReference(string $reference): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT e.*, pkg.title as package_title 
             FROM enquiries e
             LEFT JOIN packages pkg ON e.package_id = pkg.id
             WHERE e.reference = :ref LIMIT 1"
        );
        $stmt->execute([':ref' => trim($reference)]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Update enquiry status and optional follow-up date.
     *
     * @param int $id
     * @param string $status ('new', 'contacted', 'quoted', 'closed', 'cancelled')
     * @param string|null $followUpDate (YYYY-MM-DD or null)
     * @return bool
     */
    public static function updateStatus(int $id, string $status, ?string $followUpDate = null): bool
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new RuntimeException("Invalid enquiry status: {$status}");
        }

        $followUp = (!empty($followUpDate) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $followUpDate))
            ? $followUpDate
            : null;

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE enquiries SET status = :status, follow_up_date = :follow_up WHERE id = :id"
        );
        return $stmt->execute([
            ':status'    => $status,
            ':follow_up' => $followUp,
            ':id'        => $id
        ]);
    }

    /**
     * Add an internal consultation note to an enquiry.
     *
     * @param int $enquiryId
     * @param int $adminId Authenticated admin ID
     * @param string $noteText Note content
     * @return int Inserted note ID
     */
    public static function addNote(int $enquiryId, int $adminId, string $noteText): int
    {
        $note = trim($noteText);
        if ($note === '') {
            throw new RuntimeException("Note content cannot be empty.");
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO enquiry_notes (enquiry_id, admin_id, note) VALUES (:enquiry_id, :admin_id, :note)"
        );
        $stmt->execute([
            ':enquiry_id' => $enquiryId,
            ':admin_id'   => $adminId,
            ':note'       => $note
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Fetch all internal notes for a specific enquiry with author username.
     */
    public static function getNotes(int $enquiryId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT n.*, a.username as admin_username
             FROM enquiry_notes n
             JOIN admins a ON n.admin_id = a.id
             WHERE n.enquiry_id = :enquiry_id
             ORDER BY n.created_at ASC"
        );
        $stmt->execute([':enquiry_id' => $enquiryId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Aggregate counts by status for admin dashboard and filter tabs.
     */
    public static function getCounts(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query(
            "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as status_new,
                SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as status_contacted,
                SUM(CASE WHEN status = 'quoted' THEN 1 ELSE 0 END) as status_quoted,
                SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as status_closed,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as status_cancelled,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today
             FROM enquiries"
        );
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return [
            'total'     => (int)($row['total'] ?? 0),
            'new'       => (int)($row['status_new'] ?? 0),
            'contacted' => (int)($row['status_contacted'] ?? 0),
            'quoted'    => (int)($row['status_quoted'] ?? 0),
            'closed'    => (int)($row['status_closed'] ?? 0),
            'cancelled' => (int)($row['status_cancelled'] ?? 0),
            'today'     => (int)($row['today'] ?? 0)
        ];
    }

    /**
     * Generate unique reference code with collision check.
     */
    private static function generateUniqueReference(PDO $pdo): string
    {
        while (true) {
            $ref = 'ENQ-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM enquiries WHERE reference = :ref");
            $stmt->execute([':ref' => $ref]);
            if ((int)$stmt->fetchColumn() === 0) {
                return $ref;
            }
        }
    }
}
