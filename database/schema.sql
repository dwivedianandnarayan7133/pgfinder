-- ============================================================
-- Student Accommodation Platform — Database Schema
-- Import via phpMyAdmin or: mysql -u root -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS student_accommodation
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE student_accommodation;

-- ============================================================
-- Table: users
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100)        NOT NULL,
  email      VARCHAR(150)        NOT NULL UNIQUE,
  password   VARCHAR(255)        NOT NULL,
  phone      VARCHAR(15)         DEFAULT NULL,
  created_at TIMESTAMP           DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: properties
-- ============================================================
CREATE TABLE IF NOT EXISTS properties (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(150)        NOT NULL,
  city        VARCHAR(80)         NOT NULL,
  address     VARCHAR(255)        NOT NULL,
  price       DECIMAL(10,2)       NOT NULL,
  gender      ENUM('male','female','any') NOT NULL DEFAULT 'any',
  rating      DECIMAL(2,1)        DEFAULT 4.0,
  description TEXT,
  image       VARCHAR(255)        DEFAULT 'images/default.jpg',
  created_at  TIMESTAMP           DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- Table: amenities
-- ============================================================
CREATE TABLE IF NOT EXISTS amenities (
  id   INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  icon VARCHAR(50) DEFAULT 'bi-check-circle'
) ENGINE=InnoDB;

-- ============================================================
-- Table: property_amenities (junction)
-- ============================================================
CREATE TABLE IF NOT EXISTS property_amenities (
  property_id INT NOT NULL,
  amenity_id  INT NOT NULL,
  PRIMARY KEY (property_id, amenity_id),
  FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
  FOREIGN KEY (amenity_id)  REFERENCES amenities(id)  ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- Table: interested_users
-- ============================================================
CREATE TABLE IF NOT EXISTS interested_users (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  property_id INT NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_interest (user_id, property_id),
  FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
  FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Amenities
INSERT INTO amenities (name, icon) VALUES
  ('WiFi',            'bi-wifi'),
  ('AC',              'bi-thermometer-snow'),
  ('Meals Included',  'bi-cup-hot'),
  ('Laundry',         'bi-basket'),
  ('Parking',         'bi-car-front'),
  ('CCTV Security',   'bi-camera-video'),
  ('Power Backup',    'bi-battery-charging'),
  ('Study Room',      'bi-book'),
  ('Gym',             'bi-heart-pulse'),
  ('Hot Water',       'bi-droplet-half');

-- Properties (10 PGs across 5 cities)
INSERT INTO properties (name, city, address, price, gender, rating, description, image) VALUES
(
  'Sunrise PG for Boys',
  'Bangalore',
  '14, 3rd Cross, Koramangala, Bangalore - 560034',
  8500.00, 'male', 4.5,
  'Sunrise PG offers premium accommodation for boys with fully furnished rooms, high-speed WiFi, and home-cooked meals. Located in the heart of Koramangala, walking distance from tech parks and metro station.',
  'https://images.unsplash.com/photo-1555854877-bab0e564b8d5?w=800'
),
(
  'Green Valley Girls PG',
  'Bangalore',
  '27, Indiranagar 100ft Road, Bangalore - 560038',
  9200.00, 'female', 4.7,
  'An exclusive ladies PG with 24/7 security, biometric entry, and spacious rooms. Green Valley provides a safe, homely environment with nutritious meals and housekeeping.',
  'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800'
),
(
  'Urban Nest Co-Living',
  'Bangalore',
  '5, HSR Layout Sector 2, Bangalore - 560102',
  11000.00, 'any', 4.3,
  'A modern co-living space designed for young professionals and students. Urban Nest features community events, rooftop lounge, and premium amenities in a vibrant neighbourhood.',
  'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800'
),
(
  'Scholar''s Inn Boys PG',
  'Pune',
  '88, FC Road, Shivajinagar, Pune - 411005',
  7000.00, 'male', 4.2,
  'Perfect for engineering and MBA students near Fergusson College. Scholar''s Inn provides a dedicated study room, library access, and reliable internet for academic excellence.',
  'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800'
),
(
  'Lotus Ladies Hostel',
  'Pune',
  '12, Kothrud Road, Pune - 411038',
  8000.00, 'female', 4.6,
  'Lotus Ladies Hostel is a premium women-only accommodation with CCTV surveillance, warden facility, and hygienic mess. Close to Symbiosis and MIT Pune.',
  'https://images.unsplash.com/photo-1484154218962-a197022b5858?w=800'
),
(
  'Metro PG Hyderabad',
  'Hyderabad',
  '45, Madhapur, Hi-Tech City, Hyderabad - 500081',
  9500.00, 'any', 4.4,
  'Strategically located near Cyber Towers and HITEC City, Metro PG is ideal for IT interns and students. Features include gym, power backup, and air-conditioned rooms.',
  'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800'
),
(
  'Nest Boys PG',
  'Hyderabad',
  '21, Kukatpally Housing Board, Hyderabad - 500072',
  6500.00, 'male', 4.0,
  'Budget-friendly boys PG in Kukatpally with clean rooms, fast WiFi, and daily meals. Close to JNTU and several engineering colleges.',
  'https://images.unsplash.com/photo-1493809842364-78817add7ffb?w=800'
),
(
  'Prestige Ladies PG',
  'Chennai',
  '3, T Nagar, Pondy Bazaar, Chennai - 600017',
  7500.00, 'female', 4.5,
  'Prestige Ladies PG is a top-rated hostel in T Nagar with excellent connectivity. Offers vegetarian meals, housekeeping, and in-house laundry for a stress-free stay.',
  'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800'
),
(
  'The Scholar House',
  'Delhi',
  '67, Lajpat Nagar II, New Delhi - 110024',
  10500.00, 'any', 4.8,
  'The Scholar House is Delhi''s most sought-after co-living space. With a rooftop café, collaborative workspaces, and metro access at the doorstep, it redefines student living.',
  'https://images.unsplash.com/photo-1524758631624-e2822e304c36?w=800'
),
(
  'Capital Boys PG',
  'Delhi',
  '22, Karol Bagh, New Delhi - 110005',
  8800.00, 'male', 4.1,
  'Capital Boys PG offers spacious double and triple occupancy rooms near Karol Bagh metro. Includes parking, power backup, and meals with North Indian cuisine.',
  'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800'
);

-- property_amenities mappings
INSERT INTO property_amenities (property_id, amenity_id) VALUES
-- Sunrise PG (1)
(1,1),(1,2),(1,3),(1,6),(1,7),(1,10),
-- Green Valley (2)
(2,1),(2,2),(2,3),(2,4),(2,6),(2,7),(2,10),
-- Urban Nest (3)
(3,1),(3,2),(3,3),(3,4),(3,5),(3,6),(3,7),(3,8),(3,9),(3,10),
-- Scholar's Inn (4)
(4,1),(4,3),(4,6),(4,8),(4,10),
-- Lotus Ladies (5)
(5,1),(5,2),(5,3),(5,4),(5,6),(5,10),
-- Metro PG (6)
(6,1),(6,2),(6,5),(6,6),(6,7),(6,9),(6,10),
-- Nest Boys (7)
(7,1),(7,3),(7,6),(7,10),
-- Prestige Ladies (8)
(8,1),(8,3),(8,4),(8,6),(8,10),
-- The Scholar House (9)
(9,1),(9,2),(9,3),(9,4),(9,5),(9,6),(9,7),(9,8),(9,9),(9,10),
-- Capital Boys (10)
(10,1),(10,2),(10,3),(10,5),(10,6),(10,7),(10,10);

-- Demo user (password: demo1234)
INSERT INTO users (name, email, password, phone) VALUES
('Demo Student', 'demo@pgfinder.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210');
