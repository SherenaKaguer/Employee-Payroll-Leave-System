<?php
require_once '../includes/header.php';
get_header("My Leave Requests");

$user_id = $_SESSION['user_id'];
$user = hr_get_current_user();

$error = '';
$success = '';

// Handle New Request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['leave_type'];
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);
    
    $days = calculate_leave_days($start, $end);
    
    if ($days <= 0) {
        $error = "Invalid date range.";
    } elseif ($days > $user['leave_balance'] && $type != 'unpaid') {
        $error = "Insufficient leave balance. You only have {$user['leave_balance']} days left.";
    } else {
        $query = "INSERT INTO leave_requests (employee_id, leave_type, start_date, end_date, total_days, reason, status) 
                  VALUES ($user_id, '$type', '$start', '$end', $days, '$reason', 'pending')";
        
        if (mysqli_query($conn, $query)) {
            $success = "Leave request submitted successfully! It should now appear in your history below.";
        } else {
            $error = "Database Error: " . mysqli_error($conn);
        }
    }
}

// Get history with approver details
$query = "SELECT lr.*, e.full_name as approver_name 
          FROM leave_requests lr 
          LEFT JOIN employees e ON lr.approver_id = e.id 
          WHERE lr.employee_id = $user_id 
          ORDER BY lr.request_date DESC";
$history = mysqli_query($conn, $query);
?>

<div class="dashboard-grid">
    <!-- Request Form -->
    <div class="card">
        <div class="card-header">
            <h3>New Leave Request</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info" style="margin-bottom: 24px;">
                <i class="fas fa-info-circle"></i> Available Balance: <strong><?php echo $user['leave_balance']; ?> Days</strong>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Leave Type</label>
                    <select name="leave_type" required>
                        <option value="annual">Annual Leave</option>
                        <option value="sick">Sick Leave</option>
                        <option value="maternity">Maternity Leave</option>
                        <option value="paternity">Paternity Leave</option>
                        <option value="unpaid">Unpaid Leave</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" required min="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label>Reason</label>
                    <textarea name="reason" rows="3" required placeholder="Briefly explain the reason..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Submit Request</button>
            </form>
        </div>
    </div>

    <!-- History List -->
    <div class="card">
        <div class="card-header">
            <h3>My Leave History</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($history)): ?>
                        <tr>
                            <td>
                                <strong><?php echo date('M d, Y', strtotime($row['start_date'])); ?></strong><br>
                                <small class="badge badge-secondary"><?php echo $row['total_days']; ?> Days</small>
                            </td>
                            <td><?php echo ucfirst($row['leave_type']); ?></td>
                            <td><?php echo get_status_badge($row['status']); ?></td>
                            <td>
                                <?php if ($row['status'] != 'pending'): ?>
                                    <small><strong>By:</strong> <?php echo htmlspecialchars($row['approver_name'] ?? 'System'); ?></small><br>
                                    <small><em>"<?php echo htmlspecialchars($row['approval_reason'] ?? 'No remarks'); ?>"</em></small>
                                <?php else: ?>
                                    <span class="badge badge-info">Awaiting Review</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if (mysqli_num_rows($history) == 0): ?>
                            <tr><td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">No requests submitted yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
