<?php
require_once 'includes/auth.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';

hr_session_start();

if (hr_is_logged_in()) {
    if (hr_is_admin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: employee/dashboard.php');
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (hr_login($username, $password)) {
        if (hr_is_admin()) {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: employee/dashboard.php');
        }
        exit();
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - HR Payroll System</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Load saved theme immediately to prevent flash
        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark-mode');
        }
    </script>
</head>

<body class="login-page">
    <div id="nprogress-bar"></div>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="favicon.svg" alt="Logo" width="80" style="margin-bottom: 20px;">
                <h1>HR Payroll System</h1>
                <p>Management Portal Login</p>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Username</label>
                    <div class="form-icon-group">
                        <i class="fas fa-user-circle"></i>
                        <input type="text" name="username" required placeholder="Enter your username">
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="form-icon-group">
                        <i class="fas fa-shield-alt"></i>
                        <input type="password" name="password" required placeholder="Enter secure password">
                        <i class="fas fa-eye pass-toggle"></i>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary btn-block" style="padding: 14px;">Sign In to Dashboard <i class="fas fa-sign-in-alt" style="margin-left: 8px;"></i></button>
                </div>
            </form>

            <div class="login-footer" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                <?php if (get_setting('allow_self_registration') == '1'): ?>
                    <p style="color: #94a3b8; font-size: 0.8rem; margin-bottom: 10px;">Don't have an account? <a href="register.php" style="color: var(--primary-light); font-weight: 700; text-decoration: none;">Register</a></p>
                <?php endif; ?>
                <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; font-size: 0.75rem; text-align: left;">
                    <p style="color: var(--primary-light); font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">Demo Access</p>
                    <p style="color: #cbd5e1;">Admin: <strong>admin / password</strong></p>
                    <p style="color: #cbd5e1;">Staff: <strong>john.doe / password</strong></p>
                </div>
            </div>
        </div>
    </div>

    <div id="toast-container"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function showToast(title, message, type = 'success') {
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            const toastHtml = `
                <div class="toast toast-${type}" onclick="this.remove()">
                    <div class="toast-icon"><i class="fas ${icon}"></i></div>
                    <div class="toast-content"><h4>${title}</h4><p>${message}</p></div>
                </div>
            `;
            $('#toast-container').append(toastHtml);
            setTimeout(() => $('.toast').first().fadeOut(300, function() { $(this).remove(); }), 5000);
        }

        $(document).ready(function() {
            <?php if ($error): ?> showToast('Authentication Failed', '<?php echo addslashes($error); ?>', 'danger'); <?php endif; ?>

            // Password Toggle
            $(document).on('click', '.pass-toggle', function() {
                const container = $(this).closest('.form-icon-group');
                const input = container.find('input').first();
                const icon = $(this);

                if (!input.length) return;

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
        });
    </script>
</body>

</html>
