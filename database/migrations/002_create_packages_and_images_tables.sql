-- Migration: 002_create_packages_and_images_tables.sql
-- Description: Creates tour packages and secondary gallery images tables.
-- SOT Reference: LG-SOT-001 Section 6 & 9

CREATE TABLE IF NOT EXISTS packages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(191) NOT NULL UNIQUE,
    destination_country VARCHAR(100) NOT NULL,
    destination_city VARCHAR(100) NULL,
    summary TEXT NOT NULL,
    description LONGTEXT NOT NULL,
    cover_image VARCHAR(255) NULL,
    duration_days INT UNSIGNED NULL,
    travel_start_date DATE NULL,
    travel_end_date DATE NULL,
    price_amount DECIMAL(12, 2) NULL,
    price_currency VARCHAR(3) NOT NULL DEFAULT 'NGN',
    inclusions TEXT NULL,
    exclusions TEXT NULL,
    itinerary LONGTEXT NULL,
    capacity INT UNSIGNED NULL,
    booking_deadline DATE NULL,
    is_payment_enabled TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_packages_status (status),
    INDEX idx_packages_featured (is_featured),
    INDEX idx_packages_destination (destination_country),
    INDEX idx_packages_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS package_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    package_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    alt_text VARCHAR(255) NULL,
    display_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pkg_images_pkg_id (package_id),
    CONSTRAINT fk_pkg_images_package
        FOREIGN KEY (package_id) REFERENCES packages(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback Instructions:
-- DROP TABLE IF EXISTS package_images;
-- DROP TABLE IF EXISTS packages;
