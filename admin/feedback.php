<?php
require_once '../includes/header.php';
get_header("Staff Feedback & Inquiries");

// Handle Status Update
if (isset($_POST['update_status'])) {
    $fid = (int)$_POST['feedback_id'];
    $new_status = mysqli_real_escape_string($conn, $_POST['status']);
    if (mysqli_query($conn, "UPDATE feedback SET status = '$new_status' WHERE id = $fid")) {
        hr_audit_log('Update Feedback Status', "Feedback ID $fid marked as $new_status");
        $success = "Feedback status updated!";
    }
}

$query = "SELECT f.*, e.full_name, e.employee_id 
          FROM feedback f 
          JOIN employees e ON f.user_id = e.id 
          ORDER BY f.created_at DESC";
$feedback = mysqli_query($conn, $query);
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-comments" style="margin-right: 10px; color: var(--primary);"></i> Employee Voice Portal</h3>
        <span class="badge badge-primary">Admin Review Mode</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Subject</th>
                        <th>Message Preview</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_fb = false;
                    while ($f = mysqli_fetch_assoc($feedback)): 
                        $has_fb = true;
                    ?>
                    <tr>
                        <td><small><?php echo date('M d, Y', strtotime($f['created_at'])); ?></small></td>
                        <td>
                            <strong><?php echo htmlspecialchars($f['full_name']); ?></strong><br>
                            <small><?php echo $f['employee_id']; ?></small>
                        </td>
                        <td><strong><?php echo htmlspecialchars($f['subject']); ?></strong></td>
                        <td><div style="max-width: 250px; font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;"><?php echo htmlspecialchars($f['message']); ?></div></td>
                        <td>
                            <?php 
                            $st = $f['status'];
                            $st_class = $st == 'new' ? 'badge-danger' : ($st == 'reviewed' ? 'badge-warning' : 'badge-success');
                            echo "<span class='badge $st_class'>" . strtoupper($st) . "</span>";
                            ?>
                        </td>
                        <td>
                            <form method="POST" style="display: flex; gap: 5px;">
                                <input type="hidden" name="feedback_id" value="<?php echo $f['id']; ?>">
                                <select name="status" style="font-size: 0.7rem; padding: 5px; border-radius: 6px; border: 1px solid var(--border);">
                                    <option value="new" <?php echo $st == 'new' ? 'selected' : ''; ?>>New</option>
                                    <option value="reviewed" <?php echo $st == 'reviewed' ? 'selected' : ''; ?>>Reviewed</option>
                                    <option value="resolved" <?php echo $st == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-outline" style="padding: 5px 10px;"><i class="fas fa-check"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if (!$has_fb): ?>
                <div class="empty-state">
                    <i class="fas fa-comment-slash"></i>
                    <h3>Quiet Channels</h3>
                    <p>No employee feedback has been submitted yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
