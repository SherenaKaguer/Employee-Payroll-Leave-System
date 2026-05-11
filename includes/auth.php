<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/session.php';

function hr_login($username, $password) {
    global $conn;
    
    $username = mysqli_real_escape_string($conn, $username);
    $query = "SELECT * FROM employees WHERE username = '$username' AND is_active = 1";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            hr_session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['employee_id'] = $user['employee_id'];
            $_SESSION['department_id'] = $user['department_id'];
            
            // Record Login History
            $uid = $user['id'];
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
            $ua = mysqli_real_escape_string($conn, $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');
            mysqli_query($conn, "INSERT INTO login_logs (user_id, ip_address, user_agent, status) VALUES ($uid, '$ip', '$ua', 'success')");
            
            return true;
        }
    }
    return false;
}

function hr_is_logged_in() {
    hr_session_start();
    return isset($_SESSION['user_id']);
}

function hr_is_admin() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'hr');
}

function hr_require_login() {
    if (!hr_is_logged_in()) {
        header('Location: ../login.php');
        exit();
    }
}

function hr_require_admin() {
    hr_require_login();
    if (!hr_is_admin()) {
        header('Location: ../employee/dashboard.php');
        exit();
    }
}

function hr_get_current_user() {
    global $conn;
    if (!hr_is_logged_in()) return null;
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM employees WHERE id = $user_id";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result);
}

function hr_logout() {
    hr_session_destroy();
    header('Location: ../login.php');
    exit();
}
?>