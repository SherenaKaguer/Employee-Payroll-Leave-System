<?php
require_once '../includes/header.php';
get_header("Payroll Management");

// Handle Payroll Generation
if (isset($_POST['generate_payroll'])) {
    $month = $_POST['payroll_month'] . '-01';
    
    // Start Transaction
    mysqli_begin_transaction($conn);
    
    try {
        $employees = mysqli_query($conn, "SELECT id FROM employees WHERE is_active = 1");
        $count = 0;
        
        while ($emp = mysqli_fetch_assoc($employees)) {
            $emp_id = $emp['id'];
            
            // Check if already generated
            $check = mysqli_query($conn, "SELECT id FROM payroll_records WHERE employee_id = $emp_id AND payroll_month = '$month'");
            if (mysqli_num_rows($check) == 0) {
                $salary_data = calculate_salary($emp_id, $month);
                
                // Skip if no salary data found (base_salary 0 means calculation failed or no grade)
                if ($salary_data['base_salary'] <= 0) {
                    continue; 
                }
                
                $base = $salary_data['base_salary'];
                $allowances = $salary_data['allowances'];
                $deductions = $salary_data['total_deductions'];
                $net = $salary_data['net_salary'];
                
                $query = "INSERT INTO payroll_records (employee_id, payroll_month, base_salary, allowances, total_deductions, net_salary, status) 
                          VALUES ($emp_id, '$month', $base, $allowances, $deductions, $net, 'processed')";
                
                if (!mysqli_query($conn, $query)) {
                    throw new Exception("Error generating payroll for employee ID: $emp_id");
                }
                $count++;
            }
        }
        
        mysqli_commit($conn);
        hr_audit_log('Generate Payroll', "Processed monthly disbursement for " . date('F Y', strtotime($month)) . ". Total employees: $count");
        echo "<script>window.location.href='payroll.php?success=" . urlencode("Payroll generated for $count employees.") . "';</script>";
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>window.location.href='payroll.php?error=" . urlencode("Transaction failed: " . $e->getMessage()) . "';</script>";
    }
}

// Get payroll records with employee position
$query = "SELECT pr.*, e.full_name, e.employee_id as emp_code, e.position 
          FROM payroll_records pr
          JOIN employees e ON pr.employee_id = e.id
          ORDER BY pr.payroll_month DESC, e.full_name ASC";
$payroll_records = mysqli_query($conn, $query);
?>

<div class="card" style="margin-bottom: 30px;">
    <div class="card-header">
        <h3><i class="fas fa-magic" style="margin-right: 10px; color: var(--primary);"></i> Bulk Payroll Generation</h3>
    </div>
    <div class="card-body" style="padding: 30px;">
        <div style="display: flex; gap: 30px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 300px;">
                <h4 style="margin-bottom: 10px; font-weight: 800;">Process Monthly Salaries</h4>
                <p style="color: var(--text-muted); font-size: 0.9rem;">Select a month to automatically calculate and generate payroll records for all active employees based on their salary grades and mandatory deductions.</p>
            </div>
            <form method="POST" style="display: flex; gap: 15px; align-items: flex-end; background: #f8fafc; padding: 25px; border-radius: 16px; border: 1px solid var(--border);">
                <div class="form-group" style="margin-bottom: 0;">
                    <label style="font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">Payroll Month</label>
                    <div class="form-icon-group" style="margin-bottom: 0;">
                        <i class="fas fa-calendar-alt"></i>
                        <input type="month" name="payroll_month" required value="<?php echo date('Y-m'); ?>" style="padding: 10px 15px 10px 45px;">
                    </div>
                </div>
                <button type="submit" name="generate_payroll" class="btn btn-primary" style="padding: 12px 25px;"><i class="fas fa-sync"></i> Run Monthly Process</button>
            </form>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="card-body" style="padding: 15px 25px;">
        <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr; gap: 20px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Quick Search</label>
                <div class="form-icon-group">
                    <i class="fas fa-search"></i>
                    <input type="text" id="payrollSearch" placeholder="Search by name, ID, or position..." style="padding: 10px 15px 10px 45px;">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Filter Month</label>
                <div class="form-icon-group">
                    <i class="fas fa-calendar"></i>
                    <input type="month" id="monthFilter" style="padding: 10px 15px 10px 45px;">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-history" style="margin-right: 10px; color: var(--primary);"></i> Disbursement History</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table" id="payrollTable">
                <thead>
                    <tr>
                        <th>Employee Details</th>
                        <th>Month Period</th>
                        <th>Net Salary</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_payroll = false;
                    while ($row = mysqli_fetch_assoc($payroll_records)): 
                        $has_payroll = true;
                        $month_val = date('Y-m', strtotime($row['payroll_month']));
                    ?>
                    <tr class="payroll-row" data-month="<?php echo $month_val; ?>">
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 35px; height: 35px; border-radius: 10px; background: #f1f5f9; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800;">
                                    <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <strong class="emp-name"><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                                    <small class="emp-id" style="color: var(--text-muted);"><?php echo $row['emp_code']; ?> • <?php echo htmlspecialchars($row['position']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><strong><?php echo date('F Y', strtotime($row['payroll_month'])); ?></strong></td>
                        <td><strong class="text-gradient" style="font-size: 1.1rem;"><?php echo format_currency($row['net_salary']); ?></strong></td>
                        <td><?php echo get_status_badge($row['status']); ?></td>
                        <td>
                            <div style="display: flex; gap: 5px;">
                                <a href="payroll_view.php?id=<?php echo $row['id']; ?>" class="btn btn-outline" style="padding: 6px 10px;" title="View Statement"><i class="fas fa-file-alt"></i></a>
                                <?php if ($row['status'] !== 'paid'): ?>
                                    <a href="payroll_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-outline" style="padding: 6px 10px;" title="Adjust Values"><i class="fas fa-edit"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if (!$has_payroll): ?>
                <div class="empty-state">
                    <i class="fas fa-money-check-alt"></i>
                    <h3>No Records</h3>
                    <p>No payroll records have been generated yet. Use the process form above to begin.</p>
                </div>
            <?php endif; ?>
            <div id="noPayrollResults" class="empty-state" style="display: none;">
                <i class="fas fa-search-dollar"></i>
                <h3>No Matches Found</h3>
                <p>We couldn't find any payroll records matching your search or month filter.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('payrollSearch');
    const monthFilter = document.getElementById('monthFilter');
    const rows = document.querySelectorAll('.payroll-row');
    const noResults = document.getElementById('noPayrollResults');
    const table = document.getElementById('payrollTable');

    function filterPayroll() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedMonth = monthFilter.value;
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.querySelector('.emp-name').textContent.toLowerCase();
            const id = row.querySelector('.emp-id').textContent.toLowerCase();
            const rowMonth = row.getAttribute('data-month');

            const matchesSearch = name.includes(searchTerm) || id.includes(searchTerm);
            const matchesMonth = !selectedMonth || rowMonth === selectedMonth;

            if (matchesSearch && matchesMonth) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCount === 0 && rows.length > 0) {
            noResults.style.display = 'block';
            table.querySelector('thead').style.display = 'none';
        } else {
            noResults.style.display = 'none';
            table.querySelector('thead').style.display = '';
        }
    }

    searchInput.addEventListener('input', filterPayroll);
    monthFilter.addEventListener('change', filterPayroll);
});
</script>


<?php require_once '../includes/footer.php'; ?>
