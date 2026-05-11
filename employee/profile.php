<?php
require_once '../includes/header.php';
get_header("My Profile");

$user_id = $_SESSION['user_id'];
$user = hr_get_current_user();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $ui_accent = mysqli_real_escape_string($conn, $_POST['ui_accent']);
    
    $query_parts = [
        "email = '$email'",
        "phone = '$phone'",
        "address = '$address'",
        "ui_accent = '$ui_accent'"
    ];
    
    // Handle Profile Picture Upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['profile_pic']['tmp_name'];
        $file_name = $_FILES['profile_pic']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_exts)) {
            $new_file_name = 'profile_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_path = 'uploads/profiles/' . $new_file_name;
            
            if (move_uploaded_file($file_tmp, __DIR__ . '/../' . $upload_path)) {
                // Delete old pic if exists
                if (!empty($user['profile_pic']) && file_exists(__DIR__ . '/../' . $user['profile_pic'])) {
                    unlink(__DIR__ . '/../' . $user['profile_pic']);
                }
                $query_parts[] = "profile_pic = '$upload_path'";
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        }
    }
    
    if (!empty($_POST['new_password'])) {
        if (password_verify($_POST['curr_password'], $user['password'])) {
            $new_pass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $query_parts[] = "password = '$new_pass'";
        } else {
            $error = "Current password is incorrect.";
        }
    }
    
    if (empty($error)) {
        $query = "UPDATE employees SET " . implode(', ', $query_parts) . " WHERE id = $user_id";
        if (mysqli_query($conn, $query)) {
            $success = "Profile updated successfully!";
            $user = hr_get_current_user(); // Refresh
        } else {
            $error = "Error updating profile: " . mysqli_error($conn);
        }
    }
}

// Get Department and Grade Info
$query = "SELECT d.name as department_name, sg.grade_level, sg.base_salary 
          FROM employees e 
          LEFT JOIN departments d ON e.department_id = d.id 
          LEFT JOIN salary_grades sg ON e.salary_grade_id = sg.id 
          WHERE e.id = $user_id";
$info = mysqli_fetch_assoc(mysqli_query($conn, $query));
?>

<div class="dashboard-grid">
    <!-- Profile Sidebar -->
    <div class="card" style="text-align: center;">
        <div class="card-body">
            <?php 
            $pic = get_profile_pic($user);
            if ($pic): ?>
                <img src="<?php echo $pic; ?>" alt="Profile" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin: 0 auto 15px; box-shadow: var(--shadow-float); border: 4px solid white;">
            <?php else: ?>
                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 3rem; margin: 0 auto 15px; box-shadow: var(--shadow-float);">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
            <?php endif; ?>
            <h3 style="margin: 0; font-weight: 800;"><?php echo htmlspecialchars($user['full_name']); ?></h3>
            <p style="color: var(--text-muted); font-weight: 600;"><?php echo htmlspecialchars($user['position']); ?></p>
            <span class="role-badge" style="margin-top: 12px; background: rgba(99, 102, 241, 0.1); color: var(--primary); border: none;"><?php echo strtoupper($user['role']); ?></span>
            
            <div style="margin-top: 20px;">
                <a href="id_card.php" target="_blank" class="btn btn-outline" style="border-radius: 100px; font-size: 0.75rem; width: 100%; justify-content: center;"><i class="fas fa-id-card" style="margin-right: 8px;"></i> Generate Digital ID</a>
            </div>

            <div style="text-align: left; border-top: 1px solid var(--border); padding-top: 30px; margin-top: 30px;">
                <p style="margin-bottom: 15px; font-size: 0.9rem;"><i class="fas fa-id-badge" style="width: 25px; color: var(--primary);"></i> <span style="color: var(--text-muted);">Employee ID:</span> <strong><?php echo $user['employee_id']; ?></strong></p>
                <p style="margin-bottom: 15px; font-size: 0.9rem;"><i class="fas fa-building" style="width: 25px; color: var(--primary);"></i> <span style="color: var(--text-muted);">Department:</span> <strong><?php echo htmlspecialchars($info['department_name'] ?? 'General'); ?></strong></p>
                <p style="margin-bottom: 15px; font-size: 0.9rem;"><i class="fas fa-layer-group" style="width: 25px; color: var(--primary);"></i> <span style="color: var(--text-muted);">Grade:</span> <span class="badge badge-primary"><?php echo htmlspecialchars($info['grade_level'] ?? 'N/A'); ?></span></p>
                <p style="margin-bottom: 0; font-size: 0.9rem;"><i class="fas fa-calendar-alt" style="width: 25px; color: var(--primary);"></i> <span style="color: var(--text-muted);">Joined:</span> <strong><?php echo date('M Y', strtotime($user['hire_date'])); ?></strong></p>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="card">
        <div class="card-header">
            <h3>Update Profile Information</h3>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Profile Picture</label>
                    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 10px;">
                        <div id="imagePreviewContainer" style="width: 80px; height: 80px; border-radius: 12px; overflow: hidden; border: 2px dashed var(--border); display: flex; align-items: center; justify-content: center; background: #f8fafc;">
                            <i class="fas fa-image" style="color: var(--border); font-size: 1.5rem;"></i>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="profile_pic" id="profile_pic_input" accept="image/*" style="font-size: 0.8rem;">
                            <p style="color: var(--text-muted); font-size: 0.75rem; margin-top: 5px;">Recommended: Square JPG/PNG (max 2MB)</p>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="e.g. +1 234 567 890">
                </div>
                <div class="form-group">
                    <label>Home Address</label>
                    <textarea name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label>Personal Dashboard Accent</label>
                    <div class="form-icon-group">
                        <i class="fas fa-palette"></i>
                        <input type="color" name="ui_accent" value="<?php echo $user['ui_accent'] ?? '#6366f1'; ?>" style="height: 45px; padding: 5px;">
                    </div>
                    <small style="color: var(--text-muted);">Customize the primary color of your personal dashboard.</small>
                </div>

                <hr style="margin: 30px 0; border: none; border-top: 1px solid var(--border);">
                <h4 style="font-weight: 800; margin-bottom: 10px;">Security</h4>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px;">Leave password fields blank if you don't want to change it.</p>

                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="curr_password" placeholder="Verify current password">
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="Enter new password">
                </div>

                <div style="text-align: right; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary" style="padding: 14px 40px; font-size: 0.95rem;">Save Profile Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Security Log -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-shield-alt" style="margin-right: 10px; color: var(--primary);"></i> Recent Security Activity</h3>
        </div>
        <div class="card-body" style="padding: 25px;">
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php 
                $logins = mysqli_query($conn, "SELECT * FROM login_logs WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 3");
                if (mysqli_num_rows($logins) > 0):
                    while($log = mysqli_fetch_assoc($logins)): ?>
                        <div style="display: flex; gap: 15px; align-items: flex-start;">
                            <div style="width: 35px; height: 35px; border-radius: 50%; background: #f8fafc; color: var(--success); display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid var(--border);">
                                <i class="fas fa-sign-in-alt" style="font-size: 0.8rem;"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="font-size: 0.85rem; margin: 0; color: var(--text-main);">Successful Login from <strong><?php echo $log['ip_address']; ?></strong></p>
                                <small style="color: var(--text-muted); font-size: 0.75rem;"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                    <p style="text-align: center; color: var(--text-muted); font-size: 0.8rem; margin: 0;">No recent login history recorded.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
