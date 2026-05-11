-- ============================================
-- ELITE HRMS - MASTER DATABASE SCHEMA
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS hr_payroll_system;
USE hr_payroll_system;

-- 1. DROP EXISTING ASSETS
DROP TABLE IF EXISTS payroll_deductions;
DROP TABLE IF EXISTS payroll_records;
DROP TABLE IF EXISTS leave_requests;
DROP TABLE IF EXISTS feedback;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS login_logs;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS deductions;
DROP TABLE IF EXISTS salary_grades;
DROP TABLE IF EXISTS system_settings;
DROP VIEW IF EXISTS pending_leave_requests;
DROP VIEW IF EXISTS monthly_payroll_summary;

-- 2. SYSTEM CONFIGURATION
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO system_settings (setting_key, setting_value) VALUES
('company_name', 'Elite HRMS Solutions'),
('currency_symbol', '$'),
('system_email', 'hr@example.com'),
('allow_self_registration', '1'),
('primary_color', '#6366f1'),
('accent_color', '#f472b6'),
('default_leave_balance', '15');

-- 3. ORGANIZATIONAL STRUCTURE
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    manager_id INT NULL,
    budget DECIMAL(12, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE salary_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grade_level VARCHAR(20) NOT NULL UNIQUE,
    base_salary DECIMAL(10, 2) NOT NULL,
    housing_allowance DECIMAL(10, 2) DEFAULT 0,
    transport_allowance DECIMAL(10, 2) DEFAULT 0,
    medical_allowance DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. WORKFORCE DIRECTORY
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    department_id INT,
    position VARCHAR(100),
    salary_grade_id INT,
    dob DATE NULL,
    hire_date DATE,
    leave_balance INT DEFAULT 15,
    role ENUM('admin', 'employee', 'hr') DEFAULT 'employee',
    profile_pic VARCHAR(255) NULL,
    ui_accent VARCHAR(20) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (salary_grade_id) REFERENCES salary_grades(id) ON DELETE SET NULL
);

-- Update department managers
ALTER TABLE departments ADD FOREIGN KEY (manager_id) REFERENCES employees(id) ON DELETE SET NULL;

-- 5. OPERATIONS & WORKFLOWS
CREATE TABLE leave_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type ENUM('annual', 'sick', 'unpaid', 'maternity', 'paternity') DEFAULT 'annual',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days INT NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending',
    approver_id INT NULL,
    approval_reason TEXT,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_date TIMESTAMP NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (approver_id) REFERENCES employees(id) ON DELETE SET NULL
);

CREATE TABLE payroll_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    payroll_month DATE NOT NULL,
    base_salary DECIMAL(10, 2) NOT NULL,
    allowances DECIMAL(10, 2) DEFAULT 0,
    total_deductions DECIMAL(10, 2) DEFAULT 0,
    bonus DECIMAL(10, 2) DEFAULT 0,
    net_salary DECIMAL(10, 2) NOT NULL,
    status ENUM('draft', 'processed', 'paid') DEFAULT 'draft',
    payment_date DATE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

CREATE TABLE deductions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('percentage', 'fixed') DEFAULT 'fixed',
    amount DECIMAL(10, 2) NOT NULL,
    is_mandatory BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE payroll_deductions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payroll_id INT NOT NULL,
    deduction_id INT NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (payroll_id) REFERENCES payroll_records(id) ON DELETE CASCADE,
    FOREIGN KEY (deduction_id) REFERENCES deductions(id) ON DELETE CASCADE
);

-- 6. ENGAGEMENT & COMMUNICATION
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE CASCADE
);

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'low',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES employees(id) ON DELETE SET NULL
);

CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'reviewed', 'resolved') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- 7. SECURITY & AUDIT
CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE SET NULL
);

CREATE TABLE login_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status ENUM('success', 'failed') DEFAULT 'success',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- 8. INTELLIGENCE VIEWS
CREATE VIEW pending_leave_requests AS
SELECT 
    lr.*,
    e.full_name as employee_name,
    e.employee_id as emp_code,
    d.name as department_name
FROM leave_requests lr
JOIN employees e ON lr.employee_id = e.id
JOIN departments d ON e.department_id = d.id
WHERE lr.status = 'pending'
ORDER BY lr.request_date ASC;

CREATE VIEW monthly_payroll_summary AS
SELECT 
    DATE_FORMAT(payroll_month, '%Y-%m') as month,
    COUNT(*) as total_employees,
    SUM(base_salary) as total_base,
    SUM(allowances) as total_allowances,
    SUM(total_deductions) as total_deductions,
    SUM(net_salary) as total_net
FROM payroll_records
GROUP BY DATE_FORMAT(payroll_month, '%Y-%m');

-- 9. MASTER SAMPLE DATA
INSERT INTO salary_grades (grade_level, base_salary, housing_allowance, transport_allowance, medical_allowance) VALUES
('Grade 1', 30000, 5000, 2000, 3000),
('Grade 2', 45000, 7000, 3000, 4000),
('Grade 3', 60000, 10000, 5000, 5000),
('Grade 4', 80000, 15000, 7000, 7000),
('Grade 5', 100000, 20000, 10000, 10000);

INSERT INTO deductions (name, type, amount, is_mandatory) VALUES
('PAYE Tax', 'percentage', 15, TRUE),
('Pension Fund', 'percentage', 7.5, TRUE),
('NHIF', 'fixed', 500, TRUE),
('NSSF', 'fixed', 400, TRUE);

INSERT INTO departments (name, code, description) VALUES
('Human Resources', 'HR', 'Employee relations and recruitment'),
('Information Technology', 'IT', 'Software development and infrastructure'),
('Finance', 'FIN', 'Accounting and financial management'),
('Marketing', 'MKT', 'Brand management and advertising');

INSERT INTO employees (employee_id, username, password, email, full_name, role, leave_balance, hire_date) VALUES
('EMP001', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@hrsystem.com', 'System Administrator', 'admin', 15, CURDATE());

UPDATE departments SET manager_id = 1 WHERE id = 1;

INSERT INTO employees (employee_id, username, password, email, full_name, department_id, position, salary_grade_id, leave_balance, role, hire_date) VALUES
('EMP002', 'john.doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@company.com', 'John Doe', 2, 'Senior Developer', 3, 12, 'employee', '2023-01-15'),
('EMP003', 'jane.smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jane@company.com', 'Jane Smith', 1, 'HR Manager', 2, 10, 'hr', '2022-06-01');

SET FOREIGN_KEY_CHECKS = 1;