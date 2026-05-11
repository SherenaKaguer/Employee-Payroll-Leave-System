<?php
require_once '../includes/header.php';
get_header("Reports & Analytics");

// Handle Period Filtering
$selected_period = isset($_GET['period']) ? $_GET['period'] : date('Y-m');
$target_month = $selected_period . '-01';
$year_start = date('Y') . '-01-01';

// Get monthly payroll summary from view
$monthly_summary_query = "SELECT * FROM monthly_payroll_summary ORDER BY month ASC LIMIT 6";
$monthly_summary_result = mysqli_query($conn, $monthly_summary_query);
$months = [];
$payroll_totals = [];
$employee_counts = [];

while ($row = mysqli_fetch_assoc($monthly_summary_result)) {
    $months[] = date('M Y', strtotime($row['month'] . '-01'));
    $payroll_totals[] = (float)$row['total_net'];
    $employee_counts[] = (int)$row['total_employees'];
}
// Get department-wise employee count and budget comparison
$dept_stats_query = "SELECT d.name, d.budget, COUNT(e.id) as emp_count, SUM(pr.net_salary) as total_salary 
                     FROM departments d 
                     LEFT JOIN employees e ON d.id = e.department_id 
                     LEFT JOIN payroll_records pr ON e.id = pr.employee_id AND pr.payroll_month = '$target_month'
                     GROUP BY d.id";
$dept_stats_result = mysqli_query($conn, $dept_stats_query);
$dept_names = [];
$dept_counts = [];
$dept_salaries = [];
$dept_budgets = [];

while ($dept = mysqli_fetch_assoc($dept_stats_result)) {
    $dept_names[] = $dept['name'];
    $dept_counts[] = (int)$dept['emp_count'];
    $dept_salaries[] = (float)($dept['total_salary'] ?? 0);
    $dept_budgets[] = (float)$dept['budget'];
}

// Get leave type distribution (all time approved)
$leave_dist_query = "SELECT leave_type, COUNT(*) as count FROM leave_requests WHERE status = 'approved' GROUP BY leave_type";
$leave_dist_res = mysqli_query($conn, $leave_dist_query);
$l_types = [];
$l_counts = [];
while($l = mysqli_fetch_assoc($leave_dist_res)) {
    $l_types[] = ucfirst($l['leave_type']);
    $l_counts[] = (int)$l['count'];
}

// Workforce Growth (Last 12 Months)
$growth_q = mysqli_query($conn, "SELECT DATE_FORMAT(hire_date, '%b %Y') as month, COUNT(*) as count FROM employees WHERE hire_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY month ORDER BY hire_date ASC");
$g_months = []; $g_counts = [];
$rt_q = mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE hire_date < DATE_SUB(CURDATE(), INTERVAL 12 MONTH)");
$rt_res = mysqli_fetch_assoc($rt_q);
$running_total = (int)($rt_res['count'] ?? 0);
while($g = mysqli_fetch_assoc($growth_q)) {
    $g_months[] = $g['month'];
    $running_total += (int)$g['count'];
    $g_counts[] = $running_total;
}

// Fiscal Year Breakdown (2026)
$fiscal_q = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(base_salary) as base, SUM(allowances) as allow, SUM(bonus) as bonus FROM payroll_records WHERE payroll_month >= '$year_start'"));
$fiscal_data = [(float)($fiscal_q['base'] ?? 0), (float)($fiscal_q['allow'] ?? 0), (float)($fiscal_q['bonus'] ?? 0)];
?>

<div class="card" style="margin-bottom: 30px;">
    <div class="card-body" style="padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 45px; height: 45px; border-radius: 12px; background: #eef2ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fas fa-filter"></i>
            </div>
            <div>
                <h4 style="margin: 0; font-weight: 800;">Analytics Filter</h4>
                <p style="margin: 0; font-size: 0.8rem; color: var(--text-muted);">Viewing historical performance for <strong><?php echo date('F Y', strtotime($target_month)); ?></strong></p>
            </div>
        </div>
        <form method="GET" style="display: flex; gap: 10px; align-items: center;">
            <div class="form-icon-group" style="margin-bottom: 0;">
                <i class="fas fa-calendar-check"></i>
                <input type="month" name="period" value="<?php echo $selected_period; ?>" style="padding: 8px 15px 8px 45px; border-radius: 100px;">
            </div>
            <button type="submit" class="btn btn-primary" style="border-radius: 100px; padding: 10px 25px;">Update Reports</button>
            <?php if($selected_period != date('Y-m')): ?>
                <a href="reports.php" class="btn btn-outline" style="border-radius: 100px; padding: 10px 20px;">Reset</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="dashboard-grid">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-area" style="color: var(--primary); margin-right: 10px;"></i> Expenditure Trend</h3>
        </div>
        <div class="card-body">
            <canvas id="payrollChart" height="300"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie" style="color: var(--accent); margin-right: 10px;"></i> Staff Distribution</h3>
        </div>
        <div class="card-body">
            <canvas id="deptChart" height="300"></canvas>
        </div>
    </div>
</div>

<div class="dashboard-grid" style="margin-top: 30px;">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-balance-scale" style="color: var(--success); margin-right: 10px;"></i> Budget vs. Actual</h3>
        </div>
        <div class="card-body">
            <canvas id="budgetBarChart" height="300"></canvas>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-calendar-check" style="color: var(--warning); margin-right: 10px;"></i> Leave Patterns</h3>
        </div>
        <div class="card-body">
            <canvas id="leaveDistChart" height="300"></canvas>
        </div>
    </div>
</div>

<div class="dashboard-grid" style="margin-top: 30px;">
    <!-- Workforce Growth -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-line" style="color: var(--primary); margin-right: 10px;"></i> Workforce Growth</h3>
        </div>
        <div class="card-body">
            <canvas id="growthChart" height="300"></canvas>
        </div>
    </div>

    <!-- Fiscal Breakdown -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chart-pie" style="color: var(--accent); margin-right: 10px;"></i> Fiscal Composition</h3>
        </div>
        <div class="card-body">
            <canvas id="fiscalPieChart" height="300"></canvas>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 30px;">
    <div class="card-header">
        <h3>Detailed Monthly Summary</h3>
        <a href="export_reports.php" class="btn btn-primary" style="font-size: 12px; background: var(--success); border-color: var(--success);"><i class="fas fa-file-csv"></i> Export to CSV</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month Period</th>
                        <th>Total Employees</th>
                        <th>Gross Base Salary</th>
                        <th>Total Allowances</th>
                        <th>Total Deductions</th>
                        <th>Net Paid Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $full_summary = mysqli_query($conn, "SELECT * FROM monthly_payroll_summary ORDER BY month DESC");
                    while ($row = mysqli_fetch_assoc($full_summary)): ?>
                    <tr>
                        <td><strong><?php echo date('F Y', strtotime($row['month'] . '-01')); ?></strong></td>
                        <td><?php echo $row['total_employees']; ?></td>
                        <td><?php echo format_currency($row['total_base']); ?></td>
                        <td><?php echo format_currency($row['total_allowances']); ?></td>
                        <td><span style="color: var(--danger);"><?php echo format_currency($row['total_deductions']); ?></span></td>
                        <td><strong class="text-gradient"><?php echo format_currency($row['total_net']); ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonts for all charts
    const fontCfg = { family: 'Plus Jakarta Sans', weight: '700', size: 11 };

    // Payroll Chart
    new Chart(document.getElementById('payrollChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{ label: 'Net Payroll', data: <?php echo json_encode($payroll_totals); ?>, borderColor: '#6366f1', fill: true, tension: 0.4 }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    // Dept Chart
    new Chart(document.getElementById('deptChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($dept_names); ?>,
            datasets: [{ data: <?php echo json_encode($dept_counts); ?>, backgroundColor: ['#6366f1', '#f472b6', '#10b981', '#f59e0b'] }]
        },
        options: { cutout: '70%', plugins: { legend: { position: 'bottom', labels: { font: fontCfg } } } }
    });

    // Leave Radar
    new Chart(document.getElementById('leaveDistChart').getContext('2d'), {
        type: 'radar',
        data: {
            labels: <?php echo json_encode($l_types); ?>,
            datasets: [{ label: 'Approved', data: <?php echo json_encode($l_counts); ?>, borderColor: '#f472b6', backgroundColor: 'rgba(244, 114, 182, 0.2)' }]
        }
    });

    // Budget Comparison
    new Chart(document.getElementById('budgetBarChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($dept_names); ?>,
            datasets: [
                { label: 'Payroll', data: <?php echo json_encode($dept_salaries); ?>, backgroundColor: '#10b981', borderRadius: 6 },
                { label: 'Budget', data: <?php echo json_encode($dept_budgets); ?>, backgroundColor: '#e2e8f0', borderRadius: 6 }
            ]
        },
        options: { plugins: { legend: { position: 'bottom', labels: { font: fontCfg } } } }
    });

    // Growth Chart
    new Chart(document.getElementById('growthChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($g_months); ?>,
            datasets: [{ label: 'Headcount', data: <?php echo json_encode($g_counts); ?>, borderColor: '#6366f1', fill: true, tension: 0.4, borderWidth: 4 }]
        }
    });

    // Fiscal Pie
    new Chart(document.getElementById('fiscalPieChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: ['Base Salary', 'Allowances', 'Bonuses'],
            datasets: [{
                data: <?php echo json_encode($fiscal_data); ?>,
                backgroundColor: ['#6366f1', '#10b981', '#f59e0b'],
                borderWidth: 0
            }]
        },
        options: { plugins: { legend: { position: 'bottom', labels: { font: fontCfg } } } }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
