CREATE DATABASE IF NOT EXISTS barbershop_db;
USE barbershop_db;

CREATE TABLE roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (name) VALUES ('customer'), ('barber'), ('admin');

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL DEFAULT 1,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(255),
  phone VARCHAR(30),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

CREATE TABLE services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  duration_minutes INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  active TINYINT(1) DEFAULT 1
);

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  barber_id INT NOT NULL,
  service_id INT NOT NULL,
  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,
  status VARCHAR(20) DEFAULT 'booked',
  notes TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (barber_id) REFERENCES users(id),
  FOREIGN KEY (service_id) REFERENCES services(id)
);

ALTER TABLE appointments 
ADD CONSTRAINT chk_appointment_status 
CHECK (status IN ('booked','confirmed','completed','cancelled'));

CREATE INDEX idx_barber_time ON appointments(barber_id, start_at, end_at);

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  appointment_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method VARCHAR(50),
  status VARCHAR(20) DEFAULT 'pending',
  paid_at DATETIME,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);

ALTER TABLE payments 
ADD CONSTRAINT chk_payment_status 
CHECK (status IN ('pending','paid','failed'));

INSERT INTO users (role_id, email, password_hash, full_name, phone) VALUES
(3, 'admin@barber.com', '$2y$10$REPLACE_WITH_HASH', 'Admin User', '+000000'),
(2, 'barber1@barber.com', '$2y$10$REPLACE_WITH_HASH', 'Barber One', '+000001'),
(1, 'customer1@example.com', '$2y$10$REPLACE_WITH_HASH', 'Customer One', '+000002');

INSERT INTO services (name, description, duration_minutes, price) VALUES
('Standard Haircut', 'Classic haircut with clippers and scissors', 30, 15.00),
('Beard Trim', 'Precision beard shaping', 20, 8.00),
('Full Grooming', 'Haircut + beard + styling', 60, 40.00);