<?php
require_once '../includes/header.php';
get_header("Add New Employee");

$error = '';
$success = '';

// Get Departments
$departments = mysqli_query($conn, "SELECT * FROM departments ORDER BY name");

// Get Salary Grades
$salary_grades = mysqli_query($conn, "SELECT * FROM salary_grades ORDER BY grade_level");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $dept_id = (int)$_POST['department_id'];
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $sg_id = (int)$_POST['salary_grade_id'];
    $role = $_POST['role'];
    $hire_date = $_POST['hire_date'];
    $dob = $_POST['dob'] ?? '';
    
    // Check if exists
    $check = mysqli_query($conn, "SELECT id FROM employees WHERE username = '$username' OR email = '$email' OR employee_id = '$emp_id'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Employee ID, Username or Email already exists!";
    } else {
        $query = "INSERT INTO employees (employee_id, username, password, email, full_name, department_id, position, salary_grade_id, role, hire_date, dob) 
                  VALUES ('$emp_id', '$username', '$password', '$email', '$full_name', $dept_id, '$position', $sg_id, '$role', '$hire_date', ".(!empty($dob) ? "'$dob'" : "NULL").")";
        
        if (mysqli_query($conn, $query)) {
            hr_audit_log('Add Employee', "Created new account for $full_name (ID: $emp_id).");
            $success = "Employee added successfully!";
            echo "<script>setTimeout(() => { window.location.href='employees.php'; }, 2000);</script>";
        } else {
            $error = "Error adding employee: " . mysqli_error($conn);
        }
    }
}
?>

<div class="card" style="max-width: 900px; margin: 0 auto; overflow: hidden;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 25px 40px;">
        <h3 style="font-weight: 800;"><i class="fas fa-user-plus" style="margin-right: 10px; color: var(--primary);"></i> Onboard New Staff</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 5px;">Enter the employee's personal and professional details to create their account.</p>
    </div>
    <div class="card-body" style="padding: 40px;">
        <form method="POST">
            <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 30px; padding: 0;">
                <!-- Identity Section -->
                <div class="form-group">
                    <label>Employee ID</label>
                    <div class="form-icon-group">
                        <i class="fas fa-id-card"></i>
                        <input type="text" name="employee_id" required placeholder="e.g. EMP005">
                    </div>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <div class="form-icon-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="full_name" required placeholder="John Doe">
                    </div>
                </div>

                <!-- Account Section -->
                <div class="form-group">
                    <label>Username</label>
                    <div class="form-icon-group">
                        <i class="fas fa-at"></i>
                        <input type="text" name="username" required placeholder="john.doe">
                    </div>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <div class="form-icon-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                </div>

                <!-- Contact & Date -->
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="form-icon-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" required placeholder="john@example.com">
                    </div>
                </div>
                <div class="form-group">
                    <label>Hire Date</label>
                    <div class="form-icon-group">
                        <i class="fas fa-calendar-alt"></i>
                        <input type="date" name="hire_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Date of Birth</label>
                    <div class="form-icon-group">
                        <i class="fas fa-birthday-cake"></i>
                        <input type="date" name="dob">
                    </div>
                </div>

                <!-- Org Section -->
                <div class="form-group">
                    <label>Department</label>
                    <div class="form-icon-group">
                        <i class="fas fa-building"></i>
                        <select name="department_id" required>
                            <option value="">Select Department</option>
                            <?php while ($dept = mysqli_fetch_assoc($departments)): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo $dept['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <div class="form-icon-group">
                        <i class="fas fa-briefcase"></i>
                        <input type="text" name="position" required placeholder="e.g. Software Engineer">
                    </div>
                </div>

                <!-- Compensation & Access -->
                <div class="form-group">
                    <label>Salary Grade</label>
                    <div class="form-icon-group">
                        <i class="fas fa-layer-group"></i>
                        <select name="salary_grade_id" required>
                            <option value="">Select Grade</option>
                            <?php while ($sg = mysqli_fetch_assoc($salary_grades)): ?>
                                <option value="<?php echo $sg['id']; ?>"><?php echo $sg['grade_level']; ?> (<?php echo format_currency($sg['base_salary']); ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>System Role</label>
                    <div class="form-icon-group">
                        <i class="fas fa-user-shield"></i>
                        <select name="role" required>
                            <option value="employee">Employee</option>
                            <option value="hr">HR Specialist</option>
                            <option value="admin">System Admin</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 40px; text-align: right; display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid var(--border); padding-top: 30px;">
                <a href="employees.php" class="btn btn-outline" style="padding: 12px 30px;">Cancel</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 50px;">Create Account <i class="fas fa-check-circle" style="margin-left: 8px;"></i></button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
