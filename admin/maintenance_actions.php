<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!hr_is_admin()) {
    die("Unauthorized.");
}

$action = $_GET['action'] ?? '';

if ($action === 'clear_logs') {
    mysqli_query($conn, "DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    hr_audit_log('Maintenance', "Administrator cleared audit logs older than 30 days.");
    header('Location: settings.php?success=' . urlencode("Audit logs cleared successfully!"));
    exit();
}

if ($action === 'reset_leave') {
    $default = (int)get_setting('default_leave_balance') ?? 15;
    mysqli_query($conn, "UPDATE employees SET leave_balance = $default");
    hr_audit_log('Maintenance', "Administrator reset all employee leave balances to $default.");
    header('Location: settings.php?success=' . urlencode("All leave balances reset to organization standard ($default days)!"));
    exit();
}

header('Location: settings.php');
exit();
?>