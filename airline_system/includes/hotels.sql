-- Add guest information columns to hotel_bookings table
ALTER TABLE hotel_bookings ADD COLUMN guest_first_name VARCHAR(50) NOT NULL;
ALTER TABLE hotel_bookings ADD COLUMN guest_last_name VARCHAR(50) NOT NULL;
ALTER TABLE hotel_bookings ADD COLUMN guest_email VARCHAR(100) NOT NULL;
ALTER TABLE hotel_bookings ADD COLUMN guest_phone VARCHAR(20) NOT NULL;
ALTER TABLE hotel_bookings ADD COLUMN special_requests TEXT;

-- Add booking_reference to hotel_bookings table
ALTER TABLE hotel_bookings ADD COLUMN booking_reference VARCHAR(20) UNIQUE;

-- Add transaction_id to hotel_bookings table
ALTER TABLE hotel_bookings ADD COLUMN transaction_id VARCHAR(20) UNIQUE;

-- Add status column to hotels table (for maintenance)
ALTER TABLE hotels ADD COLUMN status ENUM('active', 'maintenance', 'closed') DEFAULT 'active';

-- Add room_types table
CREATE TABLE IF NOT EXISTS room_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    room_type VARCHAR(50) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    max_occupancy INT NOT NULL,
    amenities TEXT,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Add hotel_reviews table
CREATE TABLE IF NOT EXISTS hotel_reviews (
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

-- Add hotel_bookings_status table
CREATE TABLE IF NOT EXISTS hotel_bookings_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    status VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES hotel_bookings(id)
);

-- Add hotel_facilities table
CREATE TABLE IF NOT EXISTS hotel_facilities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    facility_name VARCHAR(50) NOT NULL,
    description TEXT,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Add hotel_photos table
CREATE TABLE IF NOT EXISTS hotel_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hotel_id INT NOT NULL,
    photo_url VARCHAR(255) NOT NULL,
    caption TEXT,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Insert sample hotels
INSERT INTO hotels (name, location, description, amenities, price_per_night, rating, available_rooms, status) VALUES
('Grand Central Hotel', 'New York City, USA', 'Luxury 5-star hotel in the heart of Manhattan', 'WiFi, Pool, Gym, Restaurant', 299.99, 4.8, 150, 'active'),
('Beachfront Paradise Resort', 'Miami, USA', 'Modern resort with beach access', 'WiFi, Pool, Spa, Restaurant', 199.99, 4.5, 200, 'active'),
('Mountain View Lodge', 'Aspen, USA', 'Mountain resort with ski access', 'WiFi, Ski Shuttle, Restaurant', 249.99, 4.7, 100, 'active'),
('Downtown Suites', 'Chicago, USA', 'Modern boutique hotel in downtown', 'WiFi, Gym, Restaurant', 179.99, 4.6, 120, 'active'),
('Oceanfront Inn', 'San Diego, USA', 'Coastal hotel with ocean views', 'WiFi, Pool, Restaurant', 229.99, 4.4, 180, 'active');

-- Insert room types for each hotel
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

-- Insert hotel facilities
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

-- Insert hotel photos
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
