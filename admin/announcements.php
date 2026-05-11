<?php
require_once '../includes/header.php';
get_header("Company Announcements");

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (mysqli_query($conn, "DELETE FROM announcements WHERE id = $id")) {
        hr_audit_log('Delete Announcement', "Removed announcement ID: $id");
        $success = "Announcement removed successfully!";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    $admin_id = $_SESSION['user_id'];
    
    if (isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $query = "UPDATE announcements SET title = '$title', message = '$message', priority = '$priority' WHERE id = $id";
        $action = "Update Announcement";
    } else {
        $query = "INSERT INTO announcements (title, message, priority, created_by) VALUES ('$title', '$message', '$priority', $admin_id)";
        $action = "Post Announcement";
    }

    if (mysqli_query($conn, $query)) {
        hr_audit_log($action, "Title: $title");
        $success = "Announcement published successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

$edit_ann = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_ann = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM announcements WHERE id = $id"));
}

$announcements = mysqli_query($conn, "SELECT a.*, e.full_name as author FROM announcements a LEFT JOIN employees e ON a.created_by = e.id ORDER BY a.created_at DESC");
?>

<div class="dashboard-grid">
    <!-- Form Side -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas <?php echo $edit_ann ? 'fa-edit' : 'fa-bullhorn'; ?>" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $edit_ann ? 'Edit' : 'Post'; ?> Announcement</h3>
        </div>
        <div class="card-body" style="padding: 40px;">
            <form method="POST">
                <?php if ($edit_ann): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_ann['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Bulletin Title</label>
                    <div class="form-icon-group">
                        <i class="fas fa-heading"></i>
                        <input type="text" name="title" required value="<?php echo $edit_ann['title'] ?? ''; ?>" placeholder="e.g. Holiday Notice">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Urgency Level</label>
                    <div class="form-icon-group">
                        <i class="fas fa-exclamation-triangle"></i>
                        <select name="priority" required>
                            <option value="low" <?php echo ($edit_ann['priority'] ?? '') == 'low' ? 'selected' : ''; ?>>Low - General Info</option>
                            <option value="medium" <?php echo ($edit_ann['priority'] ?? '') == 'medium' ? 'selected' : ''; ?>>Medium - Important</option>
                            <option value="high" <?php echo ($edit_ann['priority'] ?? '') == 'high' ? 'selected' : ''; ?>>High - Critical Action</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Message Content</label>
                    <div class="form-icon-group">
                        <i class="fas fa-pen-nib" style="top: 20px; transform: none;"></i>
                        <textarea name="message" rows="6" required placeholder="Write your company-wide update here..."><?php echo $edit_ann['message'] ?? ''; ?></textarea>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary btn-block"><?php echo $edit_ann ? 'Update' : 'Publish'; ?> Update <i class="fas fa-paper-plane" style="margin-left: 8px;"></i></button>
                    <?php if ($edit_ann): ?>
                        <a href="announcements.php" class="btn btn-outline btn-block" style="margin-top: 10px;">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- History Side -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-history" style="margin-right: 10px; color: var(--primary);"></i> Bulletin History</h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php 
                $has_ann = false;
                while($ann = mysqli_fetch_assoc($announcements)): 
                    $has_ann = true;
                    $p_class = $ann['priority'] == 'high' ? 'badge-danger' : ($ann['priority'] == 'medium' ? 'badge-warning' : 'badge-info');
                ?>
                    <div style="padding: 20px; border: 1px solid var(--border); border-radius: 16px; background: #f8fafc; position: relative;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                            <span class="badge <?php echo $p_class; ?>" style="font-size: 0.6rem; text-transform: uppercase;"><?php echo $ann['priority']; ?></span>
                            <div style="display: flex; gap: 5px;">
                                <a href="?edit=<?php echo $ann['id']; ?>" class="btn-icon" style="color: var(--primary); font-size: 0.8rem;"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo $ann['id']; ?>" class="btn-icon" style="color: var(--danger); font-size: 0.8rem;" onclick="return confirm('Delete this post?')"><i class="fas fa-trash"></i></a>
                            </div>
                        </div>
                        <h4 style="margin: 0 0 5px 0; font-weight: 800; color: var(--text-main);"><?php echo htmlspecialchars($ann['title']); ?></h4>
                        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 15px;"><?php echo nl2br(htmlspecialchars($ann['message'])); ?></p>
                        <div style="border-top: 1px solid rgba(0,0,0,0.05); padding-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                            <small style="font-weight: 700; color: var(--text-muted);">By <?php echo htmlspecialchars($ann['author'] ?? 'Admin'); ?></small>
                            <small style="color: var(--text-muted);"><?php echo date('M d, Y', strtotime($ann['created_at'])); ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
                
                <?php if (!$has_ann): ?>
                    <div class="empty-state">
                        <i class="fas fa-comment-slash"></i>
                        <h3>No Bulletins</h3>
                        <p>Keep your team updated by posting your first company-wide announcement.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
