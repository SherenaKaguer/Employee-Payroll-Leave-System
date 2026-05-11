<?php
require_once '../includes/header.php';
get_header("Salary Grade Management");

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM salary_grades WHERE id = $id");
    echo "<script>window.location.href='salary_grades.php';</script>";
}

// Handle Add/Edit
$error = '';
$success = '';
$edit_grade = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_grade = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM salary_grades WHERE id = $id"));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $level = mysqli_real_escape_string($conn, $_POST['grade_level']);
    $base = (float)$_POST['base_salary'];
    $housing = (float)$_POST['housing_allowance'];
    $transport = (float)$_POST['transport_allowance'];
    $medical = (float)$_POST['medical_allowance'];
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $query = "UPDATE salary_grades SET grade_level='$level', base_salary=$base, housing_allowance=$housing, transport_allowance=$transport, medical_allowance=$medical WHERE id=$id";
    } else {
        $query = "INSERT INTO salary_grades (grade_level, base_salary, housing_allowance, transport_allowance, medical_allowance) 
                  VALUES ('$level', $base, $housing, $transport, $medical)";
    }
    
    if (mysqli_query($conn, $query)) {
        $success = "Salary grade saved successfully!";
        echo "<script>setTimeout(() => { window.location.href='salary_grades.php'; }, 1000);</script>";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Get grades
$salary_grades = mysqli_query($conn, "SELECT * FROM salary_grades ORDER BY base_salary ASC");
?>

<div class="dashboard-grid">
    <!-- Form Side -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas <?php echo $edit_grade ? 'fa-edit' : 'fa-plus-circle'; ?>" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $edit_grade ? 'Edit' : 'Add'; ?> Salary Grade</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php if ($edit_grade): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_grade['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Grade Level/Name</label>
                    <div class="form-icon-group">
                        <i class="fas fa-layer-group"></i>
                        <input type="text" name="grade_level" required value="<?php echo $edit_grade['grade_level'] ?? ''; ?>" placeholder="e.g. Grade 1">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Base Salary</label>
                    <div class="form-icon-group">
                        <i class="fas fa-money-bill-wave"></i>
                        <input type="number" step="0.01" name="base_salary" required value="<?php echo $edit_grade['base_salary'] ?? '0.00'; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Housing Allowance</label>
                    <div class="form-icon-group">
                        <i class="fas fa-home"></i>
                        <input type="number" step="0.01" name="housing_allowance" required value="<?php echo $edit_grade['housing_allowance'] ?? '0.00'; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Transport Allowance</label>
                    <div class="form-icon-group">
                        <i class="fas fa-bus"></i>
                        <input type="number" step="0.01" name="transport_allowance" required value="<?php echo $edit_grade['transport_allowance'] ?? '0.00'; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Medical Allowance</label>
                    <div class="form-icon-group">
                        <i class="fas fa-hand-holding-medical"></i>
                        <input type="number" step="0.01" name="medical_allowance" required value="<?php echo $edit_grade['medical_allowance'] ?? '0.00'; ?>">
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary btn-block"><?php echo $edit_grade ? 'Update' : 'Save'; ?> Grade</button>
                    <?php if ($edit_grade): ?>
                        <a href="salary_grades.php" class="btn btn-outline btn-block" style="margin-top: 10px;">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- List Side -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-coins" style="margin-right: 10px; color: var(--primary);"></i> Salary Configuration</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Grade</th>
                            <th>Base</th>
                            <th>Allowances</th>
                            <th>Gross Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $has_records = false;
                        while ($sg = mysqli_fetch_assoc($salary_grades)): 
                            $has_records = true;
                            $total_allowance = $sg['housing_allowance'] + $sg['transport_allowance'] + $sg['medical_allowance'];
                            $gross = $sg['base_salary'] + $total_allowance;
                        ?>
                        <tr>
                            <td><strong style="color: var(--primary);"><?php echo $sg['grade_level']; ?></strong></td>
                            <td><?php echo format_currency($sg['base_salary']); ?></td>
                            <td>
                                <div style="display: flex; gap: 8px; font-size: 0.75rem; color: var(--text-muted);">
                                    <span title="Housing"><i class="fas fa-home"></i></span>
                                    <span title="Transport"><i class="fas fa-bus"></i></span>
                                    <span title="Medical"><i class="fas fa-medkit"></i></span>
                                    <span><?php echo format_currency($total_allowance); ?></span>
                                </div>
                            </td>
                            <td><strong class="text-gradient" style="font-size: 1rem;"><?php echo format_currency($gross); ?></strong></td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="salary_grades.php?edit=<?php echo $sg['id']; ?>" class="btn btn-outline" style="padding: 6px 10px; font-size: 0.75rem;"><i class="fas fa-edit"></i></a>
                                    <a href="salary_grades.php?delete=<?php echo $sg['id']; ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.75rem;" onclick="return confirm('Delete this grade?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php if (!$has_records): ?>
                    <div class="empty-state">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <h3>No Salary Grades</h3>
                        <p>Configure salary structures and allowances for your employees.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
