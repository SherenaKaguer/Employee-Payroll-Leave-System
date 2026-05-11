<?php
require_once '../includes/header.php';
get_header("Department Management");

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM departments WHERE id = $id");
    echo "<script>window.location.href='departments.php';</script>";
}

// Handle Add/Edit
$error = '';
$success = '';
$edit_dept = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_dept = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM departments WHERE id = $id"));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $code = mysqli_real_escape_string($conn, $_POST['code']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $budget = (float)$_POST['budget'];
    $manager_id = !empty($_POST['manager_id']) ? (int)$_POST['manager_id'] : "NULL";
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $query = "UPDATE departments SET name='$name', code='$code', description='$desc', budget=$budget, manager_id=$manager_id WHERE id=$id";
    } else {
        $query = "INSERT INTO departments (name, code, description, budget, manager_id) VALUES ('$name', '$code', '$desc', $budget, $manager_id)";
    }
    
    if (mysqli_query($conn, $query)) {
        $success = "Department saved successfully!";
        echo "<script>setTimeout(() => { window.location.href='departments.php'; }, 1000);</script>";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Get departments
$query = "SELECT d.*, e.full_name as manager_name 
          FROM departments d 
          LEFT JOIN employees e ON d.manager_id = e.id 
          ORDER BY d.name ASC";
$departments = mysqli_query($conn, $query);
?>

<div class="dashboard-grid">
    <!-- Form Side -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas <?php echo $edit_dept ? 'fa-edit' : 'fa-plus-circle'; ?>" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $edit_dept ? 'Edit' : 'Add'; ?> Department</h3>
        </div>
        <div class="card-body">
            <?php 
            // Get all employees for the manager dropdown
            $employees = mysqli_query($conn, "SELECT id, full_name, employee_id FROM employees ORDER BY full_name");
            ?>

            <form method="POST">
                <?php if ($edit_dept): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_dept['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Department Name</label>
                    <div class="form-icon-group">
                        <i class="fas fa-building"></i>
                        <input type="text" name="name" required value="<?php echo $edit_dept['name'] ?? ''; ?>" placeholder="e.g. Engineering">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Department Code</label>
                    <div class="form-icon-group">
                        <i class="fas fa-tag"></i>
                        <input type="text" name="code" required value="<?php echo $edit_dept['code'] ?? ''; ?>" placeholder="e.g. ENG">
                    </div>
                </div>

                <div class="form-group">
                    <label>Department Manager</label>
                    <div class="form-icon-group">
                        <i class="fas fa-user-tie"></i>
                        <select name="manager_id">
                            <option value="">No Manager Assigned</option>
                            <?php while ($emp = mysqli_fetch_assoc($employees)): ?>
                                <option value="<?php echo $emp['id']; ?>" <?php echo ($edit_dept['manager_id'] ?? '') == $emp['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($emp['full_name']); ?> (<?php echo $emp['employee_id']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Annual Budget</label>
                    <div class="form-icon-group">
                        <i class="fas fa-wallet"></i>
                        <input type="number" step="0.01" name="budget" required value="<?php echo $edit_dept['budget'] ?? '0.00'; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <div class="form-icon-group">
                        <i class="fas fa-info-circle" style="top: 20px; transform: none;"></i>
                        <textarea name="description" rows="3" placeholder="Briefly describe the department's role..."><?php echo $edit_dept['description'] ?? ''; ?></textarea>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary btn-block"><?php echo $edit_dept ? 'Update' : 'Save'; ?> Department</button>
                    <?php if ($edit_dept): ?>
                        <a href="departments.php" class="btn btn-outline btn-block" style="margin-top: 10px;">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- List Side -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-list" style="margin-right: 10px; color: var(--primary);"></i> Departments List</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Manager</th>
                            <th>Budget</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $has_records = false;
                        $current_year = date('Y') . '-01-01';
                        
                        while ($dept = mysqli_fetch_assoc($departments)): 
                            $has_records = true;
                            $d_id = $dept['id'];
                            
                            // Calculate current year spending
                            $spend_q = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(net_salary) as total FROM payroll_records pr JOIN employees e ON pr.employee_id = e.id WHERE e.department_id = $d_id AND pr.payroll_month >= '$current_year'"));
                            $spent = (float)($spend_q['total'] ?? 0);
                            $budget = (float)$dept['budget'];
                            $percent = $budget > 0 ? min(100, ($spent / $budget) * 100) : 0;
                            $p_color = $percent > 90 ? 'var(--danger)' : ($percent > 70 ? 'var(--warning)' : 'var(--success)');
                        ?>
                        <tr>
                            <td><span class="badge badge-info" style="font-size: 0.7rem;"><?php echo $dept['code']; ?></span></td>
                            <td>
                                <strong><?php echo htmlspecialchars($dept['name']); ?></strong><br>
                                <small style="color: var(--text-muted);"><?php echo htmlspecialchars($dept['manager_name'] ?? 'No Manager'); ?></small>
                            </td>
                            <td>
                                <div style="width: 150px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                                        <small style="font-weight: 800; font-size: 0.65rem; color: var(--text-muted);">Yearly Usage</small>
                                        <small style="font-weight: 800; font-size: 0.65rem; color: <?php echo $p_color; ?>;"><?php echo round($percent); ?>%</small>
                                    </div>
                                    <div style="height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                                        <div style="height: 100%; width: <?php echo $percent; ?>%; background: <?php echo $p_color; ?>; transition: width 1s ease-out;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong style="display: block; color: var(--text-main); font-size: 0.9rem;"><?php echo format_currency($spent); ?></strong>
                                <small style="color: var(--text-muted); font-size: 0.7rem;">of <?php echo format_currency($budget); ?></small>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="departments.php?edit=<?php echo $dept['id']; ?>" class="btn btn-outline" style="padding: 6px 10px; font-size: 0.75rem;"><i class="fas fa-edit"></i></a>
                                    <a href="departments.php?delete=<?php echo $dept['id']; ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.75rem;" onclick="return confirm('Delete this department?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php if (!$has_records): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open"></i>
                        <h3>No Departments</h3>
                        <p>You haven't added any departments yet. Start by creating one.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
