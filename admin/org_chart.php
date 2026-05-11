<?php
require_once '../includes/header.php';
get_header("Organizational Chart");

// Get departments and their managers
$query = "SELECT d.name as dept_name, e.full_name as manager_name, e.position as manager_pos, e.profile_pic
          FROM departments d
          LEFT JOIN employees e ON d.manager_id = e.id
          ORDER BY d.name";
$org_res = mysqli_query($conn, $query);
?>

<style>
    .org-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 50px;
        padding: 40px 0;
    }
    .org-root {
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        color: white;
        padding: 25px 40px;
        border-radius: 20px;
        font-weight: 800;
        box-shadow: var(--shadow-float);
        position: relative;
    }
    .org-root::after {
        content: '';
        position: absolute;
        bottom: -50px;
        left: 50%;
        width: 2px;
        height: 50px;
        background: var(--border);
    }
    .org-grid {
        display: flex;
        justify-content: center;
        gap: 30px;
        flex-wrap: wrap;
        width: 100%;
        position: relative;
    }
    .org-node {
        background: white;
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 20px;
        width: 240px;
        text-align: center;
        box-shadow: var(--shadow-soft);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
    }
    .org-node:hover { transform: translateY(-10px); box-shadow: var(--shadow-float); border-color: var(--primary); }
    
    .node-avatar {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        margin: 0 auto 15px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--border);
        overflow: hidden;
    }
    .node-avatar img { width: 100%; height: 100%; object-fit: cover; }
    
    .node-dept { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: var(--primary); margin-bottom: 5px; display: block; }
    .node-name { font-size: 0.95rem; font-weight: 800; color: var(--text-main); margin-bottom: 2px; display: block; }
    .node-pos { font-size: 0.75rem; color: var(--text-muted); }
</style>

<div class="card">
    <div class="card-header">
        <h3><i class="fas fa-sitemap" style="color: var(--primary); margin-right: 10px;"></i> Institutional Structure</h3>
    </div>
    <div class="card-body">
        <div class="org-container">
            <div class="org-root">
                <i class="fas fa-building" style="margin-right: 10px;"></i>
                <?php echo get_setting('company_name') ?? 'Headquarters'; ?>
            </div>
            
            <div class="org-grid">
                <?php while($node = mysqli_fetch_assoc($org_res)): ?>
                    <div class="org-node">
                        <span class="node-dept"><?php echo htmlspecialchars($node['dept_name']); ?></span>
                        <div class="node-avatar">
                            <?php if(!empty($node['profile_pic'])): ?>
                                <img src="<?php echo BASE_URL . $node['profile_pic']; ?>">
                            <?php else: ?>
                                <i class="fas fa-user-tie" style="color: var(--border);"></i>
                            <?php endif; ?>
                        </div>
                        <span class="node-name"><?php echo htmlspecialchars($node['manager_name'] ?? 'Vacant Position'); ?></span>
                        <span class="node-pos"><?php echo htmlspecialchars($node['manager_pos'] ?? 'Head of Department'); ?></span>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
