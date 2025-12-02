CREATE DATABASE barbershop_db;
GO

USE barbershop_db;
GO

CREATE TABLE roles (
  id INT IDENTITY(1,1) PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO roles (name) VALUES ('customer'), ('barber'), ('admin');

CREATE TABLE users (
  id INT IDENTITY(1,1) PRIMARY KEY,
  role_id INT NOT NULL DEFAULT 1,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  full_name VARCHAR(255),
  phone VARCHAR(30),
  created_at DATETIME DEFAULT GETDATE(),
  updated_at DATETIME DEFAULT GETDATE(),
  FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Add trigger for updated_at in SQL Server
GO
CREATE TRIGGER trg_users_update
ON users
AFTER UPDATE
AS
BEGIN
  UPDATE users
  SET updated_at = GETDATE()
  FROM users u
  INNER JOIN inserted i ON u.id = i.id
END
GO

CREATE TABLE services (
  id INT IDENTITY(1,1) PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  duration_minutes INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  active BIT DEFAULT 1
);

CREATE TABLE appointments (
  id INT IDENTITY(1,1) PRIMARY KEY,
  customer_id INT NOT NULL,
  barber_id INT NOT NULL,
  service_id INT NOT NULL,
  start_at DATETIME NOT NULL,
  end_at DATETIME NOT NULL,
  status VARCHAR(20) CHECK (status IN ('booked','confirmed','completed','cancelled')) DEFAULT 'booked',
  notes TEXT,
  created_at DATETIME DEFAULT GETDATE(),
  updated_at DATETIME DEFAULT GETDATE(),
  FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (barber_id) REFERENCES users(id),
  FOREIGN KEY (service_id) REFERENCES services(id),
);

CREATE INDEX idx_barber_time ON appointments(barber_id, start_at, end_at);
GO

-- Trigger for appointments updated_at
CREATE TRIGGER trg_appointments_update
ON appointments
AFTER UPDATE
AS
BEGIN
  UPDATE appointments
  SET updated_at = GETDATE()
  FROM appointments a
  INNER JOIN inserted i ON a.id = i.id
END
GO

CREATE TABLE payments (
  id INT IDENTITY(1,1) PRIMARY KEY,
  appointment_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method VARCHAR(50),
  status VARCHAR(20) CHECK (status IN ('pending','paid','failed')) DEFAULT 'pending',
  paid_at DATETIME,
  created_at DATETIME DEFAULT GETDATE(),
  FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE
);

-- Sample data
INSERT INTO users (role_id, email, password_hash, full_name, phone) VALUES
(3, 'admin@barber.com', '$2y$10$REPLACE_WITH_HASH', 'Admin User', '+000000'),
(2, 'barber1@barber.com', '$2y$10$REPLACE_WITH_HASH', 'Barber One', '+000001'),
(1, 'customer1@example.com', '$2y$10$REPLACE_WITH_HASH', 'Customer One', '+000002');

INSERT INTO services (name, description, duration_minutes, price) VALUES
('Standard Haircut', 'Classic haircut with clippers and scissors', 30, 15.00),
('Beard Trim', 'Precision beard shaping', 20, 8.00),

('Full Grooming', 'Haircut + beard + styling', 60, 40.00);
