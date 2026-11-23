CREATE DATABASE IF NOT EXISTS client_tracker_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE client_tracker_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255) NULL,
    age TINYINT UNSIGNED NOT NULL,
    phone VARCHAR(30) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS clients (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    business_name VARCHAR(120) NOT NULL,
    industry VARCHAR(100) NOT NULL,
    contact_person VARCHAR(120) NOT NULL,
    email VARCHAR(100) NOT NULL,
    contact_no VARCHAR(30) NULL,
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    added_by INT UNSIGNED NULL,
    last_updated_by INT UNSIGNED NULL,
    deleted_by INT UNSIGNED NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_clients_email (email),
    KEY idx_clients_deleted_at (deleted_at),
    CONSTRAINT fk_clients_added_by FOREIGN KEY (added_by) REFERENCES users (id),
    CONSTRAINT fk_clients_updated_by FOREIGN KEY (last_updated_by) REFERENCES users (id),
    CONSTRAINT fk_clients_deleted_by FOREIGN KEY (deleted_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS automations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    client_id INT UNSIGNED NOT NULL,
    automation_name VARCHAR(150) NOT NULL,
    type ENUM('Lead Capture', 'Speed-to-Lead', 'CRM Sync', 'Chatbot') NOT NULL,
    tool_used ENUM('Zapier', 'Make', 'n8n', 'GoHighLevel') NOT NULL,
    monthly_fee DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status ENUM('Planning', 'Building', 'Live', 'Paused') NOT NULL DEFAULT 'Planning',
    image_path VARCHAR(255) NULL,
    date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    added_by INT UNSIGNED NULL,
    last_updated_by INT UNSIGNED NULL,
    deleted_by INT UNSIGNED NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_automations_client_id (client_id),
    KEY idx_automations_deleted_at (deleted_at),
    CONSTRAINT fk_automations_client FOREIGN KEY (client_id) REFERENCES clients (id),
    CONSTRAINT fk_automations_added_by FOREIGN KEY (added_by) REFERENCES users (id),
    CONSTRAINT fk_automations_updated_by FOREIGN KEY (last_updated_by) REFERENCES users (id),
    CONSTRAINT fk_automations_deleted_by FOREIGN KEY (deleted_by) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE automations
    MODIFY COLUMN tool_used ENUM('Zapier', 'Make', 'n8n', 'GoHighLevel') NOT NULL;
