<?php
require_once '../includes/header.php';
get_header("My Payroll History");

$user_id = $_SESSION['user_id'];

// Get payroll records
$query = "SELECT * FROM payroll_records 
          WHERE employee_id = $user_id 
          ORDER BY payroll_month DESC";
$payroll_records = mysqli_query($conn, $query);
?>

<div class="card">
    <div class="card-header">
        <h3>My Earnings & Deductions</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Base Salary</th>
                        <th>Allowances</th>
                        <th>Deductions</th>
                        <th>Net Salary</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($payroll_records)): ?>
                    <tr>
                        <td><strong><?php echo date('F Y', strtotime($row['payroll_month'])); ?></strong></td>
                        <td><?php echo format_currency($row['base_salary']); ?></td>
                        <td><?php echo format_currency($row['allowances']); ?></td>
                        <td><?php echo format_currency($row['total_deductions']); ?></td>
                        <td><strong class="text-gradient"><?php echo format_currency($row['net_salary']); ?></strong></td>
                        <td><?php echo get_status_badge($row['status']); ?></td>
                        <td>
                            <a href="payroll_view.php?id=<?php echo $row['id']; ?>" class="btn btn-outline" title="View Payslip"><i class="fas fa-eye"></i> View</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if (mysqli_num_rows($payroll_records) == 0): ?>
                        <tr><td colspan="7" style="text-align: center;">No payroll records found yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
