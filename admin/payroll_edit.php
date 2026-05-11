<?php
require_once '../includes/header.php';
get_header("Adjust Payroll Record");

if (!isset($_GET['id'])) {
    header('Location: payroll.php');
    exit();
}

$id = (int)$_GET['id'];
$query = "SELECT pr.*, e.full_name, e.employee_id as emp_code, e.position 
          FROM payroll_records pr
          JOIN employees e ON pr.employee_id = e.id
          WHERE pr.id = $id";
$payroll = mysqli_fetch_assoc(mysqli_query($conn, $query));

if (!$payroll) {
    header('Location: payroll.php');
    exit();
}

// Only allow editing if not already paid
$can_edit = $payroll['status'] !== 'paid';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $can_edit) {
    $base = (float)$_POST['base_salary'];
    $allowances = (float)$_POST['allowances'];
    $deductions = (float)$_POST['total_deductions'];
    $bonus = (float)$_POST['bonus'];
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    $status = $_POST['status'];
    
    // PHP Recalculation of Net Salary
    $net = ($base + $allowances + $bonus) - $deductions;
    
    $update_query = "UPDATE payroll_records SET 
                     base_salary = $base, 
                     allowances = $allowances, 
                     total_deductions = $deductions, 
                     bonus = $bonus, 
                     net_salary = $net,
                     notes = '$notes',
                     status = '$status'
                     WHERE id = $id";
    
    if (mysqli_query($conn, $update_query)) {
        $success = "Payroll adjusted successfully!";
        // Refresh local data
        $payroll = mysqli_fetch_assoc(mysqli_query($conn, $query));
    } else {
        $error = "Error updating payroll: " . mysqli_error($conn);
    }
}
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h3><i class="fas fa-edit" style="margin-right: 10px; color: var(--primary);"></i> Payroll Adjustment: <?php echo htmlspecialchars($payroll['full_name']); ?></h3>
    </div>
    <div class="card-body" style="padding: 40px;">
        <?php if (!$can_edit): ?>
            <div class="alert alert-warning" style="margin-bottom: 30px;">
                <i class="fas fa-lock"></i> This payroll has been marked as <strong>PAID</strong> and is now locked for modifications.
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                <div style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid var(--border);">
                    <small style="display: block; text-transform: uppercase; color: var(--text-muted); font-weight: 800; font-size: 0.65rem; margin-bottom: 8px;">Employee Context</small>
                    <p style="font-weight: 800; color: var(--text-main); margin: 0;"><?php echo $payroll['full_name']; ?></p>
                    <p style="color: var(--text-muted); font-size: 0.85rem;"><?php echo $payroll['emp_code']; ?> • <?php echo $payroll['position']; ?></p>
                </div>
                <div style="background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid var(--border);">
                    <small style="display: block; text-transform: uppercase; color: var(--text-muted); font-weight: 800; font-size: 0.65rem; margin-bottom: 8px;">Statement Period</small>
                    <p style="font-weight: 800; color: var(--text-main); margin: 0;"><?php echo date('F Y', strtotime($payroll['payroll_month'])); ?></p>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Status: <?php echo get_status_badge($payroll['status']); ?></p>
                </div>

                <div class="form-group">
                    <label>Base Salary</label>
                    <div class="form-icon-group">
                        <i class="fas fa-money-bill-wave"></i>
                        <input type="number" step="0.01" name="base_salary" value="<?php echo $payroll['base_salary']; ?>" <?php echo !$can_edit ? 'disabled' : ''; ?>>
                    </div>
                </div>
                <div class="form-group">
                    <label>Total Allowances</label>
                    <div class="form-icon-group">
                        <i class="fas fa-plus-circle"></i>
                        <input type="number" step="0.01" name="allowances" value="<?php echo $payroll['allowances']; ?>" <?php echo !$can_edit ? 'disabled' : ''; ?>>
                    </div>
                </div>

                <div class="form-group">
                    <label>Total Deductions</label>
                    <div class="form-icon-group">
                        <i class="fas fa-minus-circle"></i>
                        <input type="number" step="0.01" name="total_deductions" value="<?php echo $payroll['total_deductions']; ?>" <?php echo !$can_edit ? 'disabled' : ''; ?>>
                    </div>
                </div>
                <div class="form-group">
                    <label>Adjustment Bonus (+)</label>
                    <div class="form-icon-group">
                        <i class="fas fa-award"></i>
                        <input type="number" step="0.01" name="bonus" value="<?php echo $payroll['bonus']; ?>" <?php echo !$can_edit ? 'disabled' : ''; ?> placeholder="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label>Current Status</label>
                    <div class="form-icon-group">
                        <i class="fas fa-tasks"></i>
                        <select name="status" <?php echo !$can_edit ? 'disabled' : ''; ?>>
                            <option value="draft" <?php echo $payroll['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="processed" <?php echo $payroll['status'] == 'processed' ? 'selected' : ''; ?>>Processed</option>
                            <option value="paid" <?php echo $payroll['status'] == 'paid' ? 'selected' : ''; ?>>Paid (Lock Record)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Final Net Salary</label>
                    <div style="font-size: 1.8rem; font-weight: 800; color: var(--primary); letter-spacing: -1px;">
                        <?php echo format_currency($payroll['net_salary']); ?>
                    </div>
                    <small style="color: var(--text-muted); font-weight: 600;">(Automatically Re-calculated)</small>
                </div>
            </div>

            <div class="form-group">
                <label>Adjustment Remarks / Internal Notes</label>
                <div class="form-icon-group">
                    <i class="fas fa-comment-alt" style="top: 20px; transform: none;"></i>
                    <textarea name="notes" rows="3" placeholder="e.g. Performance bonus for Q1 targets..." <?php echo !$can_edit ? 'disabled' : ''; ?>><?php echo htmlspecialchars($payroll['notes'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div style="margin-top: 40px; text-align: right; display: flex; gap: 12px; justify-content: flex-end;">
                <a href="payroll.php" class="btn btn-outline" style="padding: 12px 25px;">Cancel & Exit</a>
                <?php if ($can_edit): ?>
                    <button type="submit" class="btn btn-primary" style="padding: 12px 40px;">Update & Recalculate <i class="fas fa-sync" style="margin-left: 8px;"></i></button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
