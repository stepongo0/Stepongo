-- Database: stepongo_new_db (Ensure this database exists in phpMyAdmin)
-- You can create it manually if it doesn't exist:
CREATE DATABASE IF NOT EXISTS stepongo_new_db; -- Changed database name here
-- Then select it before running these commands:
USE stepongo_new_db; -- Changed database name here

-- Table: users (for the User class)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL, -- Store hashed passwords, NEVER plain text
    `email` VARCHAR(255) UNIQUE,
    `role` ENUM('admin', 'sub_admin', 'labour', 'client', 'user') DEFAULT 'user',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: labours (for the Labour class)
CREATE TABLE IF NOT EXISTS `labours` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20),
    `address` TEXT,
    `daily_wage` DECIMAL(10, 2) DEFAULT 0.00,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table: payments (for payments summary)
CREATE TABLE IF NOT EXISTS `payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT,
    `amount` DECIMAL(10, 2) NOT NULL,
    `payment_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'paid', 'overdue') DEFAULT 'pending',
    `notes` TEXT,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: inventory (for inventory summary)
CREATE TABLE IF NOT EXISTS `inventory` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `item_name` VARCHAR(255) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 0,
    `unit` VARCHAR(50),
    `price_per_unit` DECIMAL(10, 2) DEFAULT 0.00,
    `last_updated` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: support_tickets (for support tickets summary)
CREATE TABLE IF NOT EXISTS `support_tickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `status` ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
    `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `created_by_user_id` INT,
    `assigned_to_user_id` INT, -- Could be a sub_admin or specific support agent
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`assigned_to_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: labour_attendance (for recent attendance)
CREATE TABLE IF NOT EXISTS `labour_attendance` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `labour_id` INT NOT NULL,
    `project_id` INT, -- Optional: link attendance to a specific project
    `check_in` DATETIME NOT NULL,
    `check_out` DATETIME,
    `status` ENUM('present', 'absent', 'half_day') DEFAULT 'present',
    `notes` TEXT,
    FOREIGN KEY (`labour_id`) REFERENCES `labours`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create the labour_documents table
CREATE TABLE IF NOT EXISTS labour_documents (
    document_id INT AUTO_INCREMENT PRIMARY KEY,
    labour_id INT NOT NULL,
    document_type VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Verified', 'Rejected') DEFAULT 'Pending',
    admin_comments TEXT,
    upload_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_timestamp TIMESTAMP NULL DEFAULT NULL,
    updated_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table for Assignments
CREATE TABLE IF NOT EXISTS assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    labour_id INT NOT NULL,
    project_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    task_description TEXT NOT NULL,
    assigned_supervisor VARCHAR(255),
    status ENUM('Assigned', 'In Progress', 'Completed', 'Cancelled') DEFAULT 'Assigned',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (labour_id) REFERENCES labours(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

-- Create the new database if it doesn't exist
CREATE DATABASE IF NOT EXISTS stepongo_dev_db;

-- Use the new database
USE stepongo_dev_db;

-- Create the new 'developer_projects' table
CREATE TABLE IF NOT EXISTS developer_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_title VARCHAR(255) NOT NULL,
    location VARCHAR(255) NOT NULL,
    description TEXT,
    timeline VARCHAR(100),
    cost DECIMAL(10, 2),
    contractor_name VARCHAR(255),
    status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending'
);

CREATE TABLE safety_guidelines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    language_code VARCHAR(10) NOT NULL, -- e.g., 'en' for English, 'hi' for Hindi
    category VARCHAR(100) NOT NULL,
    video_file VARCHAR(255) DEFAULT NULL, -- Path to uploaded video file
    video_link VARCHAR(255) DEFAULT NULL, -- YouTube or other video link
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS company_documents (
    id INT(11) NOT NULL AUTO_INCREMENT,
    company_id VARCHAR(255) NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    document_type VARCHAR(255) NOT NULL,
    document_file VARCHAR(255) NOT NULL,
    verification_status ENUM('Pending','Verified','Rejected') DEFAULT 'Pending',
    remarks TEXT DEFAULT NULL,
    upload_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `company_id` (`company_id`),
    KEY `company_name` (`company_name`),
    KEY `verification_status` (`verification_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `support_tickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `user_type` ENUM('company', 'worker') NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `issue_title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `priority` ENUM('Low', 'Medium', 'High') NOT NULL,
    `attachment` VARCHAR(255) NULL,
    `status` ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    `admin_reply` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- SQL Code for creating the 'safety_protocols' table and inserting sample data

-- Create Database (Optional, if you already have one, skip this)
CREATE DATABASE IF NOT EXISTS `stepongo_new_db`;
USE `stepongo_new_db`;

-- Table structure for `safety_protocols`
-- This table will store safety protocols and guidelines for companies.
CREATE TABLE IF NOT EXISTS `safety_protocols` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `company_name` VARCHAR(255) NOT NULL,
    `protocol_title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `applicable_law` VARCHAR(255) NOT NULL COMMENT 'Relevant Indian law/act (e.g., Factories Act, 1948)',
    `last_updated` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample data into `safety_protocols`
-- These are example entries to get you started.
INSERT INTO `safety_protocols` (`company_name`, `protocol_title`, `description`, `applicable_law`) VALUES
('Tech Solutions Pvt. Ltd.', 'Fire Safety Drills', 'Conduct quarterly fire safety drills and ensure all exits are clear. Maintain fire extinguishers as per IS 2190.', 'Factories Act, 1948'),
('Manufacturing Co. India', 'Machine Guarding Standards', 'All moving parts of machinery must be adequately guarded to prevent accidental contact. Refer to IS 14489 for safety standards.', 'Factories Act, 1948'),
('Logistics Hub Ltd.', 'Hazardous Material Handling', 'Proper labeling, storage, and handling procedures for hazardous materials as per Hazardous Waste (Management, Handling and Transboundary Movement) Rules, 2016.', 'Environment (Protection) Act, 1986'),
('IT Services India', 'Ergonomic Workstation Setup', 'Provide ergonomic chairs, adjustable desks, and proper monitor height to prevent musculoskeletal disorders. Follow OSH guidelines.', 'Model Rules under OSHA (Occupational Safety and Health) Code, 2020 (Proposed)');


-- Insert sample data into the new 'developer_projects' table
INSERT INTO developer_projects (project_title, location, description, timeline, cost, contractor_name, status, approval_status) VALUES
('New Office Building - V2', 'Downtown City', 'Construction of a 10-story office building with modern amenities.', '12 months', 15500000.00, 'BuildRight Inc. V2', 'In Progress', 'Approved'),
('Residential Complex Phase 3', 'Suburbia Heights West', 'Development of 75 new residential units, including townhouses and apartments.', '20 months', 27000000.00, 'HomeBuilders Ltd. V2', 'Pending', 'Pending'),
('City Metro Line Extension', 'East District', 'Extension of the existing metro line by 10km with 5 new stations.', '36 months', 80000000.00, 'UrbanTransit Solutions', 'In Progress', 'Approved'),
('Public Library Modernization', 'Historic Quarter', 'Modernization of the central public library, adding digital resources and community spaces.', '8 months', 1200000.00, 'Bookworm Builders', 'Completed', 'Approved'),
('Water Treatment Plant Upgrade', 'Industrial Zone', 'Upgrade of the main water treatment plant to increase capacity and efficiency.', '15 months', 10000000.00, 'AquaTech Engineering', 'Pending', 'Rejected');


-
;

-- Optional: Add some sample data to test
-- IMPORTANT: Replace the password hashes below with actual bcrypt hashes generated from real passwords.
-- Example of generating a hash in PHP: echo password_hash('your_secret_password', PASSWORD_DEFAULT);

INSERT INTO `users` (`username`, `password_hash`, `email`, `role`) VALUES
('admin_user', '$2y$10$fG6m9t1L0s2V7l4H8a5R3O6K7J8I9E0C1B2A3Z4Y5X6W7U8T9S0Q1P2N3M4L5K6J', 'admin@example.com', 'admin'),
('sub_admin_user', '$2y$10$hK2u8w5X1Z9P7Q4D3S6F7G8H9J0K1L2M3N4B5V6C7X8Z9A0S1D2F3G4H5J6K7L', 'subadmin@example.com', 'sub_admin'),
('test_labour', '$2y$10$eR3t7y0U1I2O3P4A5S6D7F8G9H0J1K2L3M4N5B6V7C8X9Z0Q1W2E3R4T5Y6U7I8O', 'labour@example.com', 'labour');

INSERT INTO `labours` (`name`, `phone`, `daily_wage`) VALUES
('John Doe', '123-456-7890', 500.00),
('Jane Smith', '987-654-3210', 550.00),
('Mike Johnson', '555-123-4567', 480.00);

INSERT INTO `projects` (`project_name`, `client_name`, `start_date`, `status`) VALUES
('Office Renovation', 'ABC Corp', '2025-01-15', 'ongoing'),
('Warehouse Expansion', 'XYZ Ltd', '2024-11-01', 'completed'),
('New Building Construction', 'PQR Inc', '2025-06-01', 'pending');

INSERT INTO `payments` (`project_id`, `amount`, `status`) VALUES
(1, 1500.00, 'pending'),
(2, 5000.00, 'paid'),
(3, 2000.00, 'pending');

INSERT INTO `inventory` (`item_name`, `quantity`, `unit`, `price_per_unit`) VALUES
('Cement Bags', 100, 'bags', 5.50),
('Steel Rods', 50, 'pieces', 12.00);

INSERT INTO `support_tickets` (`subject`, `description`, `status`, `created_by_user_id`, `assigned_to_user_id`) VALUES
('Login Issue', 'Cannot log in to the system.', 'open', 1, 2),
('Payment Query', 'Client asking about invoice #123.', 'in_progress', 3, 2);

INSERT INTO `labour_attendance` (`labour_id`, `project_id`, `check_in`, `status`) VALUES
(1, 1, '2025-06-28 08:00:00', 'present'),
(2, 1, '2025-06-28 08:15:00', 'present'),
(3, NULL, '2025-06-28 09:00:00', 'absent');

INSERT INTO labour_documents (labour_id, document_type, file_path, status) VALUES
(101, 'Aadhar', 'uploads/aadhar_101.pdf', 'Pending'),
(102, 'PAN', 'uploads/pan_102.jpg', 'Verified'),
(101, 'Certificate', 'uploads/cert_101.png', 'Rejected'),
(103, 'Aadhar', 'uploads/aadhar_103.pdf', 'Pending');

INSERT INTO assignments (labour_id, project_id, start_date, end_date, task_description, assigned_supervisor, status) VALUES
(1, 1, '2023-01-20', '2023-02-15', 'Install new flooring in main office area.', 'Supervisor A', 'In Progress'),
(2, 1, '2023-02-01', '2023-02-28', 'Paint all interior walls.', 'Supervisor B', 'Assigned'),
(1, 2, '2023-04-05', '2023-06-30', 'Construct new foundation for warehouse extension.', 'Supervisor A', 'In Progress'),
(3, 3, '2023-07-10', '2023-08-20', 'Lay brickwork for ground floor.', 'Supervisor C', 'Assigned')
ON DUPLICATE KEY UPDATE task_description=task_description; -- Prevents errors if run multiple times

INSERT INTO company_documents (company_id, company_name, document_type, document_file, verification_status, remarks) VALUES
('COMP001', 'Acme Corp', 'GST Certificate', 'doc_6694e9f78a1b2c3d4e5f6a7b.pdf', 'Pending', NULL),
('COMP002', 'Beta Solutions', 'PAN Card', 'doc_7788a1b2c3d4e5f6a7b8c9d0.jpg', 'Verified', 'All details matched.'),
('COMP003', 'Gamma Innovations', 'Address Proof', 'doc_8899b1c2d3e4f5a6b7c8d9e0.png', 'Rejected', 'Address mismatch with records.'),
('COMP001', 'Acme Corp', 'Company Registration', 'doc_9900c1d2e3f4a5b6c7d8e9f0.docx', 'Pending', NULL),
('COMP004', 'Delta Dynamics', 'Bank Statement', 'doc_1122d3e4f5a6b7c8d9e0f1a2.pdf', 'Pending', NULL);