<?php
require_once '../includes/header.php';
get_header("Staff Profile 360");

if (!isset($_GET['id'])) {
    header('Location: employees.php');
    exit();
}

$id = (int)$_GET['id'];

// Get full employee details
$query = "SELECT e.*, d.name as department_name, sg.grade_level, sg.base_salary 
          FROM employees e 
          LEFT JOIN departments d ON e.department_id = d.id 
          LEFT JOIN salary_grades sg ON e.salary_grade_id = sg.id 
          WHERE e.id = $id";
$emp = mysqli_fetch_assoc(mysqli_query($conn, $query));

if (!$emp) {
    echo "<div class='alert alert-danger'>Employee not found in the master directory.</div>";
    exit();
}

// Get history counts
$payroll_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM payroll_records WHERE employee_id = $id"))['count'];
$leave_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM leave_requests WHERE employee_id = $id"))['count'];
?>

<div class="dashboard-grid" style="grid-template-columns: 350px 1fr; gap: 30px;">
    <!-- Left: Professional Identity Card -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="card" style="text-align: center; position: sticky; top: 110px;">
            <div class="card-body" style="padding: 40px 30px;">
                <div style="position: relative; width: 140px; height: 140px; margin: 0 auto 25px;">
                    <?php 
                    $pic = get_profile_pic($emp);
                    if ($pic): ?>
                        <img src="<?php echo $pic; ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 30px; object-fit: cover; border: 5px solid white; box-shadow: var(--shadow-float);">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; border-radius: 30px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; display: flex; align-items: center; justify-content: center; font-size: 4rem; font-weight: 800; box-shadow: var(--shadow-float);">
                            <?php echo strtoupper(substr($emp['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <span style="position: absolute; bottom: -5px; right: -5px; width: 25px; height: 25px; background: var(--success); border: 4px solid white; border-radius: 50%;" title="Active Employee"></span>
                </div>

                <h3 style="margin: 0; font-weight: 800; font-size: 1.4rem; color: var(--text-main);"><?php echo htmlspecialchars($emp['full_name']); ?></h3>
                <p style="color: var(--primary); font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 5px;"><?php echo htmlspecialchars($emp['position']); ?></p>
                
                <div style="margin-top: 25px; display: flex; flex-direction: column; gap: 12px; text-align: left; background: #f8fafc; padding: 20px; border-radius: 16px; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between;">
                        <small style="font-weight: 800; color: var(--text-muted); text-transform: uppercase; font-size: 0.6rem;">Work ID</small>
                        <strong style="font-size: 0.8rem;"><?php echo $emp['employee_id']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <small style="font-weight: 800; color: var(--text-muted); text-transform: uppercase; font-size: 0.6rem;">Department</small>
                        <span class="badge badge-info" style="font-size: 0.6rem;"><?php echo htmlspecialchars($emp['department_name'] ?? 'General'); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <small style="font-weight: 800; color: var(--text-muted); text-transform: uppercase; font-size: 0.6rem;">Role</small>
                        <span class="badge badge-secondary" style="font-size: 0.6rem;"><?php echo strtoupper($emp['role']); ?></span>
                    </div>
                </div>

                <div style="margin-top: 30px; display: flex; flex-direction: column; gap: 10px;">
                    <a href="employee_edit.php?id=<?php echo $id; ?>" class="btn btn-primary btn-block"><i class="fas fa-user-edit"></i> Edit Official Record</a>
                    <a href="employees.php" class="btn btn-outline btn-block">Return to Directory</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Multi-Dimensional History -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <!-- Perspective Tabs -->
        <div class="card" style="margin-bottom: 0; background: var(--glass-bg); backdrop-filter: blur(10px);">
            <div class="card-body" style="padding: 12px; display: flex; gap: 12px;">
                <button class="btn btn-primary" onclick="switchPerspective('bio')" id="tab-bio"><i class="fas fa-id-card"></i> Biographical</button>
                <button class="btn btn-outline" onclick="switchPerspective('finance')" id="tab-finance"><i class="fas fa-coins"></i> Financials (<?php echo $payroll_count; ?>)</button>
                <button class="btn btn-outline" onclick="switchPerspective('schedule')" id="tab-schedule"><i class="fas fa-calendar-check"></i> Attendance (<?php echo $leave_count; ?>)</button>
            </div>
        </div>

        <!-- Perspective: Bio -->
        <div id="perspective-bio" class="perspective-content">
            <div class="card">
                <div class="card-header"><h3><i class="fas fa-info-circle" style="color: var(--primary); margin-right: 10px;"></i> Personal Master Data</h3></div>
                <div class="card-body" style="padding: 40px;">
                    <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 40px;">
                        <div>
                            <h4 style="font-weight: 800; margin-bottom: 20px; font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Contact Details</h4>
                            <p style="margin-bottom: 15px;"><strong>Email:</strong> <a href="mailto:<?php echo $emp['email']; ?>" style="color: var(--primary); text-decoration: none;"><?php echo htmlspecialchars($emp['email']); ?></a></p>
                            <p style="margin-bottom: 15px;"><strong>Phone:</strong> <?php echo htmlspecialchars($emp['phone'] ?? 'Not provided'); ?></p>
                            <p style="margin-bottom: 15px;"><strong>Date of Birth:</strong> <?php echo $emp['dob'] ? date('F d, Y', strtotime($emp['dob'])) : 'Not recorded'; ?></p>
                            <div style="margin-top: 20px;">
                                <small style="display: block; color: var(--text-muted); font-weight: 800; font-size: 0.65rem; text-transform: uppercase; margin-bottom: 5px;">Residential Address</small>
                                <p style="font-size: 0.9rem; line-height: 1.5; color: var(--text-main);"><?php echo nl2br(htmlspecialchars($emp['address'] ?? 'No address on file.')); ?></p>
                            </div>
                        </div>
                        <div>
                            <h4 style="font-weight: 800; margin-bottom: 20px; font-size: 0.9rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Career Lifecycle</h4>
                            <p style="margin-bottom: 15px;"><strong>Hire Date:</strong> <?php echo date('F d, Y', strtotime($emp['hire_date'])); ?></p>
                            <p style="margin-bottom: 15px;"><strong>Time at Company:</strong> <?php 
                                $start = new DateTime($emp['hire_date']);
                                $now = new DateTime();
                                $tenure = $start->diff($now);
                                echo $tenure->y . " Years, " . $tenure->m . " Months";
                            ?></p>
                            <p style="margin-bottom: 15px;"><strong>Grade Progression:</strong> <span class="badge badge-primary"><?php echo $emp['grade_level'] ?? 'N/A'; ?></span></p>
                            <div style="margin-top: 25px; padding: 20px; background: #ecfdf5; border-radius: 16px; border: 1px solid #d1fae5;">
                                <small style="display: block; color: #065f46; font-weight: 800; font-size: 0.65rem; text-transform: uppercase; margin-bottom: 5px;">Current Leave Capacity</small>
                                <strong style="font-size: 1.4rem; color: #059669;"><?php echo $emp['leave_balance']; ?> Days Available</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Perspective: Finance -->
        <div id="perspective-finance" class="perspective-content" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-file-invoice-dollar" style="color: var(--success); margin-right: 10px;"></i> Compensation History</h3>
                    <span class="badge badge-success">Automated Audit</span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Month Period</th>
                                    <th>Base Component</th>
                                    <th>Allowances</th>
                                    <th>Deductions</th>
                                    <th>Final Net</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $fin_history = mysqli_query($conn, "SELECT * FROM payroll_records WHERE employee_id = $id ORDER BY payroll_month DESC");
                                while($pr = mysqli_fetch_assoc($fin_history)): ?>
                                    <tr>
                                        <td><strong><?php echo date('F Y', strtotime($pr['payroll_month'])); ?></strong></td>
                                        <td><?php echo format_currency($pr['base_salary']); ?></td>
                                        <td><small style="color: var(--success); font-weight: 700;">+ <?php echo format_currency($pr['allowances']); ?></small></td>
                                        <td><small style="color: var(--danger); font-weight: 700;">- <?php echo format_currency($pr['total_deductions']); ?></small></td>
                                        <td><strong class="text-gradient"><?php echo format_currency($pr['net_salary']); ?></strong></td>
                                        <td><?php echo get_status_badge($pr['status']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if($payroll_count == 0): ?>
                                    <tr><td colspan="6" style="text-align: center; padding: 60px; color: var(--text-muted);">No financial records found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Perspective: Schedule -->
        <div id="perspective-schedule" class="perspective-content" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-check" style="color: var(--warning); margin-right: 10px;"></i> Attendance & Leave Timeline</h3>
                    <span class="badge badge-info">Verified Requests</span>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Request Period</th>
                                    <th>Leave Category</th>
                                    <th>Total Days</th>
                                    <th>Status</th>
                                    <th>Approval Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $att_history = mysqli_query($conn, "SELECT * FROM leave_requests WHERE employee_id = $id ORDER BY start_date DESC");
                                while($lr = mysqli_fetch_assoc($att_history)): ?>
                                    <tr>
                                        <td><small style="font-weight: 700;"><?php echo date('M d, Y', strtotime($lr['start_date'])); ?> - <?php echo date('M d, Y', strtotime($lr['end_date'])); ?></small></td>
                                        <td><span class="badge badge-info" style="font-size: 0.6rem;"><?php echo strtoupper($lr['leave_type']); ?></span></td>
                                        <td><strong><?php echo $lr['total_days']; ?></strong></td>
                                        <td><?php echo get_status_badge($lr['status']); ?></td>
                                        <td><small style="color: var(--text-muted); font-style: italic;"><?php echo htmlspecialchars($lr['approval_reason'] ?? '--'); ?></small></td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php if($leave_count == 0): ?>
                                    <tr><td colspan="5" style="text-align: center; padding: 60px; color: var(--text-muted);">No leave requests found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function switchPerspective(p) {
    // Hide all
    document.querySelectorAll('.perspective-content').forEach(c => c.style.display = 'none');
    // Reset buttons
    document.querySelectorAll('[id^="tab-"]').forEach(b => {
        b.className = 'btn btn-outline';
    });
    
    // Show selected
    document.getElementById('perspective-' + p).style.display = 'block';
    document.getElementById('tab-' + p).className = 'btn btn-primary';
    
    // Smooth scroll to top of card if on mobile
    if(window.innerWidth < 768) {
        window.scrollTo({ top: document.querySelector('.perspective-content').offsetTop - 100, behavior: 'smooth' });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
