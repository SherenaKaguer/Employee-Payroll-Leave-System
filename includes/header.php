<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

function get_header($title = "HR Payroll System") {
    global $conn;
    $user = hr_get_current_user();
    $role = $_SESSION['role'] ?? 'employee';
    $is_admin = hr_is_admin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - HR Payroll System</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>favicon.svg">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: <?php echo $user['ui_accent'] ?? get_setting('primary_color') ?? '#6366f1'; ?>;
            --accent: <?php echo get_setting('accent_color') ?? '#f472b6'; ?>;
        }
        @keyframes textShine {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }
    </style>
</head>
<body class="sidebar-open">
    <div class="mobile-overlay" id="mobile-overlay" onclick="var b=document.getElementById('sidebar-toggle'); if(b){ b.click(); }"></div>
    <!-- Top Progress Loader -->
    <div id="nprogress-bar"></div>
    
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo-wrapper">
                    <img src="<?php echo BASE_URL; ?>favicon.svg" alt="Logo" width="40">
                    <h2 style="background: linear-gradient(to right, #818cf8, #f472b6, #818cf8); background-size: 200% auto; -webkit-background-clip: text; -webkit-text-fill-color: transparent; animation: textShine 3s linear infinite;"><?php echo get_setting('company_name') ?? 'HRMS'; ?></h2>
                </div>
                <span class="role-badge"><?php echo strtoupper($role); ?></span>
            </div>
            <nav class="nav-menu">
                <div class="nav-section">Main</div>
                <ul>
                    <?php if ($is_admin): ?>
                        <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-gauge"></i> Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/employees.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'employees.php') ? 'active' : ''; ?>"><i class="fas fa-users"></i> Employees</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/departments.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'departments.php') ? 'active' : ''; ?>"><i class="fas fa-building"></i> Departments</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/org_chart.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'org_chart.php') ? 'active' : ''; ?>"><i class="fas fa-sitemap"></i> Org Chart</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/salary_grades.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'salary_grades.php') ? 'active' : ''; ?>"><i class="fas fa-layer-group"></i> Salary Grades</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/deductions.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'deductions.php') ? 'active' : ''; ?>"><i class="fas fa-hand-holding-usd"></i> Deductions</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/leave_request.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'leave_request.php') ? 'active' : ''; ?>">
                                <i class="fas fa-calendar-alt"></i> Leave Requests
                                <?php 
                                $pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending'"))['count'];
                                if ($pending_count > 0): ?>
                                    <span class="nav-badge"><?php echo $pending_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li><a href="<?php echo BASE_URL; ?>admin/calendar.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'calendar.php') ? 'active' : ''; ?>"><i class="fas fa-calendar-week"></i> Workforce Calendar</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/announcements.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'announcements.php') ? 'active' : ''; ?>"><i class="fas fa-bullhorn"></i> Bulletin Board</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/payroll.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'payroll.php') ? 'active' : ''; ?>"><i class="fas fa-money-bill-wave"></i> Payroll</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/reports.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'reports.php') ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> Reports</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/settings.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'settings.php') ? 'active' : ''; ?>"><i class="fas fa-cogs"></i> Settings</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/feedback.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'feedback.php') ? 'active' : ''; ?>"><i class="fas fa-comment-alt"></i> Staff Feedback</a></li>
                        <li><a href="<?php echo BASE_URL; ?>admin/audit_logs.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'audit_logs.php') ? 'active' : ''; ?>"><i class="fas fa-shield-alt"></i> Audit Logs</a></li>
                        <li><a href="<?php echo BASE_URL; ?>employee/profile.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'profile.php') ? 'active' : ''; ?>"><i class="fas fa-user-circle"></i> My Profile</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>employee/dashboard.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'dashboard.php') ? 'active' : ''; ?>"><i class="fas fa-gauge"></i> My Dashboard</a></li>
                        <li><a href="<?php echo BASE_URL; ?>employee/directory.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'directory.php') ? 'active' : ''; ?>"><i class="fas fa-address-book"></i> Company Directory</a></li>
                        <li><a href="<?php echo BASE_URL; ?>employee/feedback.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'feedback.php') ? 'active' : ''; ?>"><i class="fas fa-comment-dots"></i> Feedback Portal</a></li>
                        <li><a href="<?php echo BASE_URL; ?>employee/calendar.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'calendar.php') ? 'active' : ''; ?>"><i class="fas fa-calendar-day"></i> My Calendar</a></li>
                        <li><a href="<?php echo BASE_URL; ?>employee/leave_request.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'leave_request.php') ? 'active' : ''; ?>"><i class="fas fa-paper-plane"></i> Request Leave</a></li>
                        <li><a href="<?php echo BASE_URL; ?>employee/my_payroll.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'my_payroll.php') ? 'active' : ''; ?>"><i class="fas fa-wallet"></i> My Payroll</a></li>
                        <li><a href="<?php echo BASE_URL; ?>employee/profile.php" class="<?php echo str_contains($_SERVER['PHP_SELF'], 'profile.php') ? 'active' : ''; ?>"><i class="fas fa-user-circle"></i> My Profile</a></li>
                    <?php endif; ?>
                    <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.05); margin: 20px 10px;">
                    <li><a href="<?php echo BASE_URL; ?>logout.php" class="js-confirm-logout" style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> Secure Logout</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="bg-blob blob-1"></div>
            <div class="bg-blob blob-2"></div>
            <!-- Top Bar -->
            <header class="top-bar">
                <div class="top-bar-left">
                    <button id="sidebar-toggle" class="sidebar-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="breadcrumb">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <a href="<?php echo BASE_URL . ($is_admin ? 'admin' : 'employee'); ?>/dashboard.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.8rem; font-weight: 700;"><i class="fas fa-home"></i></a>
                            <i class="fas fa-chevron-right" style="font-size: 0.6rem; color: var(--border);"></i>
                            <h1 style="margin: 0; font-size: 1.2rem;"><?php echo $title; ?></h1>
                        </div>
                    </div>
                </div>

                <?php if ($is_admin): ?>
                <div class="top-search-wrapper" style="flex: 1; max-width: 400px; margin: 0 40px; position: relative;">
                    <div class="form-icon-group" style="margin-bottom: 0;">
                        <i class="fas fa-search"></i>
                        <input type="text" id="globalEmployeeSearch" placeholder="Quick find staff..." style="background: var(--bg-app); border-radius: 100px; padding: 8px 15px 8px 45px; font-size: 0.85rem;">
                    </div>
                    <div id="globalSearchResults" style="display: none; position: absolute; top: 110%; left: 0; right: 0; background: white; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid var(--border); z-index: 1001; max-height: 300px; overflow-y: auto; padding: 10px;">
                    </div>
                </div>
                <?php endif; ?>

                <div class="user-info">
                    <!-- Help Center -->
                    <button class="btn-icon" title="System Help Center" onclick="openHelpModal()" style="background: none; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer; margin-right: 15px;">
                        <i class="far fa-question-circle"></i>
                    </button>

                     <!-- Theme Toggle -->
                    <button id="theme-toggle" class="theme-toggle" type="button" title="Toggle Dark/Light Mode">
                        <i class="fas fa-moon"></i>
                    </button>

                    <!-- Notifications -->
                    <div class="notifications-wrapper" style="position: relative; margin-right: 15px;">
                        <?php 
                        $user_id = $_SESSION['user_id'] ?? 0;
                        $unread_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM notifications WHERE user_id = $user_id AND is_read = 0"))['count'];
                        ?>
                        <button id="notif-bell" class="btn-icon" type="button" style="background: none; border: none; font-size: 1.2rem; color: var(--text-muted); cursor: pointer; position: relative;">
                            <i class="far fa-bell"></i>
                            <?php if ($unread_count > 0): ?>
                                <span class="badge-pulse" style="position: absolute; top: -5px; right: -5px; width: 10px; height: 10px; background: var(--danger); border-radius: 50%; border: 2px solid white; display: block;"></span>
                            <?php endif; ?>
                        </button>

                        <div class="notifications-dropdown" id="notif-dropdown">
                            <div class="notif-header">
                                <h4>Notifications</h4>
                                <?php if ($unread_count > 0): ?>
                                    <span class="badge badge-primary" style="font-size: 0.6rem;"><?php echo $unread_count; ?> New</span>
                                <?php endif; ?>
                            </div>
                            <div class="notif-body">
                                <?php 
                                $notifs = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
                                if (mysqli_num_rows($notifs) > 0): 
                                    while($n = mysqli_fetch_assoc($notifs)):
                                ?>
                                    <a href="#" class="notif-item <?php echo $n['is_read'] ? '' : 'unread'; ?> notif-<?php echo $n['type']; ?>">
                                        <div class="notif-icon"><i class="fas <?php echo $n['type'] == 'success' ? 'fa-check' : ($n['type'] == 'warning' ? 'fa-exclamation' : 'fa-info'); ?>"></i></div>
                                        <div class="notif-content">
                                            <h5><?php echo htmlspecialchars($n['title']); ?></h5>
                                            <p><?php echo htmlspecialchars($n['message']); ?></p>
                                            <span class="notif-time"><?php echo date('M d, H:i', strtotime($n['created_at'])); ?></span>
                                        </div>
                                    </a>
                                <?php endwhile; else: ?>
                                    <div style="padding: 40px 20px; text-align: center; color: var(--text-muted);">
                                        <i class="fas fa-bell-slash" style="font-size: 2rem; opacity: 0.2; margin-bottom: 10px; display: block;"></i>
                                        <p style="font-size: 0.85rem; margin: 0;">No notifications yet</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="notif-footer">
                                <a href="<?php echo BASE_URL; ?>notifications.php">View All Notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="profile-wrapper" style="position: relative;">
                        <div id="profile-trigger" style="display: flex; align-items: center; gap: 12px; cursor: pointer; padding: 5px 10px; border-radius: 12px; transition: background 0.3s;">
                            <?php 
                            $pic = get_profile_pic($user);
                            if ($pic): ?>
                                <img src="<?php echo $pic; ?>" alt="User" style="width: 35px; height: 35px; border-radius: 10px; object-fit: cover; border: 1px solid var(--border);">
                            <?php else: ?>
                                <div style="width: 35px; height: 35px; border-radius: 10px; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 800;">
                                    <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div style="display: flex; flex-direction: column; line-height: 1.1;">
                                <span style="font-weight: 800; font-size: 0.85rem; color: var(--text-main);"><?php echo explode(' ', $user['full_name'] ?? 'User')[0]; ?></span>
                                <small style="font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;"><?php echo $role; ?></small>
                            </div>
                            <i class="fas fa-chevron-down" style="font-size: 0.7rem; color: var(--text-muted);"></i>
                        </div>

                        <div class="profile-dropdown" id="profile-dropdown">
                            <div class="pd-header">
                                <?php if ($pic): ?>
                                    <img src="<?php echo $pic; ?>" class="pd-avatar" alt="Avatar">
                                <?php else: ?>
                                    <div class="pd-avatar" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800;">
                                        <?php echo strtoupper(substr($user['full_name'] ?? 'U', 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="pd-name"><?php echo htmlspecialchars($user['full_name'] ?? 'User'); ?></span>
                                <span class="pd-email"><?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></span>
                            </div>
                            <div class="pd-menu">
                                <a href="<?php echo BASE_URL; ?>employee/profile.php" class="pd-item"><i class="fas fa-user-circle"></i> Profile Settings</a>
                                <?php if($is_admin): ?>
                                    <a href="<?php echo BASE_URL; ?>admin/settings.php" class="pd-item"><i class="fas fa-cog"></i> System Config</a>
                                <?php endif; ?>
                                <a href="#" class="pd-item" onclick="openHelpModal()"><i class="fas fa-question-circle"></i> Help & Support</a>
                                <hr style="margin: 10px 0; border: none; border-top: 1px solid var(--border);">
                                <a href="<?php echo BASE_URL; ?>logout.php" class="pd-item logout js-confirm-logout"><i class="fas fa-sign-out-alt"></i> Sign Out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="content-body">
<?php
}
?>
