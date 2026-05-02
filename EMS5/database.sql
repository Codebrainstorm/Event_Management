-- Event Management System Database

-- Create Database
CREATE DATABASE IF NOT EXISTS event_management_system;
USE event_management_system;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Events Table
CREATE TABLE IF NOT EXISTS events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT NOT NULL,
    event_date DATETIME NOT NULL,
    location VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    image VARCHAR(500),
    tier ENUM('Ordinary', 'Premium') DEFAULT 'Ordinary',
    agenda LONGTEXT,
    requirements LONGTEXT,
    price DECIMAL(10,2) DEFAULT 0.00,
    created_by INT NOT NULL,
    active TINYINT(1) DEFAULT 1,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Registrations Table
CREATE TABLE IF NOT EXISTS registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    amount_paid DECIMAL(10,2) DEFAULT 0.00,
    payment_status VARCHAR(50) DEFAULT 'Pending',
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, user_id)
);

-- Bookings Table (for Birthday Bookings & Other Services)
CREATE TABLE IF NOT EXISTS bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_type VARCHAR(100) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    event_date DATE NOT NULL,
    guest_count INT,
    special_requests LONGTEXT,
    price DECIMAL(10,2) DEFAULT 0.00,
    tier ENUM('Ordinary', 'Premium') DEFAULT 'Ordinary',
    amount_paid DECIMAL(10,2) DEFAULT 0.00,
    payment_status VARCHAR(50) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'Pending'
);

-- Create earnings view
CREATE OR REPLACE VIEW earnings_summary AS
SELECT 
    'Booking Revenue' AS source,
    COUNT(*) AS total_transactions,
    SUM(amount_paid) AS total_earned,
    SUM(CASE WHEN tier = 'Premium' THEN amount_paid ELSE 0 END) AS premium_earned,
    SUM(CASE WHEN tier = 'Ordinary' THEN amount_paid ELSE 0 END) AS ordinary_earned
FROM bookings
WHERE payment_status = 'Paid';

-- Create Indexes for Better Performance
CREATE INDEX idx_events_created_by ON events(created_by);
CREATE INDEX idx_events_event_date ON events(event_date);
CREATE INDEX idx_events_active ON events(active);
CREATE INDEX idx_registrations_event_id ON registrations(event_id);
CREATE INDEX idx_registrations_user_id ON registrations(user_id);
CREATE INDEX idx_bookings_event_type ON bookings(event_type);
CREATE INDEX idx_bookings_event_date ON bookings(event_date);
CREATE INDEX idx_users_email ON users(email);

-- Insert Sample Data (Optional)
INSERT INTO users (name, email, phone, password, created_at) VALUES 
('Admin User', 'admin@example.com', '+1 (555) 123-4567', '$2y$10$C0odiDXKDDlCdhg3FBpfLOVG87Rh6gj5p7xrrWjFmLNj3IFR7BYfe', NOW()),
('John Doe', 'john@example.com', '+1 (555) 987-6543', '$2y$10$C0odiDXKDDlCdhg3FBpfLOVG87Rh6gj5p7xrrWjFmLNj3IFR7BYfe', NOW()),
('Test User', 'test@example.com', '555-1234', '$2y$10$C0odiDXKDDlCdhg3FBpfLOVG87Rh6gj5p7xrrWjFmLNj3IFR7BYfe', NOW());

INSERT INTO events (title, description, event_date, location, category, image, tier, price, created_by, active, status) VALUES 
('Web Development Workshop', 'Learn modern web development with PHP and MySQL', '2026-03-15 10:00:00', 'New York Convention Center', 'Workshop', 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=600&h=400&fit=crop', 'Ordinary', 49.99, 1, 1, 'approved'),
('Tech Conference 2026', 'Annual technology conference featuring industry leaders', '2026-04-20 09:00:00', 'San Francisco Civic Center', 'Conference', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=600&h=400&fit=crop', 'Premium', 199.99, 1, 1, 'approved'),
('Startup Meetup', 'Connect with fellow entrepreneurs and startup founders', '2026-03-10 18:00:00', 'Tech Hub Downtown', 'Meetup', 'https://images.unsplash.com/photo-1528605248644-14dd04022da1?w=600&h=400&fit=crop', 'Ordinary', 0.00, 1, 1, 'approved');

-- Sample Bookings Data
INSERT INTO bookings (event_type, customer_name, email, phone, event_date, guest_count, special_requests, price, tier, amount_paid, payment_status, status) VALUES 
('Wedding', 'John Smith', 'john.smith@email.com', '555-0101', '2026-03-15', 150, 'Vegetarian meals for 20 guests', 5000.00, 'Premium', 0.00, 'Pending', 'Pending'),
('Birthday Party', 'Sarah Johnson', 'sarah.j@email.com', '555-0102', '2026-03-20', 50, 'Need DJ service', 1500.00, 'Ordinary', 1500.00, 'Paid', 'Approved'),
('Corporate Event', 'Mike Brown', 'mbrown@company.com', '555-0103', '2026-02-28', 200, 'Conference setup with AV equipment', 3000.00, 'Premium', 3000.00, 'Paid', 'Completed'),
('Anniversary', 'Emma Davis', 'emma.d@email.com', '555-0104', '2026-03-25', 75, 'Romantic candlelit dinner setup', 800.00, 'Ordinary', 0.00, 'Pending', 'Pending'),
('Graduation Party', 'Lisa Wilson', 'lisa.w@email.com', '555-0105', '2026-04-10', 100, 'Picture booth rental', 1500.00, 'Ordinary', 1500.00, 'Paid', 'Approved'),
('Baby Shower', 'Jessica Martinez', 'jess.m@email.com', '555-0106', '2026-03-30', 40, 'Pastel decorations, tea service', 1500.00, 'Ordinary', 0.00, 'Pending', 'Pending'),
('Corporate Meeting', 'Robert Taylor', 'rtaylor@corp.com', '555-0107', '2026-02-27', 80, 'Projector and sound system', 3000.00, 'Premium', 3000.00, 'Paid', 'Completed'),
('Wedding Reception', 'Amanda White', 'amanda.w@email.com', '555-0108', '2026-04-05', 180, 'Buffet style, live band', 5000.00, 'Premium', 0.00, 'Pending', 'Cancelled'),
('Reunion Dinner', 'David Green', 'dgreen@email.com', '555-0109', '2026-03-22', 60, 'Themed decorations required', 800.00, 'Ordinary', 800.00, 'Paid', 'Approved'),
('Holiday Party', 'Nancy King', 'nking@email.com', '555-0110', '2026-03-18', 120, 'Christmas lights and DJ', 800.00, 'Ordinary', 0.00, 'Pending', 'Pending');

-- Display confirmation
SELECT '✅ Database created successfully!' AS status;
SELECT '📊 Tables and views created:' AS info;
SHOW FULL TABLES;
