<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!hr_is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$ids = $data['ids'] ?? [];

if (empty($ids) || !is_array($ids)) {
    echo json_encode(['success' => false, 'message' => 'No records selected.']);
    exit();
}

$id_list = implode(',', array_map('intval', $ids));
$success = false;
$message = '';

switch ($action) {
    case 'activate':
        $query = "UPDATE employees SET is_active = 1 WHERE id IN ($id_list) AND role != 'admin'";
        if (mysqli_query($conn, $query)) {
            hr_audit_log('Bulk Activate', "Activated " . count($ids) . " staff records.");
            $success = true;
            $message = count($ids) . ' employees activated successfully.';
        }
        break;

    case 'deactivate':
        $query = "UPDATE employees SET is_active = 0 WHERE id IN ($id_list) AND role != 'admin'";
        if (mysqli_query($conn, $query)) {
            hr_audit_log('Bulk Deactivate', "Deactivated " . count($ids) . " staff records.");
            $success = true;
            $message = count($ids) . ' employees deactivated successfully.';
        }
        break;

    case 'delete':
        $query = "DELETE FROM employees WHERE id IN ($id_list) AND role != 'admin'";
        if (mysqli_query($conn, $query)) {
            hr_audit_log('Bulk Delete', "Permanently removed " . count($ids) . " staff records.");
            $success = true;
            $message = count($ids) . ' employees removed from the system.';
        }
        break;

    default:
        $message = 'Invalid action requested.';
}

echo json_encode(['success' => $success, 'message' => $message]);
?>