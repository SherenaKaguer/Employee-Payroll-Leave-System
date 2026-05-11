<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

if (!hr_is_admin()) {
    die("Unauthorized access.");
}

$filename = "payroll_summary_" . date('Y-m-d') . ".csv";

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// Header
fputcsv($output, ['Month Period', 'Total Employees', 'Gross Base Salary', 'Total Allowances', 'Total Deductions', 'Net Paid Amount']);

// Data
$query = "SELECT * FROM monthly_payroll_summary ORDER BY month DESC";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, [
        date('F Y', strtotime($row['month'] . '-01')),
        $row['total_employees'],
        $row['total_base'],
        $row['total_allowances'],
        $row['total_deductions'],
        $row['total_net']
    ]);
}

fclose($output);
exit();
?>