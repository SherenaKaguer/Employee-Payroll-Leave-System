<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

if (!hr_is_admin()) {
    die("Unauthorized access.");
}

$tables = [];
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

$sql_dump = "-- ============================================\n";
$sql_dump .= "-- ELITE HRMS DATABASE BACKUP\n";
$sql_dump .= "-- DATE: " . date('Y-m-d H:i:s') . "\n";
$sql_dump .= "-- ============================================\n\n";
$sql_dump .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

foreach ($tables as $table) {
    // Drop
    $sql_dump .= "DROP TABLE IF EXISTS `$table`;\n";
    
    // Create
    $row = mysqli_fetch_row(mysqli_query($conn, "SHOW CREATE TABLE `$table`"));
    $sql_dump .= $row[1] . ";\n\n";
    
    // Data
    $result = mysqli_query($conn, "SELECT * FROM `$table`");
    while ($row = mysqli_fetch_row($result)) {
        $sql_dump .= "INSERT INTO `$table` VALUES(";
        $values = [];
        foreach ($row as $val) {
            if (isset($val)) {
                $values[] = "'" . mysqli_real_escape_string($conn, $val) . "'";
            } else {
                $values[] = "NULL";
            }
        }
        $sql_dump .= implode(',', $values) . ");\n";
    }
    $sql_dump .= "\n";
}

$sql_dump .= "SET FOREIGN_KEY_CHECKS = 1;";

$filename = "hrms_backup_" . date('Y-m-d_H-i') . ".sql";

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($sql_dump));

echo $sql_dump;
exit();
?>