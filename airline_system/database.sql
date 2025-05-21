-- Create database
CREATE DATABASE IF NOT EXISTS airline_management;
USE airline_management;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    loyalty_points INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create admin user (password: Admin@123)
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@skyhigh.com', '$2y$10$XByPLqhShEAdwMzXPIGFTelvUymRvOIKPwub4rclGHFoAwn3nCXIK', 'admin');

-- Loyalty rewards
CREATE TABLE loyalty_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    points_required INT NOT NULL,
    discount_percent INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

INSERT INTO loyalty_rewards (name, points_required, discount_percent) VALUES 
('Silver Member', 1000, 5),
('Gold Member', 2500, 10),
('Platinum Member', 5000, 15);

-- Flights table
CREATE TABLE flights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_number VARCHAR(20) NOT NULL UNIQUE,
    airline VARCHAR(50) NOT NULL,
    departure_airport VARCHAR(50) NOT NULL,
    arrival_airport VARCHAR(50) NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    economy_price DECIMAL(10,2) NOT NULL,
    business_price DECIMAL(10,2) NOT NULL,
    first_class_price DECIMAL(10,2) NOT NULL,
    total_seats INT NOT NULL,
    available_seats INT NOT NULL,
    status ENUM('scheduled', 'delayed', 'cancelled', 'completed') DEFAULT 'scheduled',
    gate_number VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    booking_reference VARCHAR(20) NOT NULL UNIQUE,
    travel_class ENUM('economy', 'business', 'first') NOT NULL,
    passengers INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (flight_id) REFERENCES flights(id)
);

-- Passengers table
CREATE TABLE passengers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    passport_number VARCHAR(50),
    date_of_birth DATE,
    meal_preference VARCHAR(20),
    special_assistance TEXT,
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('credit_card', 'debit_card', 'paypal', 'bank_transfer') NOT NULL,
    transaction_id VARCHAR(100),
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

-- Multi-city bookings tables
CREATE TABLE multi_city_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_reference VARCHAR(20) NOT NULL UNIQUE,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE multi_city_segments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    flight_id INT NOT NULL,
    travel_class ENUM('economy', 'business', 'first') NOT NULL,
    segment_order INT NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES multi_city_bookings(id),
    FOREIGN KEY (flight_id) REFERENCES flights(id)
);

-- Hotels table
CREATE TABLE hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    description TEXT,
    amenities TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    rating DECIMAL(3,1),
    available_rooms INT NOT NULL,
    status ENUM('active', 'maintenance', 'closed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hotel bookings table
CREATE TABLE hotel_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    rooms INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    booking_reference VARCHAR(20) UNIQUE,
    guest_first_name VARCHAR(50) NOT NULL,
    guest_last_name VARCHAR(50) NOT NULL,
    guest_email VARCHAR(100) NOT NULL,
    guest_phone VARCHAR(20) NOT NULL,
    special_requests TEXT,
    payment_method VARCHAR(50) DEFAULT 'direct_payment',
    transaction_id VARCHAR(50) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Hotel booking status table
CREATE TABLE hotel_bookings_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES hotel_bookings(id)
);

-- Room types table
CREATE TABLE room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_occupancy INT NOT NULL,
    amenities TEXT,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Hotel facilities table
CREATE TABLE hotel_facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    facility_name VARCHAR(50) NOT NULL,
    description TEXT,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Hotel photos table
CREATE TABLE hotel_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    photo_url VARCHAR(255) NOT NULL,
    caption TEXT,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Hotel reviews table
CREATE TABLE hotel_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_review (hotel_id, user_id)
);

-- Feedback table
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_id INT NULL,
    rating TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comments TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id)
);

-- Sample hotels
INSERT INTO hotels (name, location, description, amenities, price_per_night, rating, available_rooms, status) VALUES
('Grand Central Hotel', 'New York City, USA', 'Luxury 5-star hotel in the heart of Manhattan', 'WiFi, Pool, Gym, Restaurant', 299.99, 4.8, 150, 'active'),
('Beachfront Paradise Resort', 'Miami, USA', 'Modern resort with beach access', 'WiFi, Pool, Spa, Restaurant', 199.99, 4.5, 200, 'active'),
('Mountain View Lodge', 'Aspen, USA', 'Mountain resort with ski access', 'WiFi, Ski Shuttle, Restaurant', 249.99, 4.7, 100, 'active'),
('Downtown Suites', 'Chicago, USA', 'Modern boutique hotel in downtown', 'WiFi, Gym, Restaurant', 179.99, 4.6, 120, 'active'),
('Oceanfront Inn', 'San Diego, USA', 'Coastal hotel with ocean views', 'WiFi, Pool, Restaurant', 229.99, 4.4, 180, 'active');

-- Room types for each hotel
INSERT INTO room_types (hotel_id, room_type, description, price_per_night, max_occupancy, amenities) VALUES
-- Grand Central Hotel
(1, 'Deluxe Room', 'Spacious room with city view', 349.99, 2, 'King bed, City view, Free WiFi, Coffee maker'),
(1, 'Executive Suite', 'Luxury suite with separate living area', 599.99, 4, 'King bed, Living room, City view, Free WiFi, Coffee maker, Sofa bed'),
(1, 'Premium Suite', 'Corner suite with panoramic views', 799.99, 4, 'King bed, Living room, City view, Free WiFi, Coffee maker, Sofa bed, Balcony'),

-- Beachfront Paradise Resort
(2, 'Ocean View Room', 'Room with beach access', 249.99, 2, 'Queen bed, Beach view, Free WiFi, Coffee maker'),
(2, 'Beachfront Suite', 'Suite with direct beach access', 499.99, 4, 'King bed, Living room, Beach view, Free WiFi, Coffee maker, Sofa bed'),
(2, 'Family Suite', 'Suite for families', 399.99, 4, '2 Queen beds, Living room, Beach view, Free WiFi, Coffee maker'),

-- Mountain View Lodge
(3, 'Mountain View Room', 'Room with mountain view', 279.99, 2, 'King bed, Mountain view, Free WiFi, Coffee maker'),
(3, 'Ski Suite', 'Suite with ski access', 449.99, 4, 'King bed, Living room, Mountain view, Free WiFi, Coffee maker, Sofa bed'),
(3, 'Family Suite', 'Suite for families', 349.99, 4, '2 Queen beds, Living room, Mountain view, Free WiFi, Coffee maker'),

-- Downtown Suites
(4, 'Standard Room', 'Modern room with city view', 199.99, 2, 'Queen bed, City view, Free WiFi, Coffee maker'),
(4, 'Executive Room', 'Premium room with business amenities', 249.99, 2, 'King bed, City view, Free WiFi, Coffee maker, Work desk'),
(4, 'Suite', 'Luxury suite with separate living area', 399.99, 4, 'King bed, Living room, City view, Free WiFi, Coffee maker, Sofa bed'),

-- Oceanfront Inn
(5, 'Ocean View Room', 'Room with ocean view', 279.99, 2, 'Queen bed, Ocean view, Free WiFi, Coffee maker'),
(5, 'Beachfront Suite', 'Suite with direct beach access', 449.99, 4, 'King bed, Living room, Ocean view, Free WiFi, Coffee maker, Sofa bed'),
(5, 'Family Suite', 'Suite for families', 349.99, 4, '2 Queen beds, Living room, Ocean view, Free WiFi, Coffee maker');

-- Hotel facilities
INSERT INTO hotel_facilities (hotel_id, facility_name, description) VALUES
(1, 'Fitness Center', '24/7 access to modern gym'),
(1, 'Indoor Pool', 'Heated indoor pool'),
(1, 'Spa', 'Full-service spa with massage'),
(1, 'Restaurant', 'Fine dining restaurant'),
(1, 'Business Center', '24/7 business services'),

(2, 'Beach Access', 'Direct beach access'),
(2, 'Pool', 'Outdoor pool with beach view'),
(2, 'Spa', 'Beachfront spa'),
(2, 'Restaurant', 'Beachfront restaurant'),
(2, 'Kids Club', 'Children entertainment'),

(3, 'Ski Shuttle', 'Free ski shuttle service'),
(3, 'Pool', 'Outdoor pool'),
(3, 'Spa', 'Mountain view spa'),
(3, 'Restaurant', 'Mountain view restaurant'),
(3, 'Ski Rental', 'On-site ski rental'),

(4, 'Fitness Center', '24/7 access to gym'),
(4, 'Pool', 'Indoor pool'),
(4, 'Restaurant', 'Modern restaurant'),
(4, 'Business Center', '24/7 business services'),
(4, 'Conference Rooms', 'Meeting facilities'),

(5, 'Beach Access', 'Direct beach access'),
(5, 'Pool', 'Outdoor pool'),
(5, 'Spa', 'Ocean view spa'),
(5, 'Restaurant', 'Ocean view restaurant'),
(5, 'Kids Club', 'Children entertainment');

-- Hotel photos
INSERT INTO hotel_photos (hotel_id, photo_url, caption, sort_order) VALUES
(1, 'images/hotels/grand_central_1.jpg', 'Grand Central Hotel - Lobby', 1),
(1, 'images/hotels/grand_central_2.jpg', 'Grand Central Hotel - Pool', 2),
(1, 'images/hotels/grand_central_3.jpg', 'Grand Central Hotel - Restaurant', 3),
(1, 'images/hotels/grand_central_4.jpg', 'Grand Central Hotel - Suite', 4),

(2, 'images/hotels/beachfront_1.jpg', 'Beachfront Paradise Resort - Beach', 1),
(2, 'images/hotels/beachfront_2.jpg', 'Beachfront Paradise Resort - Pool', 2),
(2, 'images/hotels/beachfront_3.jpg', 'Beachfront Paradise Resort - Restaurant', 3),
(2, 'images/hotels/beachfront_4.jpg', 'Beachfront Paradise Resort - Suite', 4),

(3, 'images/hotels/mountain_1.jpg', 'Mountain View Lodge - Lobby', 1),
(3, 'images/hotels/mountain_2.jpg', 'Mountain View Lodge - Pool', 2),
(3, 'images/hotels/mountain_3.jpg', 'Mountain View Lodge - Restaurant', 3),
(3, 'images/hotels/mountain_4.jpg', 'Mountain View Lodge - Suite', 4),

(4, 'images/hotels/downtown_1.jpg', 'Downtown Suites - Lobby', 1),
(4, 'images/hotels/downtown_2.jpg', 'Downtown Suites - Pool', 2),
(4, 'images/hotels/downtown_3.jpg', 'Downtown Suites - Restaurant', 3),
(4, 'images/hotels/downtown_4.jpg', 'Downtown Suites - Suite', 4),

(5, 'images/hotels/oceanfront_1.jpg', 'Oceanfront Inn - Beach', 1),
(5, 'images/hotels/oceanfront_2.jpg', 'Oceanfront Inn - Pool', 2),
(5, 'images/hotels/oceanfront_3.jpg', 'Oceanfront Inn - Restaurant', 3),
(5, 'images/hotels/oceanfront_4.jpg', 'Oceanfront Inn - Suite', 4);
