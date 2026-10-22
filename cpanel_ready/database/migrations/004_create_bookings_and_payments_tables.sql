-- Migration: 004_create_bookings_and_payments_tables.sql
-- Description: Creates package bookings and payments reconciliation tables.
-- SOT Reference: LG-SOT-001 Section 8 & 9

CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(50) NOT NULL UNIQUE,
    package_id INT UNSIGNED NOT NULL,
    customer_name VARCHAR(150) NOT NULL,
    customer_email VARCHAR(191) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    travellers_count INT UNSIGNED NOT NULL DEFAULT 1,
    unit_price DECIMAL(12, 2) NOT NULL,
    total_amount DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'NGN',
    booking_status ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_bookings_ref (reference),
    INDEX idx_bookings_status (booking_status),
    INDEX idx_bookings_created (created_at),
    CONSTRAINT fk_bookings_package
        FOREIGN KEY (package_id) REFERENCES packages(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL,
    payment_reference VARCHAR(100) NOT NULL UNIQUE,
    provider ENUM('manual', 'paystack') NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(3) NOT NULL DEFAULT 'NGN',
    status ENUM('pending', 'paid', 'failed', 'rejected') NOT NULL DEFAULT 'pending',
    provider_transaction_id VARCHAR(191) NULL,
    verified_at DATETIME NULL,
    verified_by_admin_id INT UNSIGNED NULL,
    admin_audit_notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_payments_status (status),
    INDEX idx_payments_ref (payment_reference),
    INDEX idx_payments_provider (provider),
    CONSTRAINT fk_payments_booking
        FOREIGN KEY (booking_id) REFERENCES bookings(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_payments_admin
        FOREIGN KEY (verified_by_admin_id) REFERENCES admins(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback Instructions:
-- DROP TABLE IF EXISTS payments;
-- DROP TABLE IF EXISTS bookings;
