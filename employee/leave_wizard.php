<?php
require_once '../includes/header.php';
get_header("Request Leave (Step 1)");

$user_id = $_SESSION['user_id'];
$user = hr_get_current_user();
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Initialize wizard session if not exists
if (!isset($_SESSION['leave_wizard'])) {
    $_SESSION['leave_wizard'] = [];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 1) {
        $type = $_POST['leave_type'];
        $start = $_POST['start_date'];
        $end = $_POST['end_date'];
        
        $days = calculate_leave_days($start, $end);
        
        if ($days <= 0) {
            $error = "Invalid date range.";
        } elseif ($days > $user['leave_balance'] && $type != 'unpaid') {
            $error = "Insufficient leave balance. You only have {$user['leave_balance']} days left.";
        } else {
            $_SESSION['leave_wizard'] = [
                'leave_type' => $type,
                'start_date' => $start,
                'end_date' => $end,
                'total_days' => $days
            ];
            header('Location: leave_wizard.php?step=2');
            exit();
        }
    } elseif ($step == 2) {
        $reason = mysqli_real_escape_string($conn, $_POST['reason']);
        $wizard_data = $_SESSION['leave_wizard'];
        
        $query = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, total_days, reason, status) 
                  VALUES ($user_id, '{$wizard_data['leave_type']}', '{$wizard_data['start_date']}', '{$wizard_data['end_date']}', {$wizard_data['total_days']}, '$reason', 'pending')";
        
        if (mysqli_query($conn, $query)) {
            // Log activity
            hr_audit_log('Leave Request', "Submitted request for " . $wizard_data['total_days'] . " days (" . $wizard_data['leave_type'] . ")");
            unset($_SESSION['leave_wizard']);
            echo "<script>setTimeout(launchCelebration, 500); setTimeout(() => { window.location.href='dashboard.php?success=" . urlencode("Leave request submitted successfully!") . "'; }, 3000);</script>";
            exit();
        } else {
            $error = "Error submitting request: " . mysqli_error($conn);
        }
    }
}

// Clear wizard if requested
if (isset($_GET['clear'])) {
    unset($_SESSION['leave_wizard']);
    header('Location: leave_wizard.php?step=1');
    exit();
}
?>

<div class="card" style="max-width: 700px; margin: 0 auto; overflow: hidden;">
    <div class="card-header" style="background: #f8fafc; border-bottom: 1px solid var(--border); padding: 30px;">
        <h3 style="font-weight: 800;">Leave Application Wizard</h3>
        <div style="display: flex; gap: 10px; margin-top: 15px;">
            <div style="flex: 1; height: 6px; border-radius: 3px; background: <?php echo $step >= 1 ? 'var(--primary)' : '#e2e8f0'; ?>;"></div>
            <div style="flex: 1; height: 6px; border-radius: 3px; background: <?php echo $step >= 2 ? 'var(--primary)' : '#e2e8f0'; ?>;"></div>
        </div>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 8px; font-weight: 600;">Step <?php echo $step; ?> of 2: <?php echo $step == 1 ? 'Select Dates' : 'Provide Reason'; ?></p>
    </div>
    <div class="card-body" style="padding: 40px;">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <form method="POST">
                <div class="alert alert-info" style="margin-bottom: 30px;">
                    <i class="fas fa-info-circle"></i> Available Leave Balance: <strong><?php echo $user['leave_balance']; ?> Days</strong>
                </div>

                <div class="form-group">
                    <label>Type of Leave</label>
                    <div class="form-icon-group">
                        <i class="fas fa-calendar-check"></i>
                        <select name="leave_type" required>
                            <option value="annual" <?php echo ($_SESSION['leave_wizard']['leave_type'] ?? '') == 'annual' ? 'selected' : ''; ?>>Annual Leave</option>
                            <option value="sick" <?php echo ($_SESSION['leave_wizard']['leave_type'] ?? '') == 'sick' ? 'selected' : ''; ?>>Sick Leave</option>
                            <option value="unpaid" <?php echo ($_SESSION['leave_wizard']['leave_type'] ?? '') == 'unpaid' ? 'selected' : ''; ?>>Unpaid Leave</option>
                            <option value="maternity" <?php echo ($_SESSION['leave_wizard']['leave_type'] ?? '') == 'maternity' ? 'selected' : ''; ?>>Maternity Leave</option>
                            <option value="paternity" <?php echo ($_SESSION['leave_wizard']['leave_type'] ?? '') == 'paternity' ? 'selected' : ''; ?>>Paternity Leave</option>
                        </select>
                    </div>
                </div>
                
                <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 0;">
                    <div class="form-group">
                        <label>Start Date</label>
                        <div class="form-icon-group">
                            <i class="fas fa-calendar-day"></i>
                            <input type="date" name="start_date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo $_SESSION['leave_wizard']['start_date'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>End Date</label>
                        <div class="form-icon-group">
                            <i class="fas fa-calendar-day"></i>
                            <input type="date" name="end_date" required min="<?php echo date('Y-m-d'); ?>" value="<?php echo $_SESSION['leave_wizard']['end_date'] ?? ''; ?>">
                        </div>
                    </div>
                </div>
                
                <div style="text-align: right; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">Continue to Reason <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></button>
                </div>
            </form>
        <?php else: ?>
            <div class="card" style="background: #f8fafc; border: 1px solid var(--border); box-shadow: none; margin-bottom: 30px;">
                <div class="card-body" style="padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <small style="display: block; text-transform: uppercase; color: var(--text-muted); font-weight: 800; font-size: 0.65rem; letter-spacing: 1px;">Selected Period</small>
                        <strong style="color: var(--text-main);"><?php echo date('M d, Y', strtotime($_SESSION['leave_wizard']['start_date'])); ?> to <?php echo date('M d, Y', strtotime($_SESSION['leave_wizard']['end_date'])); ?></strong>
                    </div>
                    <div style="text-align: right;">
                        <span class="badge badge-primary" style="padding: 8px 15px; font-size: 0.85rem;"><?php echo $_SESSION['leave_wizard']['total_days']; ?> Working Days</span>
                    </div>
                </div>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label>Justification / Reason</label>
                    <textarea name="reason" rows="6" required placeholder="Please provide a brief explanation for your leave request..."></textarea>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 30px;">
                    <a href="leave_wizard.php?step=1" class="btn btn-outline" style="border: none;"><i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Back to Step 1</a>
                    <button type="submit" class="btn btn-primary" style="padding: 12px 40px; background: var(--success); border-color: var(--success);">Confirm & Submit <i class="fas fa-check" style="margin-left: 8px;"></i></button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
