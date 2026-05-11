<?php
require_once '../includes/header.php';
get_header("Payslip Details");

if (!isset($_GET['id'])) {
    header('Location: payroll.php');
    exit();
}

$id = (int)$_GET['id'];
$query = "SELECT pr.*, e.full_name, e.employee_id as emp_code, e.position, d.name as department_name, sg.grade_level
          FROM payroll_records pr
          JOIN employees e ON pr.employee_id = e.id
          LEFT JOIN departments d ON e.department_id = d.id
          LEFT JOIN salary_grades sg ON e.salary_grade_id = sg.id
          WHERE pr.id = $id";

$payroll = mysqli_fetch_assoc(mysqli_query($conn, $query));

if (!$payroll) {
    header('Location: payroll.php');
    exit();
}

// Get specific deductions for this payroll if any (though currently we just show total)
// In a real system, we'd query payroll_deductions table here.
?>

<div id="payslip-container" class="card" style="max-width: 850px; margin: 0 auto; padding: 40px; border: 1px solid var(--border); box-shadow: var(--shadow-soft);">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; border-bottom: 3px solid var(--primary); padding-bottom: 25px;">
        <div>
            <h1 class="text-gradient" style="margin: 0; font-size: 2.2rem; font-weight: 800;">HRMS PAYSLIP</h1>
            <p style="color: var(--text-muted); margin: 5px 0; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; font-size: 0.8rem;">Official Earnings Statement</p>
        </div>
        <div style="text-align: right;">
            <h3 style="margin: 0; font-weight: 800; color: var(--text-main);">#<?php echo str_pad($payroll['id'], 6, '0', STR_PAD_LEFT); ?></h3>
            <p style="color: var(--text-muted); margin: 5px 0; font-size: 0.9rem;">Issued: <strong><?php echo date('M d, Y', strtotime($payroll['created_at'])); ?></strong></p>
        </div>
    </div>

    <div class="dashboard-grid" style="margin-bottom: 40px; gap: 40px;">
        <div>
            <h4 style="text-transform: uppercase; color: var(--primary); font-size: 11px; font-weight: 800; letter-spacing: 1.5px; margin-bottom: 12px;">Employee Information</h4>
            <p style="font-size: 1.1rem; font-weight: 800; margin-bottom: 4px; color: var(--text-main);"><?php echo htmlspecialchars($payroll['full_name']); ?></p>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 2px;">ID: <strong><?php echo $payroll['emp_code']; ?></strong></p>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 2px;">Position: <strong><?php echo htmlspecialchars($payroll['position']); ?></strong></p>
            <p style="font-size: 0.9rem; color: var(--text-muted);">Department: <strong><?php echo htmlspecialchars($payroll['department_name']); ?></strong></p>
        </div>
        <div style="text-align: right;">
            <h4 style="text-transform: uppercase; color: var(--primary); font-size: 11px; font-weight: 800; letter-spacing: 1.5px; margin-bottom: 12px;">Payroll Details</h4>
            <p style="font-size: 1.1rem; font-weight: 800; margin-bottom: 4px; color: var(--text-main);"><?php echo date('F Y', strtotime($payroll['payroll_month'])); ?></p>
            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 2px;">Salary Grade: <strong><?php echo $payroll['grade_level']; ?></strong></p>
            <p style="font-size: 0.9rem; color: var(--text-muted);">Payment Status: <?php echo get_status_badge($payroll['status']); ?></p>
        </div>
    </div>

    <div style="margin-bottom: 40px;">
        <table style="width: 100%; border-collapse: collapse; border: 1px solid var(--border);">
            <thead>
                <tr style="background: #f8fafc;">
                    <th style="padding: 15px 20px; text-align: left; border-bottom: 2px solid var(--border); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted);">Description</th>
                    <th style="padding: 15px 20px; text-align: right; border-bottom: 2px solid var(--border); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted);">Earnings</th>
                    <th style="padding: 15px 20px; text-align: right; border-bottom: 2px solid var(--border); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted);">Deductions</th>
                </tr>
            </thead>
            <tbody style="font-size: 0.95rem; color: var(--text-main);">
                <tr>
                    <td style="padding: 15px 20px; border-bottom: 1px solid var(--border); font-weight: 600;">Basic Salary</td>
                    <td style="padding: 15px 20px; text-align: right; border-bottom: 1px solid var(--border);"><?php echo format_currency($payroll['base_salary']); ?></td>
                    <td style="padding: 15px 20px; text-align: right; border-bottom: 1px solid var(--border); color: var(--text-muted);">--</td>
                </tr>
                <tr>
                    <td style="padding: 15px 20px; border-bottom: 1px solid var(--border); font-weight: 600;">Total Allowances</td>
                    <td style="padding: 15px 20px; text-align: right; border-bottom: 1px solid var(--border);"><?php echo format_currency($payroll['allowances']); ?></td>
                    <td style="padding: 15px 20px; text-align: right; border-bottom: 1px solid var(--border); color: var(--text-muted);">--</td>
                </tr>
                <tr>
                    <td style="padding: 15px 20px; border-bottom: 1px solid var(--border); font-weight: 600;">Statutory Deductions</td>
                    <td style="padding: 15px 20px; text-align: right; border-bottom: 1px solid var(--border); color: var(--text-muted);">--</td>
                    <td style="padding: 15px 20px; text-align: right; border-bottom: 1px solid var(--border); color: var(--danger); font-weight: 600;">- <?php echo format_currency($payroll['total_deductions']); ?></td>
                </tr>
                <?php if ($payroll['bonus'] > 0): ?>
                <tr>
                    <td style="padding: 15px 20px; border-bottom: 1px solid var(--border); font-weight: 600;">Performance Bonus</td>
                    <td style="padding: 15px 20px; text-align: right; border-bottom: 1px solid var(--border); color: var(--success); font-weight: 600;"><?php echo format_currency($payroll['bonus']); ?></td>
                    <td style="padding: 15px 20px; text-align: right; border-bottom: 1px solid var(--border); color: var(--text-muted);">--</td>
                </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: 800;">
                    <td style="padding: 20px; font-size: 1rem;">NET PAYABLE AMOUNT</td>
                    <td colspan="2" style="padding: 20px; text-align: right; color: var(--primary); font-size: 1.5rem; letter-spacing: -1px;"><?php echo format_currency($payroll['net_salary']); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 80px; position: relative;">
        <!-- Digital Verified Seal -->
        <div style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%) rotate(-15deg); opacity: 0.1; pointer-events: none;">
            <div style="border: 4px solid var(--success); color: var(--success); padding: 10px 20px; border-radius: 15px; text-align: center;">
                <i class="fas fa-check-shield" style="font-size: 3rem; display: block; margin-bottom: 5px;"></i>
                <strong style="font-size: 1.5rem; text-transform: uppercase; letter-spacing: 5px;">Verified</strong>
                <p style="font-size: 0.6rem; margin: 5px 0 0 0;">HRMS SECURE PAYROLL</p>
            </div>
        </div>

        <div style="text-align: center; width: 220px;">
            <div style="border-bottom: 2px solid var(--text-main); margin-bottom: 10px; height: 50px; display: flex; align-items: flex-end; justify-content: center; font-family: 'Dancing Script', cursive; font-size: 1.2rem; color: var(--text-muted);">
                <?php echo htmlspecialchars($payroll['full_name']); ?>
            </div>
            <p style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: var(--text-muted);">Employee Signature</p>
        </div>

        <div style="text-align: center; width: 220px;">
            <div style="border-bottom: 2px solid var(--text-main); margin-bottom: 10px; height: 50px; display: flex; align-items: flex-end; justify-content: center;">
                <img src="<?php echo BASE_URL; ?>favicon.svg" width="30" style="opacity: 0.3; margin-bottom: 5px;">
                <span style="font-family: serif; font-style: italic; color: #2c3e50; font-weight: bold;">HR Administrator</span>
            </div>
            <p style="font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: var(--text-muted);">Authorised Signature</p>
        </div>
    </div>

    <div style="margin-top: 50px; text-align: center; color: var(--text-muted); font-size: 11px;">
        <p style="margin-bottom: 5px;">This is an electronically generated document. No signature is required.</p>
        <p>© <?php echo date('Y'); ?> HRMS Payroll System - Confidential</p>
    </div>
</div>

<div style="max-width: 850px; margin: 30px auto; display: flex; justify-content: flex-end; gap: 12px;" class="no-print">
    <button onclick="exportToPDF('payslip-container', 'Payslip_<?php echo $payroll['emp_code']; ?>_<?php echo date('M_Y', strtotime($payroll['payroll_month'])); ?>')" class="btn btn-primary" style="background: var(--success); border-color: var(--success);"><i class="fas fa-file-pdf"></i> Download PDF</button>
    <button onclick="window.print()" class="btn btn-primary" style="background: var(--info); border-color: var(--info);"><i class="fas fa-print"></i> Print</button>
    <a href="payroll.php" class="btn btn-outline">Back to List</a>
</div>

<style>
@media print {
    .sidebar, .top-bar, .no-print, .breadcrumb {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
    }
}
</style>

<?php require_once '../includes/footer.php'; ?>
