<?php
// ============================================
// SESSION MANAGEMENT
// ============================================

function hr_session_start() {
    if (session_status() !== PHP_SESSION_NONE) return;

    // Safer defaults (applied per-request before session_start)
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    $is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

    // Ensure cookie flags are set consistently
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $is_secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function hr_session_destroy() {
    if (session_status() === PHP_SESSION_NONE) {
        hr_session_start();
    }

    // Clear all session data
    $_SESSION = [];

    // Remove the session cookie
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'] ?? '/',
            'domain' => $params['domain'] ?? '',
            'secure' => (bool)($params['secure'] ?? false),
            'httponly' => (bool)($params['httponly'] ?? true),
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}

function hr_set_session($key, $value) {
    $_SESSION[$key] = $value;
}

function hr_get_session($key, $default = null) {
    return $_SESSION[$key] ?? $default;
}

function hr_clear_session($key) {
    unset($_SESSION[$key]);
}

// Multi-step form session management
function hr_init_wizard($wizard_name) {
    if (!isset($_SESSION['wizards'][$wizard_name])) {
        $_SESSION['wizards'][$wizard_name] = [];
    }
    return $_SESSION['wizards'][$wizard_name];
}

function hr_set_wizard_step($wizard_name, $step, $data) {
    $_SESSION['wizards'][$wizard_name][$step] = $data;
}

function hr_get_wizard_data($wizard_name) {
    return $_SESSION['wizards'][$wizard_name] ?? [];
}

function hr_clear_wizard($wizard_name) {
    unset($_SESSION['wizards'][$wizard_name]);
}
?>
