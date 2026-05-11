<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!hr_is_admin()) {
    echo json_encode([]);
    exit();
}

$q = mysqli_real_escape_string($conn, $_GET['q'] ?? '');

if (strlen($q) < 2) {
    echo json_encode([]);
    exit();
}

$query = "SELECT id, full_name, employee_id, position, profile_pic 
          FROM employees 
          WHERE (full_name LIKE '%$q%' OR employee_id LIKE '%$q%' OR email LIKE '%$q%')
          AND role != 'admin'
          LIMIT 5";

$result = mysqli_query($conn, $query);
$employees = [];

while ($row = mysqli_fetch_assoc($result)) {
    // Generate initials if no pic
    $initials = strtoupper(substr($row['full_name'], 0, 1));
    $pic_url = !empty($row['profile_pic']) && file_exists(__DIR__ . '/../' . $row['profile_pic']) 
               ? BASE_URL . $row['profile_pic'] 
               : null;

    $employees[] = [
        'id' => $row['id'],
        'name' => $row['full_name'],
        'code' => $row['employee_id'],
        'position' => $row['position'],
        'pic' => $pic_url,
        'initials' => $initials
    ];
}

echo json_encode($employees);
?>