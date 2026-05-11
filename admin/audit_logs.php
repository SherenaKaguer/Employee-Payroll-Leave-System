<?php
require_once '../includes/header.php';
get_header("Security Audit Logs");

// Get audit logs with user info
$query = "SELECT al.*, e.full_name, e.employee_id 
          FROM audit_logs al 
          LEFT JOIN employees e ON al.user_id = e.id 
          ORDER BY al.created_at DESC";
$logs = mysqli_query($conn, $query);
?>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-shield-alt" style="margin-right: 10px; color: var(--primary);"></i> Administrative Audit Trail</h3>
        <span class="badge badge-secondary" style="font-size: 0.7rem;">Forensic Tracking Active</span>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Admin User</th>
                        <th>Action Performed</th>
                        <th>Security Details</th>
                        <th>Origin IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_logs = false;
                    while ($log = mysqli_fetch_assoc($logs)): 
                        $has_logs = true;
                    ?>
                    <tr>
                        <td><small style="font-weight: 700; color: var(--text-muted);"><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></small></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 25px; height: 25px; border-radius: 6px; background: #eef2ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.6rem; font-weight: 800;">
                                    <?php echo strtoupper(substr($log['full_name'] ?? 'S', 0, 1)); ?>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($log['full_name'] ?? 'System / Anonymous'); ?></strong>
                                    <?php if($log['employee_id']): ?>
                                        <br><small style="color: var(--text-muted); font-size: 0.65rem;"><?php echo $log['employee_id']; ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-primary" style="font-size: 0.7rem; text-transform: uppercase;"><?php echo htmlspecialchars($log['action']); ?></span></td>
                        <td><div style="max-width: 300px; font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;"><?php echo htmlspecialchars($log['details']); ?></div></td>
                        <td><small style="font-family: monospace; color: var(--text-muted);"><?php echo $log['ip_address']; ?></small></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if (!$has_logs): ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h3>Clear Audit Trail</h3>
                    <p>No administrative actions have been recorded yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
