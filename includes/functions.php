<?php
// ============================================
// HELPER FUNCTIONS
// ============================================

function calculate_leave_days($start_date, $end_date) {
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    return $interval->days + 1;
}

function calculate_salary($employee_id, $month) {
    global $conn;
    
    $query = "SELECT sg.base_salary, 
                     (sg.housing_allowance + sg.transport_allowance + sg.medical_allowance) as allowances,
                     e.leave_balance
              FROM employees e
              JOIN salary_grades sg ON e.salary_grade_id = sg.id
              WHERE e.id = $employee_id";
    
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);
    
    if (!$data) {
        return [
            'base_salary' => 0,
            'allowances' => 0,
            'total_deductions' => 0,
            'net_salary' => 0
        ];
    }
    
    $base = $data['base_salary'];
    $allowances = $data['allowances'];
    
    // Calculate total deductions
    $ded_query = "SELECT d.*, 
                         CASE WHEN d.type = 'percentage' THEN ($base * d.amount / 100)
                              ELSE d.amount
                         END as calculated_amount
                  FROM deductions d";
    $ded_result = mysqli_query($conn, $ded_query);
    
    $total_deductions = 0;
    while ($ded = mysqli_fetch_assoc($ded_result)) {
        $total_deductions += $ded['calculated_amount'];
    }
    
    $net_salary = $base + $allowances - $total_deductions;
    
    return [
        'base_salary' => $base,
        'allowances' => $allowances,
        'total_deductions' => $total_deductions,
        'net_salary' => $net_salary
    ];
}

function get_setting($key) {
    global $conn;
    if (!$conn) return null;

    // Preventive check: Ensure table exists before querying
    $table_check = @mysqli_query($conn, "SHOW TABLES LIKE 'system_settings'");
    if (!$table_check || mysqli_num_rows($table_check) == 0) {
        return null;
    }

    try {
        $safe_key = mysqli_real_escape_string($conn, $key);
        $res = mysqli_query($conn, "SELECT setting_value FROM system_settings WHERE setting_key = '$safe_key'");
        if($res && $row = mysqli_fetch_assoc($res)) {
            return $row['setting_value'];
        }
    } catch (Throwable $e) {
        // Fallback for missing settings
        return null;
    }
    return null;
}

function format_currency($amount) {
    $symbol = get_setting('currency_symbol') ?? '$';
    return $symbol . number_format($amount, 2);
}

function get_status_badge($status) {
    $badges = [
        'pending' => '<span class="badge badge-warning">Pending</span>',
        'approved' => '<span class="badge badge-success">Approved</span>',
        'rejected' => '<span class="badge badge-danger">Rejected</span>',
        'cancelled' => '<span class="badge badge-secondary">Cancelled</span>',
        'draft' => '<span class="badge badge-info">Draft</span>',
        'processed' => '<span class="badge badge-primary">Processed</span>',
        'paid' => '<span class="badge badge-success">Paid</span>'
    ];
    return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
}

function get_profile_pic($user) {
    if (!empty($user['profile_pic']) && file_exists(__DIR__ . '/../' . $user['profile_pic'])) {
        return BASE_URL . $user['profile_pic'];
    }
    return null; // Initial-based avatar will be shown
}

function hr_audit_log($action, $details = '') {
    global $conn;
    $user_id = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $action = mysqli_real_escape_string($conn, $action);
    $details = mysqli_real_escape_string($conn, $details);
    
    $query = "INSERT INTO audit_logs (user_id, action, details, ip_address) 
              VALUES (" . ($user_id ? $user_id : "NULL") . ", '$action', '$details', '$ip')";
    mysqli_query($conn, $query);
}
?>