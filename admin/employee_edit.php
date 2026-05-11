<?php
require_once '../includes/header.php';
get_header("Edit Employee");

if (!isset($_GET['id'])) {
    header('Location: employees.php');
    exit();
}

$id = (int)$_GET['id'];
$emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM employees WHERE id = $id"));

if (!$emp) {
    header('Location: employees.php');
    exit();
}

$error = '';
$success = '';

// Get Departments
$departments = mysqli_query($conn, "SELECT * FROM departments ORDER BY name");

// Get Salary Grades
$salary_grades = mysqli_query($conn, "SELECT * FROM salary_grades ORDER BY grade_level");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $dept_id = (int)$_POST['department_id'];
    $position = mysqli_real_escape_string($conn, $_POST['position']);
    $sg_id = (int)$_POST['salary_grade_id'];
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Check if exists (excluding current)
    $check = mysqli_query($conn, "SELECT id FROM employees WHERE (username = '$username' OR email = '$email' OR employee_id = '$emp_id') AND id != $id");
    if (mysqli_num_rows($check) > 0) {
        $error = "Employee ID, Username or Email already exists for another user!";
    } else {
        $query = "UPDATE employees SET 
                  employee_id = '$emp_id', 
                  username = '$username', 
                  email = '$email', 
                  full_name = '$full_name', 
                  department_id = $dept_id, 
                  position = '$position', 
                  salary_grade_id = $sg_id, 
                  role = '$role',
                  is_active = $is_active";
        
        // Update password if provided
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $query .= ", password = '$password'";
        }
        
        $query .= " WHERE id = $id";
        
        if (mysqli_query($conn, $query)) {
            $success = "Employee updated successfully!";
            $emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM employees WHERE id = $id")); // Refresh
        } else {
            $error = "Error updating employee: " . mysqli_error($conn);
        }
    }
}
?>

<div class="card" style="max-width: 900px; margin: 0 auto; overflow: hidden;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 25px 40px;">
        <h3 style="font-weight: 800;"><i class="fas fa-user-edit" style="margin-right: 10px; color: var(--primary);"></i> Modify Staff Profile</h3>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 5px;">Updating details for <strong><?php echo htmlspecialchars($emp['full_name']); ?></strong> (<?php echo $emp['employee_id']; ?>)</p>
    </div>
    <div class="card-body" style="padding: 40px;">
        <form method="POST">
            <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 30px; padding: 0;">
                <div class="form-group">
                    <label>Employee ID</label>
                    <div class="form-icon-group">
                        <i class="fas fa-id-card"></i>
                        <input type="text" name="employee_id" required value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <div class="form-icon-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="full_name" required value="<?php echo htmlspecialchars($emp['full_name']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Username</label>
                    <div class="form-icon-group">
                        <i class="fas fa-at"></i>
                        <input type="text" name="username" required value="<?php echo htmlspecialchars($emp['username']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Password <small>(Leave blank to keep current)</small></label>
                    <div class="form-icon-group">
                        <i class="fas fa-key"></i>
                        <input type="password" name="password" placeholder="Enter new password">
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <div class="form-icon-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" required value="<?php echo htmlspecialchars($emp['email']); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Account Status</label>
                    <div style="padding: 12px 20px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border); margin-top: 5px;">
                        <input type="checkbox" name="is_active" id="is_active" <?php echo $emp['is_active'] ? 'checked' : ''; ?>>
                        <label for="is_active" style="display: inline; font-weight: 700; color: var(--text-main); margin-left: 8px;"> This account is active</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Department</label>
                    <div class="form-icon-group">
                        <i class="fas fa-building"></i>
                        <select name="department_id" required>
                            <option value="">Select Department</option>
                            <?php while ($dept = mysqli_fetch_assoc($departments)): ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo $emp['department_id'] == $dept['id'] ? 'selected' : ''; ?>><?php echo $dept['name']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <div class="form-icon-group">
                        <i class="fas fa-briefcase"></i>
                        <input type="text" name="position" required value="<?php echo htmlspecialchars($emp['position']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Salary Grade</label>
                    <div class="form-icon-group">
                        <i class="fas fa-layer-group"></i>
                        <select name="salary_grade_id" required>
                            <option value="">Select Grade</option>
                            <?php while ($sg = mysqli_fetch_assoc($salary_grades)): ?>
                                <option value="<?php echo $sg['id']; ?>" <?php echo $emp['salary_grade_id'] == $sg['id'] ? 'selected' : ''; ?>><?php echo $sg['grade_level']; ?> (<?php echo format_currency($sg['base_salary']); ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>System Role</label>
                    <div class="form-icon-group">
                        <i class="fas fa-user-shield"></i>
                        <select name="role" required>
                            <option value="employee" <?php echo $emp['role'] == 'employee' ? 'selected' : ''; ?>>Employee</option>
                            <option value="hr" <?php echo $emp['role'] == 'hr' ? 'selected' : ''; ?>>HR Specialist</option>
                            <option value="admin" <?php echo $emp['role'] == 'admin' ? 'selected' : ''; ?>>System Admin</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 40px; text-align: right; display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid var(--border); padding-top: 30px;">
                <a href="employees.php" class="btn btn-outline" style="padding: 12px 30px;">Back to Directory</a>
                <button type="submit" class="btn btn-primary" style="padding: 12px 50px;">Save Changes <i class="fas fa-save" style="margin-left: 8px;"></i></button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
