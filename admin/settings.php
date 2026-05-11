<?php
require_once '../includes/header.php';
get_header("System Settings");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['settings'] as $key => $value) {
        $key = mysqli_real_escape_string($conn, $key);
        $value = mysqli_real_escape_string($conn, $value);
        mysqli_query($conn, "UPDATE system_settings SET setting_value = '$value' WHERE setting_key = '$key'");
    }
    hr_audit_log('Update Settings', "Global system configurations were modified by the administrator.");
    $success = "System settings updated successfully!";
}

$settings_res = mysqli_query($conn, "SELECT * FROM system_settings");
$settings = [];
while($s = mysqli_fetch_assoc($settings_res)) {
    $settings[$s['setting_key']] = $s['setting_value'];
}
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div class="card-header">
        <h3><i class="fas fa-cogs" style="margin-right: 10px; color: var(--primary);"></i> Global Configuration</h3>
    </div>
    <div class="card-body" style="padding: 40px;">
        <form method="POST">
            <div style="display: flex; flex-direction: column; gap: 25px;">
                <div class="form-group">
                    <label>Company Name</label>
                    <div class="form-icon-group">
                        <i class="fas fa-building"></i>
                        <input type="text" name="settings[company_name]" required value="<?php echo htmlspecialchars($settings['company_name']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Currency Symbol</label>
                    <div class="form-icon-group">
                        <i class="fas fa-coins"></i>
                        <input type="text" name="settings[currency_symbol]" required value="<?php echo htmlspecialchars($settings['currency_symbol']); ?>" placeholder="e.g. $, £, RWF">
                    </div>
                </div>

                <div class="form-group">
                    <label>System Admin Email</label>
                    <div class="form-icon-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="settings[system_email]" required value="<?php echo htmlspecialchars($settings['system_email']); ?>">
                    </div>
                </div>

                <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 0;">
                    <div class="form-group">
                        <label>Primary Brand Color</label>
                        <div class="form-icon-group">
                            <i class="fas fa-palette"></i>
                            <input type="color" name="settings[primary_color]" value="<?php echo $settings['primary_color'] ?? '#6366f1'; ?>" style="height: 45px; padding: 5px;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Accent Highlight Color</label>
                        <div class="form-icon-group">
                            <i class="fas fa-magic"></i>
                            <input type="color" name="settings[accent_color]" value="<?php echo $settings['accent_color'] ?? '#f472b6'; ?>" style="height: 45px; padding: 5px;">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Standard Annual Leave Balance (Days)</label>
                    <div class="form-icon-group">
                        <i class="fas fa-calendar-day"></i>
                        <input type="number" name="settings[default_leave_balance]" required value="<?php echo htmlspecialchars($settings['default_leave_balance'] ?? '15'); ?>">
                    </div>
                    <small style="color: var(--text-muted);">Standard leave entitlement for new employees.</small>
                </div>

                <div class="form-group">
                    <label>Self Registration</label>
                    <div style="padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border);">
                        <select name="settings[allow_self_registration]" required>
                            <option value="1" <?php echo $settings['allow_self_registration'] == '1' ? 'selected' : ''; ?>>Enabled - Employees can register themselves</option>
                            <option value="0" <?php echo $settings['allow_self_registration'] == '0' ? 'selected' : ''; ?>>Disabled - Only Admin can add employees</option>
                        </select>
                    </div>
                </div>

                <div style="margin-top: 20px; border-top: 1px solid var(--border); padding-top: 30px; text-align: right;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 40px;">Save Global Changes <i class="fas fa-save" style="margin-left: 8px;"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 30px auto;">
    <div class="card-header">
        <h3><i class="fas fa-tools" style="margin-right: 10px; color: var(--danger);"></i> System Maintenance</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div style="padding: 25px 30px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 style="font-weight: 800; margin-bottom: 5px;">Clear Security Logs</h4>
                <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0;">Remove all audit and login logs older than 30 days to optimize performance.</p>
            </div>
            <a href="maintenance_actions.php?action=clear_logs" class="btn btn-outline" style="color: var(--danger); border-color: var(--danger);" onclick="return confirm('This will permanently delete old logs. Continue?')">Execute Purge</a>
        </div>
        <div style="padding: 25px 30px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h4 style="font-weight: 800; margin-bottom: 5px;">New Year Leave Reset</h4>
                <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0;">Reset every employee's leave balance to the organization's standard entitlement.</p>
            </div>
            <a href="maintenance_actions.php?action=reset_leave" class="btn btn-outline" style="color: var(--warning); border-color: var(--warning);" onclick="return confirm('Are you sure you want to reset ALL leave balances? This cannot be undone.')">Reset All Balances</a>
        </div>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 30px auto;">
    <div class="card-header">
        <h3><i class="fas fa-database" style="margin-right: 10px; color: var(--warning);"></i> Data Portability</h3>
    </div>
    <div class="card-body" style="padding: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h4 style="font-weight: 800; margin-bottom: 5px;">Full Database Snapshot</h4>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0;">Export your entire database schema and records to a single SQL backup file.</p>
        </div>
        <a href="export_db.php" class="btn btn-outline" style="border-radius: 12px; padding: 12px 25px;"><i class="fas fa-download" style="margin-right: 8px;"></i> Download SQL Dump</a>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
