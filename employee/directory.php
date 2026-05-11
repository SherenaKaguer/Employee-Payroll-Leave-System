<?php
require_once '../includes/header.php';
get_header("Company Directory");

// Get all employees for the directory
$query = "SELECT e.*, d.name as department_name, sg.grade_level 
          FROM employees e 
          LEFT JOIN departments d ON e.department_id = d.id 
          LEFT JOIN salary_grades sg ON e.salary_grade_id = sg.id
          WHERE e.is_active = 1
          ORDER BY d.name, e.full_name ASC";
$employees = mysqli_query($conn, $query);

$depts_res = mysqli_query($conn, "SELECT name FROM departments ORDER BY name");
?>

<div class="card" style="margin-bottom: 24px;">
    <div class="card-body" style="padding: 20px 25px;">
        <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr; gap: 20px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Search Directory</label>
                <div class="form-icon-group">
                    <i class="fas fa-search"></i>
                    <input type="text" id="dirSearch" placeholder="Search colleagues by name, position, or ID..." style="padding: 10px 15px 10px 45px;">
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label style="font-weight: 700; font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Department Filter</label>
                <div class="form-icon-group">
                    <i class="fas fa-building"></i>
                    <select id="dirDeptFilter" style="padding: 10px 15px 10px 45px;">
                        <option value="all">All Departments</option>
                        <?php while($d = mysqli_fetch_assoc($depts_res)): ?>
                            <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;" id="directoryGrid">
    <?php while($emp = mysqli_fetch_assoc($employees)): ?>
        <div class="card dir-card" data-dept="<?php echo htmlspecialchars($emp['department_name'] ?? 'N/A'); ?>" style="margin-bottom: 0; transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);">
            <div class="card-body" style="padding: 25px; text-align: center;">
                <div style="position: relative; width: 100px; height: 100px; margin: 0 auto 20px;">
                    <?php 
                    $pic = get_profile_pic($emp);
                    if ($pic): ?>
                        <img src="<?php echo $pic; ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 20px; object-fit: cover; border: 4px solid white; box-shadow: var(--shadow-soft);">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: white; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 800; box-shadow: var(--shadow-soft);">
                            <?php echo strtoupper(substr($emp['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <span style="position: absolute; bottom: -5px; right: -5px; width: 15px; height: 15px; background: var(--success); border: 3px solid white; border-radius: 50%;" title="Active Staff"></span>
                </div>

                <h4 class="dir-name" style="margin: 0; font-weight: 800; color: var(--text-main);"><?php echo htmlspecialchars($emp['full_name']); ?></h4>
                <p class="dir-pos" style="color: var(--primary); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin: 5px 0 15px;"><?php echo htmlspecialchars($emp['position']); ?></p>

                <div style="display: flex; flex-direction: column; gap: 8px; text-align: left; background: #f8fafc; padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <small style="color: var(--text-muted); font-weight: 800; font-size: 0.6rem; text-transform: uppercase;">Department</small>
                        <span class="badge badge-info" style="font-size: 0.6rem;"><?php echo htmlspecialchars($emp['department_name'] ?? 'General'); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <small style="color: var(--text-muted); font-weight: 800; font-size: 0.6rem; text-transform: uppercase;">Work ID</small>
                        <strong class="dir-id" style="font-size: 0.75rem;"><?php echo $emp['employee_id']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <small style="color: var(--text-muted); font-weight: 800; font-size: 0.6rem; text-transform: uppercase;">Email</small>
                        <a href="mailto:<?php echo $emp['email']; ?>" style="font-size: 0.75rem; color: var(--primary); text-decoration: none; font-weight: 700;"><?php echo htmlspecialchars($emp['email']); ?></a>
                    </div>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<div id="noDirResults" class="empty-state" style="display: none; padding: 80px 0;">
    <i class="fas fa-search-minus"></i>
    <h3>No Teammates Found</h3>
    <p>We couldn't find any colleagues matching your current filters.</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('dirSearch');
    const deptFilter = document.getElementById('dirDeptFilter');
    const cards = document.querySelectorAll('.dir-card');
    const noResults = document.getElementById('noDirResults');
    const grid = document.getElementById('directoryGrid');

    function filterDirectory() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedDept = deptFilter.value.toLowerCase();
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.querySelector('.dir-name').textContent.toLowerCase();
            const pos = card.querySelector('.dir-pos').textContent.toLowerCase();
            const id = card.querySelector('.dir-id').textContent.toLowerCase();
            const dept = card.getAttribute('data-dept').toLowerCase();

            const matchesSearch = name.includes(searchTerm) || pos.includes(searchTerm) || id.includes(searchTerm);
            const matchesDept = selectedDept === 'all' || dept === selectedDept;

            if (matchesSearch && matchesDept) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            noResults.style.display = 'block';
            grid.style.display = 'none';
        } else {
            noResults.style.display = 'grid';
            grid.style.display = 'grid';
        }
    }

    searchInput.addEventListener('input', filterDirectory);
    deptFilter.addEventListener('change', filterDirectory);
});
</script>

<?php require_once '../includes/footer.php'; ?>
