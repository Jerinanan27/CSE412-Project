
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create admin user (password: Admin@123)
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@skyhigh.com', '$2y$10$3pANw2z3WnrC8nYYV7q6QO7vQwLZ7e5m5Xo6D3vJW7dNk9V1sXJ1O', 'admin');



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

-- Sample flights
INSERT INTO flights (flight_number, airline, departure_airport, arrival_airport, departure_time, arrival_time, economy_price, business_price, first_class_price, total_seats, available_seats) 
VALUES 
('SH101', 'SkyHigh', 'JFK', 'LAX', '2023-12-01 08:00:00', '2023-12-01 11:00:00', 199.99, 399.99, 599.99, 150, 150),
('SH202', 'SkyHigh', 'LAX', 'JFK', '2023-12-02 14:00:00', '2023-12-02 22:00:00', 249.99, 449.99, 699.99, 200, 200);



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


ALTER TABLE users ADD COLUMN loyalty_points INT DEFAULT 0;

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


CREATE TABLE hotels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100) NOT NULL,
    description TEXT,
    amenities TEXT,
    price_per_night DECIMAL(10,2) NOT NULL,
    rating DECIMAL(3,1),
    available_rooms INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE hotel_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    hotel_id INT NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    rooms INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('confirmed', 'cancelled', 'completed') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (hotel_id) REFERENCES hotels(id)
);

-- Sample hotels
INSERT INTO hotels (name, location, description, amenities, price_per_night, rating, available_rooms) VALUES 
('Grand Sky Hotel', 'New York', 'Luxury hotel in downtown Manhattan', 'Pool, Spa, Gym, Restaurant', 299.99, 4.7, 50),
('Airport Plaza', 'Los Angeles', 'Convenient airport hotel with shuttle service', 'Free WiFi, Shuttle, Breakfast', 149.99, 4.2, 100);



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