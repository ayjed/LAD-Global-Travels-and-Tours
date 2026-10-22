-- =============================================================================
-- LAD GLOBAL TRAVELS AND TOURS — PRODUCTION DATABASE EXPORT
-- Generated: 2026-10-22 15:49:22
-- Target: MySQL 8.0+ / MariaDB 10.4+ (utf8mb4_unicode_ci)
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
SET time_zone = '+00:00';

-- -----------------------------------------------------------------------------
-- Table Structure: 001_create_admins_table.sql
-- -----------------------------------------------------------------------------
-- Migration: 001_create_admins_table.sql
-- Description: Creates the administrators table for the single authorized admin account.
-- SOT Reference: LG-SOT-001 Section 9

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(191) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0,
    locked_until DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_admins_username (username),
    INDEX idx_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback Instructions:
-- DROP TABLE IF EXISTS admins;

-- -----------------------------------------------------------------------------
-- Table Structure: 002_create_packages_and_images_tables.sql
-- -----------------------------------------------------------------------------
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

-- -----------------------------------------------------------------------------
-- Table Structure: 003_create_enquiries_and_notes_tables.sql
-- -----------------------------------------------------------------------------
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

-- -----------------------------------------------------------------------------
-- Table Structure: 004_create_bookings_and_payments_tables.sql
-- -----------------------------------------------------------------------------
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

-- -----------------------------------------------------------------------------
-- Table Structure: 005_create_settings_and_audit_tables.sql
-- -----------------------------------------------------------------------------
-- Migration: 005_create_settings_and_audit_tables.sql
-- Description: Creates application settings, payment webhook deduplication events, and audit logs.
-- SOT Reference: LG-SOT-001 Section 8.3 & 9

CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_events (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id VARCHAR(191) NOT NULL UNIQUE,
    provider VARCHAR(50) NOT NULL DEFAULT 'paystack',
    event_type VARCHAR(100) NOT NULL,
    payload LONGTEXT NOT NULL,
    processed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_events_event_id (event_id),
    INDEX idx_events_provider (provider)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT UNSIGNED NULL,
    details TEXT NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_admin (admin_id),
    INDEX idx_audit_action (action),
    INDEX idx_audit_created (created_at),
    CONSTRAINT fk_audit_admin
        FOREIGN KEY (admin_id) REFERENCES admins(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback Instructions:
-- DROP TABLE IF EXISTS audit_logs;
-- DROP TABLE IF EXISTS payment_events;
-- DROP TABLE IF EXISTS settings;

-- -----------------------------------------------------------------------------
-- System & Organization Settings
-- -----------------------------------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('company_tagline', 'Explore • Discover • Experience', NOW()),
('bank_name', 'Zenith Bank PLC', NOW()),
('bank_account_name', 'LAD Global Travels and Tours Limited', NOW()),
('bank_account_number', '1017845343', NOW()),
('bank_sort_code', '057', NOW()),
('bank_transfer_instructions', 'Mandatory: Use BK-XXXX as transfer narration.', NOW()),
('bank_verified_by_client', '1', NOW()),
('bank_verified_at', '2026-10-22 14:22:15', NOW()),
('mail_from_address', 'info@ladglobal.com', NOW()),
('mail_from_name', 'LAD Global Travels', NOW()),
('notification_email_enquiries', 'sales@ladglobal.com', NOW()),
('notification_email_visa', 'visa@ladglobal.com', NOW()),
('notification_email_payments', 'admin@ladglobal.com', NOW()),
('notification_email_management', 'lanre@ladglobal.com', NOW()),
('payment_method_bank_enabled', '1', NOW()),
('payment_method_paystack_enabled', '0', NOW())
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`), `updated_at` = NOW();

-- -----------------------------------------------------------------------------
-- Tour Packages
-- -----------------------------------------------------------------------------
INSERT INTO `packages` (`id`, `title`, `slug`, `destination_country`, `destination_city`, `summary`, `description`, `cover_image`, `duration_days`, `travel_start_date`, `travel_end_date`, `price_amount`, `price_currency`, `inclusions`, `exclusions`, `itinerary`, `capacity`, `booking_deadline`, `is_payment_enabled`, `status`, `is_featured`, `created_at`, `updated_at`) VALUES ('62', 'Kenya Safari & Diani Beach Escape', 'kenya-safari-experience', 'Kenya', 'Nairobi & Diani', 'Experience game drives in the Maasai Mara savannah followed by pure coastal relaxation on the white sands of Diani Beach.', 'Embark on an iconic East African adventure combining world-class wildlife safaris with tropical relaxation. Begin in Nairobi with an introduction to Kenyan heritage, then fly directly into the legendary Maasai Mara National Reserve for morning and evening game drives in search of the Big Five.\r\n\r\nAfter exhilarating days in the wild, journey to the pristine shores of Diani Beach on the Indian Ocean. Unwind at a beachfront resort with watersports, seafood dining, and tropical sunsets.', '/assets/images/pkg-kenya.jpg', '7', NULL, NULL, '1850000.00', 'NGN', '- 3 Nights luxury safari lodge accommodation in Maasai Mara\r\n- 3 Nights beachfront resort accommodation in Diani Beach\r\n- Full board meals during safari; breakfast & dinner in Diani\r\n- All scheduled game drives in 4x4 safari land cruisers with pop-up roofs\r\n- English-speaking professional naturalist safari guide\r\n- Domestic safari flights and airport transfers', '- International flight tickets (consultant booking available upon request)\r\n- Kenya electronic travel authorization (eTA) processing fee\r\n- Personal expenses, laundry, and discretionary tips\r\n- Optional hot air balloon safari experience', 'Day 1: Arrival in Nairobi & City Orientation\r\nDay 2: Flight to Maasai Mara & Sunset Game Drive\r\nDay 3: Full Day Big Five Safari in Maasai Mara\r\nDay 4: Morning Bush Walk & Transfer to Diani Beach\r\nDay 5: Diani Beach Coral Reef Snorkeling & Coastal Leisure\r\nDay 6: Wasini Island Dhow Cruise & Dolphin Tour\r\nDay 7: Morning Beachfront Relaxation & Departure Transfer', NULL, NULL, '1', 'published', '1', '2026-10-21 15:27:34', '2026-10-21 19:17:14') ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `slug` = VALUES(`slug`), `destination_country` = VALUES(`destination_country`), `destination_city` = VALUES(`destination_city`), `summary` = VALUES(`summary`), `description` = VALUES(`description`), `cover_image` = VALUES(`cover_image`), `duration_days` = VALUES(`duration_days`), `travel_start_date` = VALUES(`travel_start_date`), `travel_end_date` = VALUES(`travel_end_date`), `price_amount` = VALUES(`price_amount`), `price_currency` = VALUES(`price_currency`), `inclusions` = VALUES(`inclusions`), `exclusions` = VALUES(`exclusions`), `itinerary` = VALUES(`itinerary`), `capacity` = VALUES(`capacity`), `booking_deadline` = VALUES(`booking_deadline`), `is_payment_enabled` = VALUES(`is_payment_enabled`), `status` = VALUES(`status`), `is_featured` = VALUES(`is_featured`), `created_at` = VALUES(`created_at`), `updated_at` = VALUES(`updated_at`);
INSERT INTO `packages` (`id`, `title`, `slug`, `destination_country`, `destination_city`, `summary`, `description`, `cover_image`, `duration_days`, `travel_start_date`, `travel_end_date`, `price_amount`, `price_currency`, `inclusions`, `exclusions`, `itinerary`, `capacity`, `booking_deadline`, `is_payment_enabled`, `status`, `is_featured`, `created_at`, `updated_at`) VALUES ('63', 'Dubai Luxury City & Desert Adventure', 'dubai-luxury-experience', 'United Arab Emirates', 'Dubai', 'Burj Khalifa experience, desert safari dune bashing, luxury marina dhow dinner cruise, and Dubai gold souk tour.', 'Discover the dazzling blend of futuristic architecture, world-class shopping, and Arabian heritage in Dubai. Stay at a 4-star city center hotel with easy access to Dubai Mall and Downtown.\r\n\r\nHighlights include ascending to the At the Top observatory at Burj Khalifa, a thrill-packed 4x4 desert safari with dune bashing and BBQ dinner under the stars, and a romantic evening dinner cruise through Dubai Marina.', '/assets/images/pkg-dubai.jpg', '6', NULL, NULL, '1450000.00', 'NGN', '- 5 Nights 4-star hotel accommodation with daily buffet breakfast\r\n- Dubai International Airport return private transfers\r\n- Half-day Dubai city heritage & modern architecture tour\r\n- Desert Safari with 4x4 dune bashing, camel rides & BBQ dinner\r\n- Dubai Marina 5-star international buffet dhow dinner cruise\r\n- Burj Khalifa Level 124/125 standard admission ticket', '- UAE tourist visa (available through our visa assistance desk)\r\n- International flight tickets\r\n- UAE Tourism Dirham hotel tax (payable directly at hotel)\r\n- Meals not explicitly listed in the inclusions', 'Day 1: Arrival at DXB & Private Luxury Hotel Check-In\r\nDay 2: Modern Dubai City Tour & Burj Khalifa At the Top\r\nDay 3: Afternoon Desert Safari with Dune Bashing, Sandboarding & BBQ\r\nDay 4: Dubai Mall Shopping & Evening Marina Dhow Dinner Cruise\r\nDay 5: Miracle Garden, Old Dubai Creek Abra Ride & Gold Souk\r\nDay 6: Souvenir Shopping & Private Airport Departure', NULL, NULL, '1', 'published', '1', '2026-10-21 15:27:34', '2026-10-21 19:16:40') ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `slug` = VALUES(`slug`), `destination_country` = VALUES(`destination_country`), `destination_city` = VALUES(`destination_city`), `summary` = VALUES(`summary`), `description` = VALUES(`description`), `cover_image` = VALUES(`cover_image`), `duration_days` = VALUES(`duration_days`), `travel_start_date` = VALUES(`travel_start_date`), `travel_end_date` = VALUES(`travel_end_date`), `price_amount` = VALUES(`price_amount`), `price_currency` = VALUES(`price_currency`), `inclusions` = VALUES(`inclusions`), `exclusions` = VALUES(`exclusions`), `itinerary` = VALUES(`itinerary`), `capacity` = VALUES(`capacity`), `booking_deadline` = VALUES(`booking_deadline`), `is_payment_enabled` = VALUES(`is_payment_enabled`), `status` = VALUES(`status`), `is_featured` = VALUES(`is_featured`), `created_at` = VALUES(`created_at`), `updated_at` = VALUES(`updated_at`);
INSERT INTO `packages` (`id`, `title`, `slug`, `destination_country`, `destination_city`, `summary`, `description`, `cover_image`, `duration_days`, `travel_start_date`, `travel_end_date`, `price_amount`, `price_currency`, `inclusions`, `exclusions`, `itinerary`, `capacity`, `booking_deadline`, `is_payment_enabled`, `status`, `is_featured`, `created_at`, `updated_at`) VALUES ('64', 'Rwanda Cultural & Volcanoes Discovery', 'rwanda-cultural-tour', 'Rwanda', 'Kigali & Musanze', 'Explore the spotless streets of Kigali, visit the memorial center, and journey into the scenic mountains of Volcanoes National Park.', 'Rwanda is celebrated as one of Africa\'s cleanest, safest, and most inspiring destinations. This comprehensive 5-day itinerary introduces you to the vibrancy of Kigali-visiting the Kigali Genocide Memorial and local art centers-before traveling to the lush volcanoes of Musanze for cultural village experiences and twin lakes exploration.', '/assets/images/pkg-rwanda.jpg', '5', NULL, NULL, '1250000.00', 'NGN', '- 4 Nights hotel and lodge accommodation\r\n- Daily breakfast and scheduled tour lunches\r\n- Private ground transportation in sanitized tour van\r\n- Kigali city guided historical tour and entry fees\r\n- Iby\'Iwacu cultural village immersion and traditional dance show\r\n- English/French speaking certified Rwandan tour guide', '- International flights\r\n- Rwanda entry visa (or East Africa Tourist Visa)\r\n- Optional mountain gorilla trekking permit\r\n- Personal travel insurance and tips', 'Day 1: Arrival at Kigali International Airport & Hotel Check-in\r\nDay 2: Kigali City Architecture, Inema Arts & Genocide Memorial\r\nDay 3: Scenic Drive to Musanze (Volcanoes) & Twin Lakes Cruise\r\nDay 4: Iby\'Iwacu Cultural Village Immersion & Gorilla Guardians\r\nDay 5: Return to Kigali, Kimironko Market Crafts & Airport Transfer', NULL, NULL, '1', 'published', '1', '2026-10-21 15:27:34', '2026-10-21 19:15:34') ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `slug` = VALUES(`slug`), `destination_country` = VALUES(`destination_country`), `destination_city` = VALUES(`destination_city`), `summary` = VALUES(`summary`), `description` = VALUES(`description`), `cover_image` = VALUES(`cover_image`), `duration_days` = VALUES(`duration_days`), `travel_start_date` = VALUES(`travel_start_date`), `travel_end_date` = VALUES(`travel_end_date`), `price_amount` = VALUES(`price_amount`), `price_currency` = VALUES(`price_currency`), `inclusions` = VALUES(`inclusions`), `exclusions` = VALUES(`exclusions`), `itinerary` = VALUES(`itinerary`), `capacity` = VALUES(`capacity`), `booking_deadline` = VALUES(`booking_deadline`), `is_payment_enabled` = VALUES(`is_payment_enabled`), `status` = VALUES(`status`), `is_featured` = VALUES(`is_featured`), `created_at` = VALUES(`created_at`), `updated_at` = VALUES(`updated_at`);
INSERT INTO `packages` (`id`, `title`, `slug`, `destination_country`, `destination_city`, `summary`, `description`, `cover_image`, `duration_days`, `travel_start_date`, `travel_end_date`, `price_amount`, `price_currency`, `inclusions`, `exclusions`, `itinerary`, `capacity`, `booking_deadline`, `is_payment_enabled`, `status`, `is_featured`, `created_at`, `updated_at`) VALUES ('65', 'Ghana Heritage & Coastal Discovery', 'ghana-heritage-tour', 'Ghana', 'Accra & Cape Coast', 'Immerse yourself in Ghanaian culture, visit the historic Cape Coast Castle, and experience the Kakum National Park canopy walk.', 'A profound journey through West African history and culture. Explore the buzzing neighborhoods of Accra, visit Kwame Nkrumah Memorial Park and the vibrant Makola Market. Travel along the coastline to the historic Cape Coast and Elmina Castles, and walk high above the rainforest on the canopy walkway of Kakum National Park.', '/assets/images/pkg-ghana.jpg', '5', NULL, NULL, '850000.00', 'NGN', '- 4 Nights 3/4-star accommodation in Accra and Cape Coast\r\n- Daily breakfast and select regional lunches\r\n- Air-conditioned private tour transportation\r\n- Guided entry to Cape Coast Castle and Kakum National Park\r\n- Accra cultural crafts and memorial park tour\r\n- Dedicated local tour guide', '- Flights or road transport from Nigeria\r\n- Personal shopping and discretionary tips\r\n- Meals outside the scheduled itinerary', 'Day 1: Arrival at Kotoka Airport in Accra & Welcome Dinner\r\nDay 2: Independence Square, Kwame Nkrumah Park & Makola Market\r\nDay 3: Scenic Coastal Drive to Cape Coast & Historic Castle Tour\r\nDay 4: Kakum National Park Rainforest Canopy Walkway\r\nDay 5: Craft Center Shopping & Return Transfer to Airport', NULL, NULL, '1', 'published', '1', '2026-10-21 15:27:34', '2026-10-21 19:16:09') ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `slug` = VALUES(`slug`), `destination_country` = VALUES(`destination_country`), `destination_city` = VALUES(`destination_city`), `summary` = VALUES(`summary`), `description` = VALUES(`description`), `cover_image` = VALUES(`cover_image`), `duration_days` = VALUES(`duration_days`), `travel_start_date` = VALUES(`travel_start_date`), `travel_end_date` = VALUES(`travel_end_date`), `price_amount` = VALUES(`price_amount`), `price_currency` = VALUES(`price_currency`), `inclusions` = VALUES(`inclusions`), `exclusions` = VALUES(`exclusions`), `itinerary` = VALUES(`itinerary`), `capacity` = VALUES(`capacity`), `booking_deadline` = VALUES(`booking_deadline`), `is_payment_enabled` = VALUES(`is_payment_enabled`), `status` = VALUES(`status`), `is_featured` = VALUES(`is_featured`), `created_at` = VALUES(`created_at`), `updated_at` = VALUES(`updated_at`);
INSERT INTO `packages` (`id`, `title`, `slug`, `destination_country`, `destination_city`, `summary`, `description`, `cover_image`, `duration_days`, `travel_start_date`, `travel_end_date`, `price_amount`, `price_currency`, `inclusions`, `exclusions`, `itinerary`, `capacity`, `booking_deadline`, `is_payment_enabled`, `status`, `is_featured`, `created_at`, `updated_at`) VALUES ('66', 'Japan & Mount Fuji Alpine Discovery', 'japan-discovery', 'Japan', 'Tokyo & Mt. Fuji', 'High-speed bullet train journey, Tokyo city highlights, Hakone hot spring resorts, and breathtaking panoramic views of Mount Fuji.', 'Immerse yourself in the timeless harmony of ancient tradition and futuristic innovation in Japan. Begin in Tokyo exploring historic Asakusa, the iconic Shibuya crossing, and modern Ginza. Travel by Shinkansen bullet train to the scenic Hakone region, cruise across Lake Ashi with views of Mount Fuji, and relax in traditional onsen hot springs.', '/assets/images/pkg-fuji.jpg', '7', NULL, NULL, '2950000.00', 'NGN', '- 6 Nights 4-star hotel and ryokan accommodation\r\n- Daily breakfast and scheduled Japanese Kaiseki dinners\r\n- 7-Day Japan Rail (JR) Shinkansen train pass\r\n- Tokyo city guided tour and Lake Ashi sightseeing cruise\r\n- Mount Fuji 5th Station excursion\r\n- English-speaking local tour director', '- International flights from Lagos\r\n- Japan tourist visa processing\r\n- Personal expenses and discretionary gratuities\r\n- Lunches outside scheduled excursions', 'Day 1: Arrival at Tokyo Haneda/Narita & Hotel Check-in\r\nDay 2: Tokyo City Tour: Senso-ji Temple, Meiji Shrine & Shibuya Crossing\r\nDay 3: Shinkansen Bullet Train to Hakone National Park\r\nDay 4: Lake Ashi Pirate Ship Cruise, Ropeway & Mount Fuji Views\r\nDay 5: Traditional Onsen Hot Springs & Return to Tokyo\r\nDay 6: Free Day for Akihabara & Ginza Luxury Shopping\r\nDay 7: Farewell Breakfast & Private Airport Transfer', NULL, NULL, '1', 'published', '1', '2026-10-21 15:27:34', '2026-10-21 19:14:50') ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `slug` = VALUES(`slug`), `destination_country` = VALUES(`destination_country`), `destination_city` = VALUES(`destination_city`), `summary` = VALUES(`summary`), `description` = VALUES(`description`), `cover_image` = VALUES(`cover_image`), `duration_days` = VALUES(`duration_days`), `travel_start_date` = VALUES(`travel_start_date`), `travel_end_date` = VALUES(`travel_end_date`), `price_amount` = VALUES(`price_amount`), `price_currency` = VALUES(`price_currency`), `inclusions` = VALUES(`inclusions`), `exclusions` = VALUES(`exclusions`), `itinerary` = VALUES(`itinerary`), `capacity` = VALUES(`capacity`), `booking_deadline` = VALUES(`booking_deadline`), `is_payment_enabled` = VALUES(`is_payment_enabled`), `status` = VALUES(`status`), `is_featured` = VALUES(`is_featured`), `created_at` = VALUES(`created_at`), `updated_at` = VALUES(`updated_at`);

-- -----------------------------------------------------------------------------
-- Package Gallery Images
-- -----------------------------------------------------------------------------
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('1', '62', '/assets/images/pkg-kenya.jpg', 'Maasai Mara Savannah Safari Game Drive', '1', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('5', '63', '/assets/images/pkg-dubai.jpg', 'Burj Khalifa & Downtown Dubai Panoramic View', '1', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('9', '64', '/assets/images/pkg-rwanda.jpg', 'Kigali City Panorama & Scenic Green Hills', '1', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('17', '66', '/assets/images/pkg-fuji.jpg', 'Mount Fuji with Spring Cherry Blossoms', '1', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('14', '65', '/assets/images/pkg-ghana-coast.jpg', 'Elmina Castle Heritage & Golden Shorelines', '2', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('18', '66', '/assets/images/pkg-fuji-lake.jpg', 'Hakone Lake Ashi Sightseeing Cruise & Mt. Fuji Views', '2', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('3', '62', '/assets/images/pkg-kenya-safari.jpg', 'African Wildlife & 4x4 Safari Cruiser', '3', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('11', '64', '/assets/images/pkg-fuji-lake.jpg', 'Rwanda Twin Lakes & Natural Rainforest Excursion', '3', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('15', '65', '/assets/images/pkg-coastal-escape.jpg', 'Kakum National Park Suspended Rainforest Canopy Walk', '3', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('4', '62', '/assets/images/pkg-travel-story.jpg', 'VIP Airport Concierge & Flight Departure', '4', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('12', '64', '/assets/images/pkg-travel-story.jpg', 'Iby\'Iwacu Cultural Village Immersion & Traditions', '4', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('16', '65', '/assets/images/pkg-travel-story.jpg', 'Accra Cultural Arts & Memorial Heritage Tour', '4', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);
INSERT INTO `package_images` (`id`, `package_id`, `image_path`, `alt_text`, `display_order`, `created_at`) VALUES ('20', '66', '/assets/images/pkg-travel-story.jpg', 'Tokyo Modern Cityscape & Traditional Ryokan Stay', '4', '2026-10-21 15:41:09') ON DUPLICATE KEY UPDATE `package_id` = VALUES(`package_id`), `image_path` = VALUES(`image_path`), `alt_text` = VALUES(`alt_text`), `display_order` = VALUES(`display_order`), `created_at` = VALUES(`created_at`);

-- -----------------------------------------------------------------------------
-- Initial Super Administrator Account
-- -----------------------------------------------------------------------------
INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `failed_login_attempts`, `locked_until`, `created_at`) VALUES (19, 'admin', 'admin@ladglobaltravels.com', '$2y$10$OaITyeO53unLBsW7bCp4fOGQNuu3bNXo3.huO8UwTqiowQt1PymuO', 0, NULL, NOW()) ON DUPLICATE KEY UPDATE `username` = VALUES(`username`), `email` = VALUES(`email`), `password_hash` = VALUES(`password_hash`);

SET FOREIGN_KEY_CHECKS = 1;
