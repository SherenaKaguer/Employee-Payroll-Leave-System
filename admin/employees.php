<?php
require_once '../includes/header.php';
get_header("Employee Management");

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $emp_res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name FROM employees WHERE id = $id"));
    $emp_name = $emp_res['full_name'] ?? 'Unknown';
    
    if (mysqli_query($conn, "DELETE FROM employees WHERE id = $id")) {
        hr_audit_log('Delete Employee', "Removed $emp_name (ID: $id) from the system.");
    }
    echo "<script>window.location.href='employees.php';</script>";
}

// Get statistics for the intelligence bar
$total_staff = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE role != 'admin'"))['count'];
$active_staff = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM employees WHERE is_active = 1 AND role != 'admin'"))['count'];
$on_leave = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(DISTINCT employee_id) as count FROM leave_requests WHERE status = 'approved' AND CURDATE() BETWEEN start_date AND end_date"))['count'];

// Get employees with department and grade info
$query = "SELECT e.*, d.name as department_name, sg.grade_level 
          FROM employees e 
          LEFT JOIN departments d ON e.department_id = d.id 
          LEFT JOIN salary_grades sg ON e.salary_grade_id = sg.id 
          ORDER BY e.created_at DESC";
$employees = mysqli_query($conn, $query);
?>

<!-- Directory Intelligence Bar -->
<div class="stats-grid" style="margin-bottom: 30px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
    <div class="stat-card" style="padding: 20px; border-radius: 16px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #eef2ff; color: var(--primary); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <span class="stat-value" style="font-size: 1.4rem; display: block;"><?php echo $total_staff; ?></span>
                <small class="stat-label" style="font-size: 0.65rem;">Total Directory</small>
            </div>
        </div>
    </div>
    <div class="stat-card" style="padding: 20px; border-radius: 16px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #ecfdf5; color: var(--success); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <span class="stat-value" style="font-size: 1.4rem; display: block;"><?php echo $active_staff; ?></span>
                <small class="stat-label" style="font-size: 0.65rem;">Active Contracts</small>
            </div>
        </div>
    </div>
    <div class="stat-card" style="padding: 20px; border-radius: 16px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #fffbeb; color: var(--warning); display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-plane-arrival"></i>
            </div>
            <div>
                <span class="stat-value" style="font-size: 1.4rem; display: block;"><?php echo $on_leave; ?></span>
                <small class="stat-label" style="font-size: 0.65rem;">Currently Out</small>
            </div>
        </div>
    </div>
    <div class="stat-card" style="padding: 20px; border-radius: 16px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; border: none;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(255,255,255,0.1); color: white; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-user-plus"></i>
            </div>
            <div>
                <a href="employee_add.php" style="color: white; text-decoration: none;">
                    <span style="font-weight: 800; font-size: 0.9rem; display: block;">Hire Staff</span>
                    <small style="font-size: 0.65rem; opacity: 0.8; font-weight: 700; text-transform: uppercase;">Quick Onboard</small>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 24px;">
    <div class="card-body" style="padding: 20px 25px;">
        <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr 1fr; gap: 20px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Search Staff</label>
                <div class="form-icon-group">
                    <i class="fas fa-search"></i>
                    <input type="text" id="employeeSearch" placeholder="Search by name, ID, or email..." style="padding: 10px 15px 10px 45px;">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Department</label>
                <div class="form-icon-group">
                    <i class="fas fa-filter"></i>
                    <select id="deptFilter" style="padding: 10px 15px 10px 45px;">
                        <option value="all">All Departments</option>
                        <?php 
                        $depts = mysqli_query($conn, "SELECT name FROM departments ORDER BY name");
                        while($d = mysqli_fetch_assoc($depts)): ?>
                            <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div style="text-align: right; display: flex; gap: 10px;">
                <a href="export_employees.php" class="btn btn-outline" style="padding: 12px 25px; width: 100%; justify-content: center; background: white;"><i class="fas fa-file-csv" style="color: var(--success);"></i> Export Directory</a>
                <a href="employee_add.php" class="btn btn-primary" style="padding: 12px 25px; width: 100%; justify-content: center;"><i class="fas fa-plus"></i> Add Employee</a>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-users-cog" style="margin-right: 10px; color: var(--primary);"></i> Staff Directory</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="data-table" id="employeeTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">
                            <div class="custom-checkbox" id="selectAll"></div>
                        </th>
                        <th class="sortable">ID</th>
                        <th class="sortable">Profile & Name</th>
                        <th class="sortable">Department</th>
                        <th class="sortable">Position</th>
                        <th class="sortable">Grade</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $has_employees = false;
                    while ($emp = mysqli_fetch_assoc($employees)): 
                        $has_employees = true;
                    ?>
                    <tr class="employee-row" data-id="<?php echo $emp['id']; ?>" data-dept="<?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?>">
                        <td>
                            <div class="custom-checkbox row-select"></div>
                        </td>
                        <td><small style="font-weight: 800; color: var(--text-muted);"><?php echo $emp['employee_id']; ?></small></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php 
                                $pic = get_profile_pic($emp);
                                if ($pic): ?>
                                    <img src="<?php echo $pic; ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 12px; object-fit: cover; border: 2px solid white; box-shadow: var(--shadow-soft);">
                                <?php else: ?>
                                    <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem; flex-shrink: 0; box-shadow: var(--shadow-soft);">
                                        <?php echo strtoupper(substr($emp['full_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <strong class="emp-name" style="display: block; color: var(--text-main);"><?php echo htmlspecialchars($emp['full_name']); ?></strong>
                                    <small class="emp-email" style="color: var(--text-muted); font-size: 0.75rem;"><?php echo htmlspecialchars($emp['email']); ?></small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge badge-secondary" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;"><?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?></span></td>
                        <td><small style="font-weight: 600;"><?php echo htmlspecialchars($emp['position']); ?></small></td>
                        <td><span class="badge badge-primary" style="font-size: 0.7rem;"><?php echo htmlspecialchars($emp['grade_level'] ?? 'N/A'); ?></span></td>
                        <td>
                            <?php if ($emp['role'] == 'admin'): ?>
                                <span class="badge badge-danger" style="font-size: 0.65rem;">ADMIN</span>
                            <?php elseif ($emp['role'] == 'hr'): ?>
                                <span class="badge badge-info" style="font-size: 0.65rem;">HR</span>
                            <?php else: ?>
                                <span class="badge badge-secondary" style="font-size: 0.65rem;">EMPLOYEE</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($emp['is_active']): ?>
                                <span class="badge badge-success" style="font-size: 0.7rem;"><i class="fas fa-check-circle"></i> Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger" style="font-size: 0.7rem;"><i class="fas fa-times-circle"></i> Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <div class="has-tooltip">
                                    <a href="employee_view.php?id=<?php echo $emp['id']; ?>" class="btn btn-outline" style="padding: 6px 10px; font-size: 0.75rem;"><i class="fas fa-id-card"></i></a>
                                    <span class="tooltip">Master 360 View</span>
                                </div>
                                <div class="has-tooltip">
                                    <a href="employee_edit.php?id=<?php echo $emp['id']; ?>" class="btn btn-outline" style="padding: 6px 10px; font-size: 0.75rem;"><i class="fas fa-user-edit"></i></a>
                                    <span class="tooltip">Edit Profile</span>
                                </div>
                                <div class="has-tooltip">
                                    <a href="employees.php?delete=<?php echo $emp['id']; ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.75rem;" onclick="return confirm('Are you sure you want to delete this employee?')"><i class="fas fa-trash-alt"></i></a>
                                    <span class="tooltip">Permanently Remove</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if (!$has_employees): ?>
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <h3>No Employees Found</h3>
                    <p>Your directory is empty. Click 'Add Employee' to get started.</p>
                </div>
            <?php endif; ?>
            <div id="noResults" class="empty-state" style="display: none;">
                <i class="fas fa-search"></i>
                <h3>No Matches Found</h3>
                <p>We couldn't find any staff members matching your search criteria.</p>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Action Bar -->
<div id="bulk-action-bar">
    <div class="bulk-count"><span id="selectedCount">0</span> selected</div>
    <div class="bulk-actions-group">
        <button class="bulk-btn" onclick="bulkAction('activate')"><i class="fas fa-user-check"></i> Activate</button>
        <button class="bulk-btn" onclick="bulkAction('deactivate')"><i class="fas fa-user-slash"></i> Deactivate</button>
        <button class="bulk-btn" onclick="bulkAction('transfer')"><i class="fas fa-exchange-alt"></i> Transfer</button>
        <button class="bulk-btn danger" onclick="bulkAction('delete')"><i class="fas fa-trash-alt"></i> Remove</button>
    </div>
    <button class="btn-icon" style="color: #94a3b8;" onclick="clearSelection()"><i class="fas fa-times"></i></button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-select');
    const bulkBar = document.getElementById('bulk-action-bar');
    const countDisplay = document.getElementById('selectedCount');

    function updateBulkBar() {
        const selectedCount = document.querySelectorAll('.row-select.checked').length;
        countDisplay.innerText = selectedCount;
        if (selectedCount > 0) {
            bulkBar.classList.add('active');
        } else {
            bulkBar.classList.remove('active');
            selectAll.classList.remove('checked');
        }
    }

    selectAll.addEventListener('click', () => {
        const isChecked = selectAll.classList.toggle('checked');
        rowCheckboxes.forEach(cb => {
            if (isChecked) cb.classList.add('checked');
            else cb.classList.remove('checked');
        });
        updateBulkBar();
    });

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('click', (e) => {
            e.stopPropagation();
            cb.classList.toggle('checked');
            updateBulkBar();
        });
    });

    window.clearSelection = function() {
        rowCheckboxes.forEach(cb => cb.classList.remove('checked'));
        selectAll.classList.remove('checked');
        updateBulkBar();
    };

    window.bulkAction = function(action) {
        const ids = Array.from(document.querySelectorAll('.row-select.checked'))
                         .map(cb => cb.closest('tr').getAttribute('data-id'));
        
        if (confirm(`Are you sure you want to ${action} ${ids.length} staff members?`)) {
            showToast('Processing', `Executing ${action} for selected records...`, 'info');
            
            fetch('bulk_employee_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: action, ids: ids })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Success', data.message, 'success');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showToast('Error', data.message, 'danger');
                }
            })
            .catch(err => {
                showToast('Error', 'A system error occurred during processing.', 'danger');
            });
        }
    };

    const searchInput = document.getElementById('employeeSearch');
    const deptFilter = document.getElementById('deptFilter');
    const rows = document.querySelectorAll('.employee-row');
    const noResults = document.getElementById('noResults');
    const table = document.getElementById('employeeTable');

    function filterEmployees() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedDept = deptFilter.value.toLowerCase();
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.querySelector('.emp-name').textContent.toLowerCase();
            const email = row.querySelector('.emp-email').textContent.toLowerCase();
            const id = row.cells[1].textContent.toLowerCase();
            const dept = row.getAttribute('data-dept').toLowerCase();

            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm) || id.includes(searchTerm);
            const matchesDept = selectedDept === 'all' || dept === selectedDept;

            if (matchesSearch && matchesDept) {
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

    searchInput.addEventListener('input', filterEmployees);
    deptFilter.addEventListener('change', filterEmployees);
});
</script>

<?php require_once '../includes/footer.php'; ?>
