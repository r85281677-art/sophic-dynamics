CREATE DATABASE IF NOT EXISTS sophix_dynamics;

USE sophix_dynamics;

DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS packages;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS travel_services;
DROP TABLE IF EXISTS users;


CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    phone VARCHAR(30),

    password_hash VARCHAR(255) NOT NULL,

    role ENUM('customer','driver')
        DEFAULT 'customer',

    notifications_enabled BOOLEAN
        DEFAULT TRUE,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE vehicles (

    id INT AUTO_INCREMENT PRIMARY KEY,

    driver_id INT NOT NULL,

    vehicle_type ENUM(
        'car',
        'van',
        'truck',
        'bike',
        'bus'
    ) NOT NULL,

    model VARCHAR(100) NOT NULL,

    plate_number VARCHAR(50)
        NOT NULL UNIQUE,

    capacity_kg DECIMAL(10,2)
        NOT NULL,

    status ENUM(
        'available',
        'busy',
        'offline'
    ) DEFAULT 'available',

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (driver_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


CREATE TABLE packages (

    id INT AUTO_INCREMENT PRIMARY KEY,

    customer_id INT NOT NULL,

    pickup_location VARCHAR(255)
        NOT NULL,

    destination VARCHAR(255)
        NOT NULL,

    package_type VARCHAR(100)
        NOT NULL,

    package_size ENUM(
        'small',
        'medium',
        'large'
    ) NOT NULL,

    weight_kg DECIMAL(10,2),

    tracking_number VARCHAR(60)
        NOT NULL UNIQUE,

    status ENUM(
        'searching',
        'driver_assigned',
        'in_transit',
        'delivered',
        'cancelled'
    ) DEFAULT 'searching',

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


CREATE TABLE bookings (

    id INT AUTO_INCREMENT PRIMARY KEY,

    customer_id INT NOT NULL,

    driver_id INT NULL,

    vehicle_id INT NULL,

    package_id INT NULL,

    service_type ENUM(
        'package',
        'vehicle',
        'travel',
        'tour'
    ) NOT NULL,

    service_name VARCHAR(150),

    pickup_location VARCHAR(255),

    destination VARCHAR(255),

    status ENUM(
        'pending',
        'accepted',
        'in_progress',
        'completed',
        'cancelled'
    ) DEFAULT 'pending',

    price DECIMAL(12,2)
        DEFAULT 0,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (driver_id)
        REFERENCES users(id)
        ON DELETE SET NULL,

    FOREIGN KEY (vehicle_id)
        REFERENCES vehicles(id)
        ON DELETE SET NULL,

    FOREIGN KEY (package_id)
        REFERENCES packages(id)
        ON DELETE SET NULL
);


CREATE TABLE notifications (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    title VARCHAR(150)
        NOT NULL,

    message TEXT
        NOT NULL,

    is_read BOOLEAN
        DEFAULT FALSE,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);


CREATE TABLE travel_services (

    id INT AUTO_INCREMENT PRIMARY KEY,

    category ENUM(
        'travel',
        'tour'
    ) NOT NULL,

    title VARCHAR(150)
        NOT NULL,

    description TEXT
        NOT NULL,

    price_from DECIMAL(12,2)
        DEFAULT 0,

    active BOOLEAN
        DEFAULT TRUE
);


INSERT INTO travel_services
(category, title, description, price_from)
VALUES

(
    'travel',
    'Airport Transfer',
    'Comfortable transportation to and from the airport.',
    5000
),

(
    'travel',
    'Intercity Transport',
    'Travel safely between cities.',
    10000
),

(
    'travel',
    'Private Car Hire',
    'Book a private car for your trip.',
    15000
),

(
    'tour',
    'City Tour',
    'Explore popular attractions around the city.',
    15000
),

(
    'tour',
    'Weekend Getaway',
    'Enjoy an organized weekend trip.',
    25000
),

(
    'tour',
    'Adventure Tour',
    'Discover new places and experiences.',
    30000
);