<?php
require_once 'includes/header.php';
get_header("All Notifications");

$user_id = $_SESSION['user_id'];

// Handle Mark all as read
if (isset($_POST['mark_all'])) {
    mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
    $success = "All notifications marked as read!";
}

$notifs = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header">
        <h3><i class="fas fa-bell" style="margin-right: 10px; color: var(--primary);"></i> Notification Center</h3>
        <form method="POST">
            <button type="submit" name="mark_all" class="btn btn-outline" style="font-size: 0.75rem;"><i class="fas fa-check-double"></i> Mark all read</button>
        </form>
    </div>
    <div class="card-body" style="padding: 0;">
        <div style="display: flex; flex-direction: column;">
            <?php 
            $has_any = false;
            while($n = mysqli_fetch_assoc($notifs)): 
                $has_any = true;
            ?>
                <div class="notif-item <?php echo $n['is_read'] ? '' : 'unread'; ?> notif-<?php echo $n['type']; ?>" style="border-bottom: 1px solid var(--border); padding: 25px 30px;">
                    <div style="display: flex; gap: 20px; align-items: flex-start;">
                        <div class="notif-icon" style="width: 45px; height: 45px; font-size: 1.2rem;">
                            <i class="fas <?php echo $n['type'] == 'success' ? 'fa-check' : ($n['type'] == 'warning' ? 'fa-exclamation' : 'fa-info'); ?>"></i>
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                                <h4 style="margin: 0 0 5px 0; font-weight: 800; color: var(--text-main);"><?php echo htmlspecialchars($n['title']); ?></h4>
                                <small style="color: var(--text-muted); font-weight: 700;"><?php echo date('M d, Y H:i', strtotime($n['created_at'])); ?></small>
                            </div>
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin: 0; line-height: 1.5;"><?php echo htmlspecialchars($n['message']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>

            <?php if (!$has_any): ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h3>Quiet Inbox</h3>
                    <p>You don't have any notifications yet. System alerts will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
