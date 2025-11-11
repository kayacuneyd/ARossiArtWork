-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Artworks table
CREATE TABLE IF NOT EXISTS artworks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    title VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    year YEAR NULL,
    technique VARCHAR(180) NULL,
    dimensions VARCHAR(120) NULL,
    price DECIMAL(10,2) NULL,
    currency CHAR(3) DEFAULT 'GBP',
    image_path VARCHAR(255) NOT NULL,
    thumbnail_path VARCHAR(255) NOT NULL,
    webp_path VARCHAR(255) NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    display_order INT NOT NULL DEFAULT 0,
    metadata JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_artworks_published (is_published),
    INDEX idx_artworks_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inquiries table
CREATE TABLE IF NOT EXISTS inquiries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    artwork_id BIGINT UNSIGNED NULL,
    name VARCHAR(180) NOT NULL,
    email VARCHAR(180) NOT NULL,
    phone VARCHAR(64) NULL,
    preferred_size VARCHAR(120) NULL,
    message TEXT NOT NULL,
    whatsapp_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_inquiries_artwork_id (artwork_id),
    CONSTRAINT fk_inquiries_artwork FOREIGN KEY (artwork_id) REFERENCES artworks(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings table
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed defaults
INSERT INTO settings (setting_key, setting_value)
VALUES
    ('artist_email', 'artist@example.com'),
    ('whatsapp_number', '+447483284919'),
    ('max_upload_mb', '8')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);
