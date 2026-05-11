<?php
require_once '../includes/header.php';
get_header("Administrator Dashboard");

// Get statistics
$stats = [];
$stats['total_employees'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE role = 'employee'"))['count'];
$stats['pending_leaves'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_requests WHERE status = 'pending'"))['count'];
$stats['departments'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM departments"))['count'];

$current_month = date('Y-m') . '-01';
$payroll_stat = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(net_salary) as total FROM payroll_records WHERE payroll_month = '$current_month'"));
$stats['monthly_payroll'] = $payroll_stat['total'] ?? 0;

$stats['total_staff'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE role != 'admin'"))['count'];

// Get recent leave requests
$pending_requests = mysqli_query($conn, "SELECT lr.*, e.full_name, e.employee_id as emp_code, d.name as department 
                                          FROM leave_requests lr
                                          JOIN employees e ON lr.employee_id = e.id
                                          JOIN departments d ON e.department_id = d.id
                                          WHERE lr.status = 'pending'
                                          ORDER BY lr.request_date DESC
                                          LIMIT 5");
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">System <span class="shine-text">Commander</span></h1>
        <p class="hero-subtitle">You are viewing the command center for <strong><?php echo get_setting('company_name') ?? 'Elite HRMS'; ?></strong>. Manage workforce trends, financial disbursements, and operational security from one high-fidelity portal.</p>
        
        <div class="hero-stats">
            <div class="hero-stat-item">
                <small style="display: block; opacity: 0.7; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; margin-bottom: 5px;">Headcount</small>
                <span style="font-weight: 800; font-size: 1.2rem;" class="stat-value"><?php echo $stats['total_staff']; ?></span>
            </div>
            <div class="hero-stat-item">
                <small style="display: block; opacity: 0.7; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; margin-bottom: 5px;">Active Payroll</small>
                <span style="font-weight: 800; font-size: 1.2rem;" class="stat-value"><?php echo format_currency($stats['monthly_payroll']); ?></span>
            </div>
            <div class="hero-stat-item">
                <small style="display: block; opacity: 0.7; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; margin-bottom: 5px;">System Date</small>
                <span style="font-weight: 800; font-size: 1.2rem;"><?php echo date('M d, Y'); ?></span>
            </div>
        </div>
    </div>

    <!-- Decorative Elements -->
    <svg style="position: absolute; right: -50px; bottom: -50px; opacity: 0.15; width: 450px; height: 450px;" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
        <path fill="#FFFFFF" d="M44.7,-76.4C58.8,-69.2,71.8,-59.1,79.6,-46.2C87.4,-33.3,90.1,-17.7,89.3,-2.4C88.5,12.9,84.3,27.9,76.4,40.8C68.5,53.7,56.9,64.5,43.4,72.4C29.9,80.3,14.5,85.3,-0.7,86.5C-15.9,87.7,-31.8,85.1,-45.8,77.5C-59.8,69.9,-71.9,57.3,-79.8,42.8C-87.7,28.3,-91.4,11.9,-90.1,-4C-88.8,-19.9,-82.5,-35.3,-72.5,-48.2C-62.5,-61.1,-48.8,-71.5,-34.5,-78.6C-20.2,-85.7,-5.1,-89.5,9.6,-86.2C24.3,-82.9,30.6,-83.6,44.7,-76.4Z" transform="translate(100 100)" />
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
                <span class="badge badge-secondary" style="font-size: 0.6rem; text-transform: uppercase; margin-bottom: 5px;"><?php echo $latest_ann['priority']; ?> Priority Bulletin</span>
                <h4 style="margin: 0; font-weight: 800; color: var(--text-main);"><?php echo htmlspecialchars($latest_ann['title']); ?></h4>
            </div>
        </div>
        <a href="announcements.php" class="btn btn-outline" style="font-size: 0.75rem; border-radius: 100px; white-space: nowrap;">View Bulletin Board</a>
    </div>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card stat-primary has-tooltip">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-value"><?php echo $stats['total_employees']; ?></div>
        <div class="stat-label">Total Employees</div>
        <span class="tooltip">Company Headcount</span>
    </div>
    <div class="stat-card stat-warning has-tooltip">
        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-value"><?php echo $stats['pending_leaves']; ?></div>
        <div class="stat-label">Pending Leaves</div>
        <span class="tooltip">Awaiting Review</span>
    </div>
    <div class="stat-card stat-info has-tooltip">
        <div class="stat-icon"><i class="fas fa-building"></i></div>
        <div class="stat-value"><?php echo $stats['departments']; ?></div>
        <div class="stat-label">Departments</div>
        <span class="tooltip">Business Units</span>
    </div>
    <div class="stat-card stat-success has-tooltip">
        <div class="stat-icon"><i class="fas fa-money-check-alt"></i></div>
        <div class="stat-value"><?php echo format_currency($stats['monthly_payroll']); ?></div>
        <div class="stat-label">Payroll (This Month)</div>
        <span class="tooltip">Disbursement Goal</span>
    </div>
</div>

<div class="dashboard-grid" style="margin-bottom: 30px;">
    <!-- Visual Analytics: Dept Distribution -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie" style="margin-right: 10px; color: var(--primary);"></i> Staffing Distribution</h3>
        </div>
        <div class="card-body">
            <canvas id="deptDistributionChart" height="280"></canvas>
            <?php
            $dept_q = mysqli_query($conn, "SELECT d.name, COUNT(e.id) as count FROM departments d LEFT JOIN employees e ON d.id = e.department_id GROUP BY d.id");
            $d_names = []; $d_counts = [];
            while($dq = mysqli_fetch_assoc($dept_q)) {
                $d_names[] = $dq['name'];
                $d_counts[] = (int)$dq['count'];
            }
            ?>
        </div>
    </div>

    <!-- Visual Analytics: Resource Efficiency Radar -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-microchip" style="margin-right: 10px; color: var(--accent);"></i> Resource Efficiency</h3>
        </div>
        <div class="card-body">
            <canvas id="efficiencyRadarChart" height="280"></canvas>
        </div>
    </div>
</div>

<div class="dashboard-grid" style="grid-template-columns: 1fr 1fr 1.5fr; gap: 30px; margin-bottom: 30px;">
    <!-- System Pulse (Technical) -->
    <div class="card" style="background: #0f172a; color: white; border: none;">
        <div class="card-header" style="border-bottom-color: rgba(255,255,255,0.05);">
            <h3 style="color: white;"><i class="fas fa-wave-square" style="color: #10b981; margin-right: 10px;"></i> System Pulse</h3>
        </div>
        <div class="card-body" style="padding: 20px;">
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <small style="color: #94a3b8; font-weight: 800; font-size: 0.6rem; text-transform: uppercase;">DB Status</small>
                    <span style="color: #10b981; font-weight: 800; font-size: 0.8rem;">Operational</span>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <small style="color: #94a3b8; font-weight: 800; font-size: 0.6rem; text-transform: uppercase;">Audit Engine</small>
                    <span style="font-weight: 800; font-size: 0.8rem;">Active</span>
                </div>
                <div style="margin-top: 10px; height: 30px; display: flex; align-items: center; justify-content: center; gap: 2px;">
                    <div style="height: 15px; width: 3px; background: #10b981; border-radius: 2px; animation: pulse 1s infinite;"></div>
                    <div style="height: 8px; width: 3px; background: #10b981; border-radius: 2px; animation: pulse 1s infinite 0.2s;"></div>
                    <div style="height: 20px; width: 3px; background: #10b981; border-radius: 2px; animation: pulse 1s infinite 0.4s;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action: Export All -->
    <div class="card" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border: none;">
        <div class="card-body" style="padding: 30px; text-align: center; height: 100%; display: flex; flex-direction: column; justify-content: center;">
            <i class="fas fa-file-export" style="font-size: 2.5rem; margin-bottom: 15px; opacity: 0.8;"></i>
            <h4 style="margin: 0; font-weight: 800;">Forensic Audit</h4>
            <a href="audit_logs.php" class="btn btn-outline" style="background: white; border: none; color: #059669; justify-content: center; font-size: 0.75rem;">Download Logs</a>
        </div>
    </div>

    <!-- Active Support -->
    <div class="card" style="border: 1px dashed var(--border); box-shadow: none; background: #f8fafc;">
        <div class="card-body" style="padding: 30px; display: flex; align-items: center; gap: 20px;">
            <div style="width: 50px; height: 50px; background: white; border: 1px solid var(--border); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--primary);">
                <i class="fas fa-headset"></i>
            </div>
            <div>
                <h4 style="margin: 0; font-weight: 800;">Concierge</h4>
                <p style="margin: 0; font-size: 0.8rem; color: var(--text-muted);">Admin Support Portal</p>
                <a href="feedback.php" style="color: var(--primary); font-size: 0.75rem; font-weight: 800; text-decoration: none; margin-top: 5px; display: block;">Open Ticket</a>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <!-- Capacity Gauges -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-users-slash" style="color: var(--danger); margin-right: 10px;"></i> Team Availability Gauges</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <?php 
                $depts_cap = mysqli_query($conn, "SELECT d.name, COUNT(e.id) as total, 
                                                  (SELECT COUNT(*) FROM leave_requests lr WHERE lr.employee_id = e.id AND lr.status = 'approved' AND CURDATE() BETWEEN lr.start_date AND lr.end_date) as out_count
                                                  FROM departments d 
                                                  LEFT JOIN employees e ON d.id = e.department_id AND e.is_active = 1
                                                  GROUP BY d.id 
                                                  HAVING total > 0");
                if ($depts_cap):
                    while($cap = mysqli_fetch_assoc($depts_cap)): 
                        $avail = $cap['total'] - $cap['out_count'];
                        $perc = $cap['total'] > 0 ? ($avail / $cap['total']) * 100 : 0;
                        $color = $perc < 50 ? 'var(--danger)' : ($perc < 80 ? 'var(--warning)' : 'var(--success)');
                    ?>
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <strong style="font-size: 0.85rem;"><?php echo htmlspecialchars($cap['name']); ?></strong>
                            <small style="font-weight: 800; color: <?php echo $color; ?>;"><?php echo $avail; ?>/<?php echo $cap['total']; ?> In Office</small>
                        </div>
                        <div style="height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; width: <?php echo $perc; ?>%; background: <?php echo $color; ?>; transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);"></div>
                        </div>
                    </div>
                    <?php endwhile; 
                endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-calendar-alt" style="margin-right: 10px; color: var(--primary);"></i> Recent Leave Requests</h3>
                <a href="leave_request.php" class="btn btn-primary" style="font-size: 12px;">View All</a>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Type</th>
                                <th>Days</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($pending_requests): ?>
                                <?php while ($req = mysqli_fetch_assoc($pending_requests)): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($req['full_name']); ?></strong></td>
                                    <td><?php echo ucfirst($req['leave_type']); ?></td>
                                    <td><?php echo $req['total_days']; ?> days</td>
                                    <td><?php echo get_status_badge($req['status']); ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if (mysqli_num_rows($pending_requests) == 0): ?>
                                    <tr><td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">No pending requests found</td></tr>
                                <?php endif; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-user-clock" style="margin-right: 10px; color: var(--danger);"></i> Staff Out Today</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <?php 
                $today = date('Y-m-d');
                $out_today = mysqli_query($conn, "SELECT e.full_name, lr.leave_type, lr.end_date 
                                                   FROM leave_requests lr
                                                   JOIN employees e ON lr.employee_id = e.id
                                                   WHERE lr.status = 'approved' 
                                                   AND '$today' BETWEEN lr.start_date AND lr.end_date
                                                   LIMIT 5");
                
                if ($out_today && mysqli_num_rows($out_today) > 0): ?>
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        <?php while($staff = mysqli_fetch_assoc($out_today)): ?>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 10px; border-bottom: 1px solid var(--border);">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 30px; height: 30px; border-radius: 50%; background: #fef2f2; color: var(--danger); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800;">
                                        <?php echo strtoupper(substr($staff['full_name'], 0, 1)); ?>
                                    </div>
                                    <span style="font-size: 0.85rem; font-weight: 700;"><?php echo htmlspecialchars($staff['full_name']); ?></span>
                                </div>
                                <span class="badge badge-secondary" style="font-size: 0.65rem;">Until <?php echo date('M d', strtotime($staff['end_date'])); ?></span>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; color: var(--text-muted); padding: 10px 0;">
                        <i class="fas fa-users" style="font-size: 1.5rem; opacity: 0.3; margin-bottom: 10px; display: block;"></i>
                        <p style="font-size: 0.8rem; margin: 0;">Everyone is in today! 🚀</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-stream" style="margin-right: 10px; color: var(--accent);"></i> System Activity</h3>
            </div>
            <div class="card-body" style="padding: 25px;">
                <div style="display: flex; flex-direction: column; gap: 20px;">
                    <?php 
                    // Get latest 5 audit logs
                    $activity_query = "SELECT al.*, e.full_name 
                                       FROM audit_logs al 
                                       LEFT JOIN employees e ON al.user_id = e.id 
                                       ORDER BY al.created_at DESC LIMIT 5";
                    $activities = mysqli_query($conn, $activity_query);
                    
                    if ($activities && mysqli_num_rows($activities) > 0):
                        while($act = mysqli_fetch_assoc($activities)): 
                            $icon = 'fa-info-circle';
                            $color = 'var(--primary)';
                            
                            if (str_contains($act['action'], 'Delete')) { $icon = 'fa-trash-alt'; $color = 'var(--danger)'; }
                            elseif (str_contains($act['action'], 'Add')) { $icon = 'fa-user-plus'; $color = 'var(--success)'; }
                            elseif (str_contains($act['action'], 'Payroll')) { $icon = 'fa-calculator'; $color = 'var(--info)'; }
                            elseif (str_contains($act['action'], 'Settings')) { $icon = 'fa-cogs'; $color = 'var(--warning)'; }
                    ?>
                        <div style="display: flex; gap: 15px; align-items: flex-start;">
                            <div style="width: 35px; height: 35px; border-radius: 50%; background: #f8fafc; color: <?php echo $color; ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0; border: 1px solid var(--border);">
                                <i class="fas <?php echo $icon; ?>" style="font-size: 0.8rem;"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="font-size: 0.85rem; margin: 0; color: var(--text-main); line-height: 1.3;">
                                    <strong><?php echo htmlspecialchars($act['action']); ?></strong>: <?php echo htmlspecialchars($act['details']); ?>
                                </p>
                                <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                                    <small style="color: var(--text-muted); font-size: 0.7rem; font-weight: 700;"><?php echo explode(' ', $act['full_name'] ?? 'System')[0]; ?></small>
                                    <small style="color: var(--text-muted); font-size: 0.7rem;"><?php echo date('M d, H:i', strtotime($act['created_at'])); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; else: ?>
                        <p style="text-align: center; color: var(--text-muted); font-size: 0.8rem; margin: 0;">No recent system activity.</p>
                    <?php endif; ?>
                </div>
                <div style="margin-top: 25px; border-top: 1px solid var(--border); padding-top: 15px; text-align: center;">
                    <a href="audit_logs.php" style="color: var(--primary); text-decoration: none; font-size: 0.8rem; font-weight: 800;">View Security Logs <i class="fas fa-arrow-right" style="margin-left: 5px;"></i></a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-bolt" style="margin-right: 10px; color: var(--warning);"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
                    <a href="employee_add.php" class="btn btn-primary" style="padding: 15px; border-radius: 16px; flex-direction: column; height: auto; gap: 8px;">
                        <i class="fas fa-user-plus" style="font-size: 1.2rem;"></i>
                        <span>Add New Staff</span>
                    </a>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <a href="payroll.php" class="btn btn-outline" style="padding: 15px; border-radius: 16px; flex-direction: column; height: auto; gap: 8px; border-style: dashed;">
                            <i class="fas fa-calculator" style="color: var(--primary);"></i>
                            <span style="font-size: 0.8rem;">Run Payroll</span>
                        </a>
                        <a href="reports.php" class="btn btn-outline" style="padding: 15px; border-radius: 16px; flex-direction: column; height: auto; gap: 8px; border-style: dashed;">
                            <i class="fas fa-file-export" style="color: var(--success);"></i>
                            <span style="font-size: 0.8rem;">Reports</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Dept Distribution
    const ctxDept = document.getElementById('deptDistributionChart').getContext('2d');
    new Chart(ctxDept, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($d_names); ?>,
            datasets: [{
                data: <?php echo json_encode($d_counts); ?>,
                backgroundColor: ['#6366f1', '#f472b6', '#10b981', '#f59e0b', '#3b82f6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { font: { family: 'Plus Jakarta Sans', weight: '700' } } } },
            cutout: '70%'
        }
    });

    // Efficiency Radar
    const ctxRadar = document.getElementById('efficiencyRadarChart').getContext('2d');
    new Chart(ctxRadar, {
        type: 'radar',
        data: {
            labels: ['Attendance', 'Budget', 'Payroll Speed', 'System Usage', 'Staff Retention'],
            datasets: [{
                label: 'Performance',
                data: [92, 85, 98, 70, 88],
                backgroundColor: 'rgba(244, 114, 182, 0.2)',
                borderColor: '#f472b6',
                borderWidth: 2,
                pointBackgroundColor: '#f472b6'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                r: {
                    angleLines: { color: 'rgba(0,0,0,0.05)' },
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { display: false },
                    pointLabels: { font: { family: 'Plus Jakarta Sans', weight: '700', size: 10 } }
                }
            }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
