-- Migration: 003_create_enquiries_and_notes_tables.sql
-- Description: Creates customer enquiries and internal admin consultation notes tables.
-- SOT Reference: LG-SOT-001 Section 7 & 9

CREATE TABLE IF NOT EXISTS enquiries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(50) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    service_type VARCHAR(50) NOT NULL,
    package_id INT UNSIGNED NULL,
    destination VARCHAR(100) NULL,
    preferred_date DATE NULL,
    travellers_count INT UNSIGNED NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'contacted', 'quoted', 'closed', 'cancelled') NOT NULL DEFAULT 'new',
    follow_up_date DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_enquiries_status (status),
    INDEX idx_enquiries_service (service_type),
    INDEX idx_enquiries_created (created_at),
    INDEX idx_enquiries_reference (reference),
    CONSTRAINT fk_enquiries_package
        FOREIGN KEY (package_id) REFERENCES packages(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS enquiry_notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enquiry_id INT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED NOT NULL,
    note TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_enquiry_notes_enquiry (enquiry_id),
    CONSTRAINT fk_enquiry_notes_enquiry
        FOREIGN KEY (enquiry_id) REFERENCES enquiries(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_enquiry_notes_admin
        FOREIGN KEY (admin_id) REFERENCES admins(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback Instructions:
-- DROP TABLE IF EXISTS enquiry_notes;
-- DROP TABLE IF EXISTS enquiries;
