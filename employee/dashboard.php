<?php
require_once '../includes/header.php';
get_header("My Dashboard");

$user = hr_get_current_user();
$user_id = $_SESSION['user_id'];

// Get leave summary
$leave_query = "SELECT COUNT(*) as total_requests, 
                       SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                       SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM leave_requests 
                WHERE employee_id = $user_id";
$leave_result = mysqli_query($conn, $leave_query);
$leave_stats = mysqli_fetch_assoc($leave_result);

// Get recent payroll
$payroll_query = "SELECT * FROM payroll_records 
                  WHERE employee_id = $user_id 
                  ORDER BY payroll_month DESC LIMIT 5";
$payroll_result = mysqli_query($conn, $payroll_query);

// Get pending leaves
$pending_leaves_query = "SELECT * FROM leave_requests 
                         WHERE employee_id = $user_id AND status = 'pending'
                         ORDER BY request_date DESC";
$pending_leaves = mysqli_query($conn, $pending_leaves_query);

// Get approved leaves (upcoming)
$approved_leaves_query = "SELECT * FROM leave_requests 
                          WHERE employee_id = $user_id AND status = 'approved' AND end_date >= CURDATE()
                          ORDER BY start_date ASC";
$approved_leaves = mysqli_query($conn, $approved_leaves_query);

$info_query = "SELECT d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = $user_id";
$info = mysqli_fetch_assoc(mysqli_query($conn, $info_query));

// Global Settings for calculations
$settings_res = mysqli_query($conn, "SELECT * FROM system_settings");
$settings = [];
while($s = mysqli_fetch_assoc($settings_res)) {
    $settings[$s['setting_key']] = $s['setting_value'];
}
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-content">
        <?php 
        // Calculate annual earnings
        $year_start = date('Y') . '-01-01';
        $annual_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(net_salary) as total FROM payroll_records WHERE employee_id = $user_id AND payroll_month >= '$year_start'"))['total'] ?? 0;
        ?>
        <h1 class="hero-title"><span class="dynamic-greeting">Hello</span>, <span class="shine-text"><?php echo explode(' ', $user['full_name'])[0]; ?></span>! ✨</h1>
        <p class="hero-subtitle">Welcome to your personalized portal for <strong><?php echo get_setting('company_name') ?? 'Elite HRMS'; ?></strong>. Track your progress, request leave, and access your high-fidelity payslips.</p>
        
        <div class="hero-stats">
            <div class="hero-stat-item">
                <small style="display: block; opacity: 0.7; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; margin-bottom: 5px;">Leave Balance</small>
                <span style="font-weight: 800; font-size: 1.2rem;"><i class="fas fa-calendar-day" style="opacity: 0.8; margin-right: 5px;"></i> <?php echo $user['leave_balance']; ?> Days</span>
            </div>
            <div class="hero-stat-item">
                <small style="display: block; opacity: 0.7; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; margin-bottom: 5px;">Yearly Earnings</small>
                <span style="font-weight: 800; font-size: 1.2rem;" class="stat-value"><?php echo $annual_total; ?></span>
            </div>
            <div style="background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 10px 20px; border-radius: 100px; display: flex; align-items: center; gap: 10px; border: 1px solid rgba(255,255,255,0.15);">
                <i class="fas fa-users" style="font-size: 0.8rem;"></i>
                <span style="font-weight: 700; font-size: 0.8rem;"><?php echo htmlspecialchars($info['department_name'] ?? 'General'); ?> Team</span>
            </div>
        </div>
    </div>

    <!-- Decorative SVG Shape -->
    <svg style="position: absolute; right: -50px; bottom: -50px; opacity: 0.15; width: 400px; height: 400px;" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <path fill="#FFFFFF" d="M36.7,-64.8C46.8,-58.2,53.8,-46.8,61.1,-35.6C68.4,-24.4,76.1,-13.4,77.9,-1.4C79.7,10.7,75.7,23.8,68.4,35.3C61.2,46.8,50.7,56.7,38.6,63.4C26.5,70.1,12.7,73.6,-0.9,75.2C-14.5,76.8,-29,76.5,-41.8,70.1C-54.6,63.7,-65.7,51.3,-72.1,37.3C-78.5,23.3,-80.1,7.6,-78.4,-7.8C-76.8,-23.2,-72,-38.3,-62.4,-48.9C-52.8,-59.5,-38.4,-65.6,-25,-69.5C-11.6,-73.4,0.8,-75.1,12.5,-73.1C24.2,-71.1,35.2,-65.4,36.7,-64.8Z" transform="translate(100 100)" />
    </svg>
</div>

<?php 
// Get latest high-priority announcement
$latest_ann = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM announcements ORDER BY priority DESC, created_at DESC LIMIT 1"));
if ($latest_ann): 
    $p_color = $latest_ann['priority'] == 'high' ? 'var(--danger)' : ($latest_ann['priority'] == 'medium' ? 'var(--warning)' : 'var(--primary)');
?>
<div class="card" style="border-left: 5px solid <?php echo $p_color; ?>; margin-bottom: 32px; background: #fff; overflow: hidden;">
    <div class="card-body" style="padding: 20px 30px; display: flex; align-items: center; justify-content: space-between; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #f8fafc; color: <?php echo $p_color; ?>; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div>
                <span class="badge badge-secondary" style="font-size: 0.6rem; text-transform: uppercase; margin-bottom: 5px;"><?php echo $latest_ann['priority']; ?> Bulletin</span>
                <h4 style="margin: 0; font-weight: 800; color: var(--text-main);"><?php echo htmlspecialchars($latest_ann['title']); ?></h4>
                <p style="margin: 5px 0 0 0; font-size: 0.8rem; color: var(--text-muted);"><?php echo substr(htmlspecialchars($latest_ann['message']), 0, 80) . '...'; ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card stat-info has-tooltip">
        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-value"><?php echo $user['leave_balance']; ?></div>
        <div class="stat-label">Available Leave Days</div>
        <span class="tooltip">Days you can still take</span>
    </div>
    <div class="stat-card stat-primary has-tooltip">
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
        <div class="stat-value"><?php echo $leave_stats['total_requests'] ?? 0; ?></div>
        <div class="stat-label">Total Requests</div>
        <span class="tooltip">All-time applications</span>
    </div>
    <div class="stat-card stat-success has-tooltip">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value"><?php echo $leave_stats['approved'] ?? 0; ?></div>
        <div class="stat-label">Approved Leaves</div>
        <span class="tooltip">Successful bookings</span>
    </div>
    <div class="stat-card stat-warning has-tooltip">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-value"><?php echo $leave_stats['pending'] ?? 0; ?></div>
        <div class="stat-label">Pending Requests</div>
        <span class="tooltip">Awaiting manager review</span>
    </div>
</div>

<div class="dashboard-grid" style="grid-template-columns: 1.5fr 1fr; gap: 30px;">
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-file-invoice-dollar" style="margin-right: 10px; color: var(--success);"></i> Recent Payroll</h3>
                <a href="my_payroll.php" class="btn btn-primary" style="font-size: 12px;">View History</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Net Salary</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($payroll_result)): ?>
                            <tr>
                                <td><strong><?php echo date('F Y', strtotime($row['payroll_month'])); ?></strong></td>
                                <td><strong class="text-gradient"><?php echo format_currency($row['net_salary']); ?></strong></td>
                                <td><?php echo get_status_badge($row['status']); ?></td>
                                <td><a href="payroll_view.php?id=<?php echo $row['id']; ?>" class="btn btn-outline" style="padding: 6px 10px;"><i class="fas fa-eye"></i></a></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($payroll_result) == 0): ?>
                                <tr><td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">No payroll records found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-line" style="margin-right: 10px; color: var(--primary);"></i> Salary Growth</h3>
            </div>
            <div class="card-body">
                <canvas id="personalPayrollChart" height="250"></canvas>
                <?php
                // Get personal payroll trend (last 6 months)
                $trend_query = "SELECT payroll_month, net_salary FROM payroll_records WHERE employee_id = $user_id ORDER BY payroll_month ASC LIMIT 6";
                $trend_res = mysqli_query($conn, $trend_query);
                $months_labels = []; $salaries_data = [];
                while($t = mysqli_fetch_assoc($trend_res)) {
                    $months_labels[] = date('M Y', strtotime($t['payroll_month']));
                    $salaries_data[] = (float)$t['net_salary'];
                }
                ?>
            </div>
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 30px;">
        <!-- Wellbeing Gauge -->
        <div class="card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none;">
            <div class="card-body" style="padding: 30px; text-align: center;">
                <div style="width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-heartbeat" style="font-size: 1.2rem;"></i>
                </div>
                <h4 style="margin: 0; font-weight: 800; font-size: 0.9rem;">Work-Life Balance</h4>
                <?php
                $entitlement = (int)($settings['default_leave_balance'] ?? 15);
                $used = $entitlement - (int)$user['leave_balance'];
                $wellbeing = $entitlement > 0 ? (1 - ($used / $entitlement)) * 100 : 100;
                ?>
                <div style="font-size: 1.8rem; font-weight: 900; margin: 5px 0;"><?php echo round($wellbeing); ?>%</div>
                <p style="font-size: 0.7rem; opacity: 0.9; margin: 0;">Maintaining healthy <br>recovery levels.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-clock" style="margin-right: 10px; color: var(--warning);"></i> Pending Leaves</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Type</th>
                                <th>Days</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($leave = mysqli_fetch_assoc($pending_leaves)): ?>
                            <tr>
                                <td><small><?php echo date('M d', strtotime($leave['start_date'])); ?></small></td>
                                <td><strong><?php echo ucfirst($leave['leave_type']); ?></strong></td>
                                <td><span class="badge badge-info"><?php echo $leave['total_days']; ?>d</span></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($pending_leaves) == 0): ?>
                                <tr><td colspan="3" style="text-align: center; padding: 40px; color: var(--text-muted);">All caught up!</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div style="padding: 20px;">
                    <a href="leave_request.php" class="btn btn-primary btn-block"><i class="fas fa-plus"></i> New Request</a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-check" style="margin-right: 10px; color: var(--success);"></i> Approved Leave Schedule</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Type</th>
                                <th>Days</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($leave = mysqli_fetch_assoc($approved_leaves)): ?>
                            <tr>
                                <td>
                                    <small><strong><?php echo date('M d', strtotime($leave['start_date'])); ?></strong> - <?php echo date('M d, Y', strtotime($leave['end_date'])); ?></small>
                                </td>
                                <td><span class="badge badge-success" style="font-size: 0.65rem; font-weight: 800;"><?php echo strtoupper($leave['leave_type']); ?></span></td>
                                <td><strong><?php echo $leave['total_days']; ?> days</strong></td>
                                <td><?php echo get_status_badge($leave['status']); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if (mysqli_num_rows($approved_leaves) == 0): ?>
                                <tr><td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">No approved leaves scheduled</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Earnings Projection -->
        <div class="card" style="background: #0f172a; color: white; border: none;">
            <div class="card-header" style="border-bottom-color: rgba(255,255,255,0.05);">
                <h3 style="color: white;"><i class="fas fa-magic" style="color: var(--primary-light); margin-right: 10px;"></i> 2026 Projection</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <small style="color: #94a3b8; font-weight: 800; font-size: 0.6rem; text-transform: uppercase;">Estimated Annual Net</small>
                <?php 
                $current_month_idx = (int)date('n');
                $projected_total = $current_month_idx > 0 ? ($annual_total / $current_month_idx) * 12 : 0;
                ?>
                <div style="font-size: 1.5rem; font-weight: 800; color: white; margin: 5px 0;"><?php echo format_currency($projected_total); ?></div>
                <p style="font-size: 0.7rem; color: #64748b; line-height: 1.4; margin-top: 10px;">Based on your current average monthly earnings.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-chart-pie" style="margin-right: 10px; color: var(--primary);"></i> Leave Distribution</h3>
            </div>
            <div class="card-body">
                <canvas id="personalLeaveChart" height="250"></canvas>
                <?php
                // Get personal leave types count
                $usage_query = "SELECT leave_type, SUM(total_days) as days FROM leave_requests WHERE employee_id = $user_id AND status = 'approved' GROUP BY leave_type";
                $usage_res = mysqli_query($conn, $usage_query);
                $types = []; $days = [];
                while($u = mysqli_fetch_assoc($usage_res)) {
                    $types[] = ucfirst($u['leave_type']);
                    $days[] = (int)$u['days'];
                }
                ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-users" style="margin-right: 10px; color: var(--primary);"></i> My Teammates</h3>
                <span class="badge badge-info" style="font-size: 0.6rem;"><?php echo htmlspecialchars($info['department_name'] ?? 'Team'); ?></span>
            </div>
            <div class="card-body" style="padding: 20px 25px;">
                <?php 
                // Get teammates
                $dept_id_query = mysqli_query($conn, "SELECT department_id FROM employees WHERE id = $user_id");
                $dept_id_row = mysqli_fetch_assoc($dept_id_query);
                $dept_id = $dept_id_row['department_id'] ?? 0;
                $teammates = mysqli_query($conn, "SELECT full_name, position, profile_pic FROM employees WHERE department_id = $dept_id AND id != $user_id LIMIT 4");
                
                if ($dept_id > 0 && mysqli_num_rows($teammates) > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php while($mate = mysqli_fetch_assoc($teammates)): ?>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php if (!empty($mate['profile_pic']) && file_exists(__DIR__ . '/../' . $mate['profile_pic'])): ?>
                                    <img src="<?php echo BASE_URL . $mate['profile_pic']; ?>" style="width: 32px; height: 32px; border-radius: 8px; object-fit: cover;">
                                <?php else: ?>
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #f1f5f9; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800;">
                                        <?php echo strtoupper(substr($mate['full_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <strong style="display: block; font-size: 0.8rem; color: var(--text-main);"><?php echo htmlspecialchars($mate['full_name']); ?></strong>
                                    <small style="color: var(--text-muted); font-size: 0.7rem;"><?php echo htmlspecialchars($mate['position']); ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: var(--text-muted); font-size: 0.8rem; margin: 10px 0;">You're the first in this team! 👋</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card" style="background: #f8fafc; border: 1px dashed var(--border); box-shadow: none;">
            <div class="card-body" style="padding: 25px; text-align: center;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: white; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; color: var(--primary);">
                    <i class="fas fa-user-shield" style="font-size: 1.5rem;"></i>
                </div>
                <h4 style="margin-bottom: 5px; font-weight: 800;">Secure Portal</h4>
                <p style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">Your personal data is encrypted and managed according to company security policies.</p>
                <a href="profile.php" class="btn btn-outline" style="margin-top: 15px; font-size: 0.75rem; border-radius: 100px;">Update Profile</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Leave Chart
    const ctxLeave = document.getElementById('personalLeaveChart').getContext('2d');
    new Chart(ctxLeave, {
        type: 'polarArea',
        data: {
            labels: <?php echo json_encode($types); ?>,
            datasets: [{
                data: <?php echo json_encode($days); ?>,
                backgroundColor: ['#6366f1', '#f472b6', '#10b981', '#f59e0b', '#3b82f6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { font: { family: 'Plus Jakarta Sans', weight: '700' } } }
            },
            scales: {
                r: { ticks: { display: false }, grid: { color: 'rgba(0,0,0,0.05)' } }
            }
        }
    });

    // Payroll Trend Chart
    const ctxPayroll = document.getElementById('personalPayrollChart').getContext('2d');
    new Chart(ctxPayroll, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months_labels); ?>,
            datasets: [{
                label: 'Net Salary ($)',
                data: <?php echo json_encode($salaries_data); ?>,
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: false, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
