-- Equipment Management Tables
-- Run this SQL script to create the equipment and maintenance tables

CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    model VARCHAR(255),
    serial_number VARCHAR(255),
    purchase_date DATE,
    purchase_price DECIMAL(10,2),
    location VARCHAR(255),
    status ENUM('active', 'inactive', 'maintenance', 'retired') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS equipment_maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    maintenance_type VARCHAR(100) NOT NULL,
    description TEXT,
    scheduled_date DATE,
    completed_date DATE,
    cost DECIMAL(10,2),
    performed_by VARCHAR(255),
    notes TEXT,
    status ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE
);

-- Insert some sample equipment data
INSERT INTO equipment (name, type, model, serial_number, purchase_date, purchase_price, location, status, notes) VALUES
('Generator 1', 'Generator', 'Cummins C50D5', 'GEN001', '2023-01-15', 25000.00, 'Main Building', 'active', 'Primary backup generator'),
('Generator 2', 'Generator', 'Cummins C25D5', 'GEN002', '2023-03-20', 15000.00, 'Warehouse', 'active', 'Secondary backup generator'),
('Water Pump', 'Pump', 'Grundfos SP5', 'PMP001', '2023-02-10', 5000.00, 'Pump Station', 'active', 'Main water supply pump'),
('Forklift', 'Vehicle', 'Toyota 5FG25', 'FL001', '2022-11-05', 18000.00, 'Warehouse', 'active', 'Electric forklift'),
('Excavator', 'Equipment', 'CAT 320', 'EXC001', '2022-08-15', 85000.00, 'Construction Site', 'maintenance', 'Under maintenance');

-- Insert some sample maintenance records
INSERT INTO equipment_maintenance (equipment_id, maintenance_type, description, scheduled_date, status, notes) VALUES
(1, 'Routine Check', 'Monthly maintenance check', '2024-02-15', 'scheduled', 'Check oil levels and filters'),
(2, 'Oil Change', 'Change engine oil and filters', '2024-02-10', 'scheduled', 'Use synthetic oil'),
(3, 'Inspection', 'Annual safety inspection', '2024-03-01', 'scheduled', 'Check seals and pressure'),
(4, 'Battery Check', 'Check battery charge and connections', '2024-02-20', 'scheduled', 'Replace if needed'),
(5, 'Repair', 'Fix hydraulic leak', '2024-02-05', 'in_progress', 'Hydraulic cylinder replacement');