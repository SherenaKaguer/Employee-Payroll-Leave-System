<?php
require_once '../includes/header.php';
get_header("Help & Feedback");

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    $query = "INSERT INTO feedback (user_id, subject, message) VALUES ($user_id, '$subject', '$message')";
    if (mysqli_query($conn, $query)) {
        $success = "Feedback submitted! Our HR team will review it shortly.";
    }
}

$my_feedback = mysqli_query($conn, "SELECT * FROM feedback WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<div class="dashboard-grid">
    <!-- Feedback Form -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-paper-plane" style="margin-right: 10px; color: var(--primary);"></i> Submit Feedback</h3>
        </div>
        <div class="card-body" style="padding: 40px;">
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 30px;">Have a suggestion, question, or need assistance? Use the form below to reach out to the HR department directly.</p>
            
            <form method="POST">
                <div class="form-group">
                    <label>Inquiry Subject</label>
                    <div class="form-icon-group">
                        <i class="fas fa-tag"></i>
                        <input type="text" name="subject" required placeholder="e.g. Payroll Discrepancy, Suggestion">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Detailed Message</label>
                    <div class="form-icon-group">
                        <i class="fas fa-pen-nib" style="top: 20px; transform: none;"></i>
                        <textarea name="message" rows="6" required placeholder="Please describe your inquiry in detail..."></textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" style="padding: 14px;">Send Feedback <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></button>
            </form>
        </div>
    </div>

    <!-- Feedback History -->
    <div class="card">
        <div class="card-header">
            <h3>My Inquiries</h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php while($f = mysqli_fetch_assoc($my_feedback)): 
                    $st = $f['status'];
                    $st_class = $st == 'new' ? 'badge-danger' : ($st == 'reviewed' ? 'badge-warning' : 'badge-success');
                ?>
                    <div style="padding: 15px; border: 1px solid var(--border); border-radius: 12px; background: #f8fafc;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <strong style="font-size: 0.85rem; color: var(--text-main);"><?php echo htmlspecialchars($f['subject']); ?></strong>
                            <span class="badge <?php echo $st_class; ?>" style="font-size: 0.6rem;"><?php echo strtoupper($st); ?></span>
                        </div>
                        <p style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.3; margin: 0;"><?php echo substr(htmlspecialchars($f['message']), 0, 100); ?>...</p>
                        <small style="display: block; margin-top: 10px; font-size: 0.65rem; color: var(--text-muted);"><?php echo date('M d, Y', strtotime($f['created_at'])); ?></small>
                    </div>
                <?php endwhile; ?>
                
                <?php if (mysqli_num_rows($my_feedback) == 0): ?>
                    <p style="text-align: center; color: var(--text-muted); font-size: 0.8rem; margin: 20px 0;">No previous inquiries.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
