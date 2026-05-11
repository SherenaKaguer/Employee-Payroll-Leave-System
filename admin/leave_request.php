<?php
require_once '../includes/header.php';
get_header("Leave Requests Management");

// Handle Approval/Rejection
if (isset($_POST['action'])) {
    $request_id = (int)$_POST['request_id'];
    $action = $_POST['action']; // approved or rejected
    $reason = mysqli_real_escape_string($conn, $_POST['approval_reason']);
    $approver_id = $_SESSION['user_id'];
    
    // Start Transaction for atomic update
    mysqli_begin_transaction($conn);
    
    try {
        // Update request status
        $query = "UPDATE leave_requests 
                  SET status = '$action', 
                      approval_reason = '$reason', 
                      approver_id = $approver_id,
                      resolved_date = CURRENT_TIMESTAMP 
                  WHERE id = $request_id";
        
        if (!mysqli_query($conn, $query)) {
            throw new Exception("Error updating request status.");
        }
        
        // If approved, decrement balance in PHP then update DB
        if ($action === 'approved') {
            // Get current employee balance and request details
            $req_query = "SELECT lr.total_days, lr.leave_type, lr.employee_id, lr.start_date, lr.end_date, e.full_name, e.leave_balance 
                          FROM leave_requests lr 
                          JOIN employees e ON lr.employee_id = e.id 
                          WHERE lr.id = $request_id";
            $req_result = mysqli_query($conn, $req_query);
            $data = mysqli_fetch_assoc($req_result);
            
            $current_balance = (int)$data['leave_balance'];
            $days_to_deduct = (int)$data['total_days'];
            $emp_id = (int)$data['employee_id'];
            $employee_name = $data['full_name'];
            $start_date = $data['start_date'];
            $end_date = $data['end_date'];
            
            // Only decrement if not unpaid
            if ($data['leave_type'] !== 'unpaid') {
                // PHP Calculation before update
                $new_balance = $current_balance - $days_to_deduct;
                
                $update_balance = "UPDATE employees SET leave_balance = $new_balance WHERE id = $emp_id";
                if (!mysqli_query($conn, $update_balance)) {
                    throw new Exception("Error updating employee leave balance.");
                }
            }
            
            // Create notification for approved leave
            $notif_title = "Leave Request Approved";
            $notif_message = "Your leave request for " . date('M d, Y', strtotime($start_date)) . " to " . date('M d, Y', strtotime($end_date)) . " has been approved!";
            $notif_type = "success";
            
            $notif_query = "INSERT INTO notifications (user_id, title, message, type) 
                           VALUES ($emp_id, '$notif_title', '$notif_message', '$notif_type')";
            if (!mysqli_query($conn, $notif_query)) {
                throw new Exception("Error creating notification.");
            }
            
            // Trigger payroll validation - mark payroll as needing review for this employee
            // Get the payroll month that might be affected (current month)
            $payroll_month = date('Y-m-01');
            $payroll_query = "SELECT id, status FROM payroll_records 
                            WHERE employee_id = $emp_id 
                            AND payroll_month >= DATE_SUB('$payroll_month', INTERVAL 1 MONTH)
                            AND status IN ('draft', 'processed')
                            LIMIT 1";
            $payroll_result = mysqli_query($conn, $payroll_query);
            
            if (mysqli_num_rows($payroll_result) > 0) {
                $payroll = mysqli_fetch_assoc($payroll_result);
                // Update payroll status to 'processed' if approved leave will affect it
                $update_payroll = "UPDATE payroll_records 
                                 SET notes = CONCAT(IFNULL(notes, ''), '\n[Leave Approved] ', DATE_FORMAT(NOW(), '%Y-%m-%d %H:%i')) 
                                 WHERE id = " . $payroll['id'];
                mysqli_query($conn, $update_payroll);
            }
            
            // Audit log
            hr_audit_log('Leave Approval', "Approved $days_to_deduct days leave for employee: $employee_name (ID: $emp_id)");
            
        } else if ($action === 'rejected') {
            // Get employee info for rejection notification
            $req_query = "SELECT lr.employee_id, e.full_name 
                          FROM leave_requests lr 
                          JOIN employees e ON lr.employee_id = e.id 
                          WHERE lr.id = $request_id";
            $req_result = mysqli_query($conn, $req_query);
            $data = mysqli_fetch_assoc($req_result);
            $emp_id = $data['employee_id'];
            
            // Create notification for rejected leave
            $notif_title = "Leave Request Rejected";
            $notif_message = "Your leave request has been rejected. Reason: " . substr($reason, 0, 100);
            $notif_type = "warning";
            
            $notif_query = "INSERT INTO notifications (user_id, title, message, type) 
                           VALUES ($emp_id, '$notif_title', '$notif_message', '$notif_type')";
            mysqli_query($conn, $notif_query);
            
            hr_audit_log('Leave Rejection', "Rejected leave request for employee ID: $emp_id. Reason: " . substr($reason, 0, 100));
        }
        
        mysqli_commit($conn);
        echo "<script>alert('Request " . ucfirst($action) . " successfully!'); window.location.href='leave_request.php';</script>";
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}

// Get leave requests
$filter = $_GET['status'] ?? 'pending';
$query = "SELECT lr.*, e.full_name, e.employee_id as emp_code, d.name as department 
          FROM leave_requests lr
          JOIN employees e ON lr.employee_id = e.id
          JOIN departments d ON e.department_id = d.id";

if ($filter != 'all') {
    $query .= " WHERE lr.status = '$filter'";
}
$query .= " ORDER BY lr.request_date DESC";
$requests = mysqli_query($conn, $query);
?>

<div class="card" style="margin-bottom: 30px;">
    <div class="card-body" style="padding: 15px 25px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <span style="font-weight: 700; color: var(--text-muted); margin-right: 10px; font-size: 0.9rem;">Filter Status:</span>
        <a href="leave_request.php?status=pending" class="btn <?php echo $filter == 'pending' ? 'btn-primary' : 'btn-outline'; ?>" style="font-size: 0.8rem; padding: 8px 16px;">Pending</a>
        <a href="leave_request.php?status=approved" class="btn <?php echo $filter == 'approved' ? 'btn-primary' : 'btn-outline'; ?>" style="font-size: 0.8rem; padding: 8px 16px;">Approved</a>
        <a href="leave_request.php?status=rejected" class="btn <?php echo $filter == 'rejected' ? 'btn-primary' : 'btn-outline'; ?>" style="font-size: 0.8rem; padding: 8px 16px;">Rejected</a>
        <a href="leave_request.php?status=all" class="btn <?php echo $filter == 'all' ? 'btn-primary' : 'btn-outline'; ?>" style="font-size: 0.8rem; padding: 8px 16px;">All Requests</a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-calendar-check" style="margin-right: 10px; color: var(--primary);"></i> <?php echo ucfirst($filter); ?> Leave Requests</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Duration</th>
                        <th>Total Days</th>
                        <th>Reasoning</th>
                        <th>Status</th>
                        <?php if ($filter == 'pending' || $filter == 'all'): ?>
                            <th>Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_reqs = false;
                    while ($req = mysqli_fetch_assoc($requests)): 
                        $has_reqs = true;
                    ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 32px; height: 32px; border-radius: 8px; background: #f1f5f9; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800;">
                                    <?php echo strtoupper(substr($req['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <strong style="display: block; font-size: 0.9rem;"><?php echo htmlspecialchars($req['full_name']); ?></strong>
                                    <small style="color: var(--text-muted);"><?php echo $req['emp_code']; ?> | <?php echo htmlspecialchars($req['department']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-info" style="font-size: 0.65rem; font-weight: 800;"><?php echo strtoupper($req['leave_type']); ?></span></td>
                        <td>
                            <div style="display: flex; flex-direction: column;">
                                <small style="font-weight: 700; color: var(--text-main);"><?php echo date('M d, Y', strtotime($req['start_date'])); ?></small>
                                <small style="color: var(--text-muted);">to <?php echo date('M d, Y', strtotime($req['end_date'])); ?></small>
                            </div>
                        </td>
                        <td><strong><?php echo $req['total_days']; ?> d</strong></td>
                        <td><div style="max-width: 250px; font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;"><?php echo htmlspecialchars($req['reason']); ?></div></td>
                        <td><?php echo get_status_badge($req['status']); ?></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <?php if ($req['status'] == 'pending'): ?>
                                    <button type="button" class="btn btn-primary" style="padding: 6px 12px;" title="Approve Request" onclick="openApprovalModal(<?php echo $req['id']; ?>, 'approved')"><i class="fas fa-check"></i></button>
                                    <button type="button" class="btn btn-danger" style="padding: 6px 12px;" title="Reject Request" onclick="openApprovalModal(<?php echo $req['id']; ?>, 'rejected')"><i class="fas fa-times"></i></button>
                                <?php else: ?>
                                    <span class="badge badge-secondary" style="opacity: 0.5;">Resolved</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if (!$has_reqs): ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Requests Found</h3>
                    <p>There are no leave requests matching the "<?php echo $filter; ?>" filter criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Premium Approval Modal -->
<div id="approvalModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 450px;">
        <div class="modal-header">
            <h3 id="modalTitle">Process Request</h3>
            <button type="button" class="modal-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="request_id" id="modal_request_id">
                <input type="hidden" name="action" id="modal_action">
                
                <div style="background: #f1f5f9; padding: 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid var(--primary);">
                    <small style="display: block; text-transform: uppercase; color: var(--text-muted); font-weight: 800; font-size: 0.65rem; margin-bottom: 5px;">Reviewing For</small>
                    <strong id="modalEmployeeName" style="color: var(--text-main); font-size: 1rem;">Employee Name</strong>
                </div>

                <div class="form-group">
                    <label>Official Remarks / Feedback</label>
                    <div class="form-icon-group">
                        <i class="fas fa-comment-dots" style="top: 20px; transform: none;"></i>
                        <textarea name="approval_reason" rows="4" placeholder="Enter reason for approval or rejection..." style="padding-top: 15px;"></textarea>
                    </div>
                    <small style="color: var(--text-muted);">This feedback will be visible to the employee.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn" id="modalSubmitBtn">Confirm Decision</button>
            </div>
        </form>
    </div>
</div>

<script>
function openApprovalModal(id, name, action) {
    const modal = document.getElementById('approvalModal');
    const submitBtn = document.getElementById('modalSubmitBtn');
    
    document.getElementById('modal_request_id').value = id;
    document.getElementById('modal_action').value = action;
    document.getElementById('modalEmployeeName').innerText = name;
    document.getElementById('modalTitle').innerText = action == 'approved' ? 'Approve Leave Request' : 'Reject Leave Request';
    
    // Update button styling based on action
    submitBtn.className = 'btn ' + (action == 'approved' ? 'btn-primary' : 'btn-danger');
    if(action == 'approved') {
        submitBtn.style.background = 'var(--success)';
        submitBtn.style.borderColor = 'var(--success)';
    } else {
        submitBtn.style.background = 'var(--danger)';
        submitBtn.style.borderColor = 'var(--danger)';
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('approvalModal').classList.remove('active');
    document.body.style.overflow = 'auto';
}

// Close on overlay click
document.getElementById('approvalModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php require_once '../includes/footer.php'; ?>
