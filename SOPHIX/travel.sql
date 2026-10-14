-- Run this in phpMyAdmin AFTER selecting sophix_dynamics.
-- These tables are added to your existing database; this does not delete existing tables.

USE sophix_dynamics;

CREATE TABLE IF NOT EXISTS travel_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_type ENUM('flight','hotel','ride','bus','train','car_rental','mover','airport_transfer','package','activity') NOT NULL,
    provider_name VARCHAR(150) NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    origin VARCHAR(150) NULL,
    destination VARCHAR(150) NULL,
    location VARCHAR(200) NULL,
    departure_datetime DATETIME NULL,
    arrival_datetime DATETIME NULL,
    check_in_date DATE NULL,
    check_out_date DATE NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    currency VARCHAR(8) NOT NULL DEFAULT 'NGN',
    capacity INT NOT NULL DEFAULT 1,
    available_quantity INT NOT NULL DEFAULT 1,
    status ENUM('available','unavailable') NOT NULL DEFAULT 'available',
    image_url VARCHAR(500) NULL,
    details_json JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_service_type (service_type),
    INDEX idx_route (origin, destination),
    INDEX idx_dates (departure_datetime, check_in_date),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS travel_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference_code VARCHAR(32) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    service_type VARCHAR(40) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    travel_date DATE NULL,
    check_in_date DATE NULL,
    check_out_date DATE NULL,
    passenger_name VARCHAR(150) NULL,
    passenger_email VARCHAR(190) NULL,
    passenger_phone VARCHAR(40) NULL,
    notes TEXT NULL,
    total_price DECIMAL(12,2) NOT NULL,
    currency VARCHAR(8) NOT NULL DEFAULT 'NGN',
    status ENUM('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_bookings (user_id, created_at),
    INDEX idx_service_bookings (service_id)
);

CREATE TABLE IF NOT EXISTS travel_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(180) NOT NULL,
    reminder_at DATETIME NOT NULL,
    notes TEXT NULL,
    status ENUM('active','done','cancelled') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_reminders (user_id, reminder_at)
);

CREATE TABLE IF NOT EXISTS travel_saved_searches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_type VARCHAR(40) NOT NULL,
    origin VARCHAR(150) NULL,
    destination VARCHAR(150) NULL,
    location VARCHAR(200) NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    max_price DECIMAL(12,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_searches (user_id, created_at)
);

-- Optional test data for the interface. These are SAMPLE listings, not live external inventory.
-- Delete these INSERT statements if you do not want sample services.
INSERT INTO travel_services
(service_type, provider_name, title, description, origin, destination, location, departure_datetime, arrival_datetime, check_in_date, check_out_date, price, currency, capacity, available_quantity, status)
SELECT 'ride','Example Local Operator','Sample airport transfer','DEMO LISTING — replace with a real registered provider and confirm availability.','Abuja','Nnamdi Azikiwe International Airport','Abuja',NULL,NULL,NULL,NULL,15000,'NGN',3,1,'available'
WHERE NOT EXISTS (SELECT 1 FROM travel_services WHERE title='Sample airport transfer');

INSERT INTO travel_services
(service_type, provider_name, title, description, origin, destination, location, departure_datetime, arrival_datetime, check_in_date, check_out_date, price, currency, capacity, available_quantity, status)
SELECT 'mover','Example Moving Operator','Sample home moving service','DEMO LISTING — replace with a real registered moving provider.','Abuja','Abuja','Abuja',NULL,NULL,NULL,NULL,50000,'NGN',1,1,'available'
WHERE NOT EXISTS (SELECT 1 FROM travel_services WHERE title='Sample home moving service');
