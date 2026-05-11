<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

if (!hr_is_admin()) {
    die("Unauthorized access.");
}

$filename = "employee_directory_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Header
fputcsv($output, ['Employee ID', 'Full Name', 'Email', 'Department', 'Position', 'Salary Grade', 'Hire Date', 'Status', 'Role']);

// Data
$query = "SELECT e.*, d.name as department_name, sg.grade_level 
          FROM employees e 
          LEFT JOIN departments d ON e.department_id = d.id 
          LEFT JOIN salary_grades sg ON e.salary_grade_id = sg.id 
          ORDER BY e.employee_id ASC";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        $row['employee_id'],
        $row['full_name'],
        $row['email'],
        $row['department_name'] ?? 'N/A',
        $row['position'],
        $row['grade_level'] ?? 'N/A',
        $row['hire_date'],
        $row['is_active'] ? 'Active' : 'Inactive',
        strtoupper($row['role'])
    ]);
}

fclose($output);
exit();
?>