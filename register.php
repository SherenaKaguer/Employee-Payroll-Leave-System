<?php
require_once 'includes/auth.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
require_once 'config/database.php';

hr_session_start();

$error = '';
$success = '';

// Get Departments for the dropdown
$departments = mysqli_query($conn, "SELECT * FROM departments ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $dept_id = (int)$_POST['department_id'];
    
    // Check if exists
    $check = mysqli_query($conn, "SELECT id FROM employees WHERE username = '$username' OR email = '$email'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username or Email already exists!";
    } else {
        // By default, self-registered users are 'employee' role and salary grade 1 (if it exists)
        $query = "INSERT INTO employees (employee_id, username, password, email, full_name, department_id, role, salary_grade_id, hire_date) 
                  VALUES ('$emp_id', '$username', '$password', '$email', '$full_name', $dept_id, 'employee', 1, CURDATE())";
        
        if (mysqli_query($conn, $query)) {
            $success = "Registration successful! You can now login.";
            echo "<script>setTimeout(() => { window.location.href='login.php'; }, 2000);</script>";
        } else {
            $error = "Error during registration: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HR Payroll System</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div id="nprogress-bar"></div>
    <div class="login-container">
        <div class="login-card" style="max-width: 500px; padding: 40px;">
            <div class="login-header">
                <img src="favicon.svg" alt="Logo" width="60" style="margin-bottom: 20px;">
                <h1>Employee Registration</h1>
                <p>Join our premium workforce</p>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Work Identity</label>
                    <div class="form-icon-group">
                        <i class="fas fa-id-badge"></i>
                        <input type="text" name="employee_id" required placeholder="e.g. EMP100">
                    </div>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <div class="form-icon-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="full_name" required placeholder="John Doe">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="form-icon-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" required placeholder="john@example.com">
                    </div>
                </div>
                <div class="form-group">
                    <label>Access Security</label>
                    <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 0;">
                        <div class="form-icon-group" style="margin-bottom: 0;">
                            <i class="fas fa-at"></i>
                            <input type="text" name="username" required placeholder="username">
                        </div>
                        <div class="form-icon-group" style="margin-bottom: 0;">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" id="reg_password" required placeholder="password">
                            <i class="fas fa-eye pass-toggle"></i>
                        </div>
                        <div id="password-strength" style="height: 4px; background: rgba(255,255,255,0.1); border-radius: 2px; margin-top: 8px; overflow: hidden;">
                            <div id="strength-bar" style="height: 100%; width: 0%; transition: all 0.3s;"></div>
                        </div>
                        <small id="strength-text" style="font-size: 0.7rem; color: #94a3b8; margin-top: 5px; display: block;">Security: Not set</small>
                    </div>
                </div>
                <div class="form-group">
                    <label>Assigned Department</label>
                    <div class="form-icon-group">
                        <i class="fas fa-building"></i>
                        <select name="department_id" required>
                            <option value="">Choose Department...</option>
                            <?php while ($dept = mysqli_fetch_assoc($departments)): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary btn-block" style="padding: 14px;">Complete Registration <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></button>
                </div>
            </form>

            <div class="demo-credentials" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 20px;">
                <p>Already have an account? <a href="login.php" style="color: var(--primary-light); font-weight: 700; text-decoration: none;">Sign In</a></p>
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
            <?php if ($error): ?> showToast('Error', '<?php echo addslashes($error); ?>', 'danger'); <?php endif; ?>
            <?php if ($success): ?> showToast('Success', '<?php echo addslashes($success); ?>', 'success'); <?php endif; ?>

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

            // Password Strength Meter
            $('#reg_password').on('input', function() {
                const val = $(this).val();
                const bar = $('#strength-bar');
                const text = $('#strength-text');
                let strength = 0;

                if (val.length > 5) strength += 25;
                if (val.match(/[A-Z]/)) strength += 25;
                if (val.match(/[0-9]/)) strength += 25;
                if (val.match(/[^A-Za-z0-9]/)) strength += 25;

                bar.css('width', strength + '%');
                
                if (strength <= 25) {
                    bar.css('background', '#ef4444');
                    text.text('Security: Weak (Use Caps/Numbers)');
                } else if (strength <= 75) {
                    bar.css('background', '#f59e0b');
                    text.text('Security: Moderate (Use Symbols)');
                } else {
                    bar.css('background', '#10b981');
                    text.text('Security: Elite Protection');
                }
            });
        });
    </script>
</body>
</html>
