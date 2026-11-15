
-- VIOLET-LUE SUITES & APARTMENTS
-- Clean database structure for project setup.
-- No real guest records or administrator accounts included.

CREATE DATABASE IF NOT EXISTS violet_lue_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE violet_lue_db;

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS guests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_reference VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(30) NOT NULL,
    nationality VARCHAR(100) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    guest_id INT DEFAULT NULL,
    booking_reference VARCHAR(30) NOT NULL UNIQUE,
    room_type VARCHAR(100) NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INT NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    special_requests TEXT DEFAULT NULL,
    status ENUM('pending', 'confirmed', 'cancelled')
        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reservations_guest_id (guest_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_number VARCHAR(30) NOT NULL UNIQUE,
    room_name VARCHAR(150) NOT NULL,
    room_type ENUM('luxury-room', 'self-contained-apartment') NOT NULL,
    description TEXT DEFAULT NULL,
    price_per_night DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    capacity INT NOT NULL DEFAULT 2,
    photo VARCHAR(255) DEFAULT NULL,
    status ENUM('available', 'occupied', 'maintenance')
        NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reservation_id INT NOT NULL,
    payment_reference VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM(
        'cash',
        'bank-transfer',
        'mobile-money',
        'card',
        'other'
    ) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded')
        NOT NULL DEFAULT 'pending',
    transaction_reference VARCHAR(100) DEFAULT NULL,
    payment_date DATETIME DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_payment_reservation (reservation_id),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
