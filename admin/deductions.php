<?php
require_once '../includes/header.php';
get_header("Deductions Management");

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM deductions WHERE id = $id");
    echo "<script>window.location.href='deductions.php';</script>";
}

// Handle Add/Edit
$error = '';
$success = '';
$edit_ded = null;

if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $edit_ded = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM deductions WHERE id = $id"));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $type = $_POST['type'];
    $amount = (float)$_POST['amount'];
    $is_mandatory = isset($_POST['is_mandatory']) ? 1 : 0;
    
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $query = "UPDATE deductions SET name='$name', type='$type', amount=$amount, is_mandatory=$is_mandatory WHERE id=$id";
    } else {
        $query = "INSERT INTO deductions (name, type, amount, is_mandatory) VALUES ('$name', '$type', $amount, $is_mandatory)";
    }
    
    if (mysqli_query($conn, $query)) {
        $success = "Deduction saved successfully!";
        echo "<script>setTimeout(() => { window.location.href='deductions.php'; }, 1000);</script>";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Get deductions
$deductions = mysqli_query($conn, "SELECT * FROM deductions ORDER BY is_mandatory DESC, name ASC");
?>

<div class="dashboard-grid">
    <!-- Form Side -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas <?php echo $edit_ded ? 'fa-edit' : 'fa-plus-circle'; ?>" style="margin-right: 10px; color: var(--primary);"></i> <?php echo $edit_ded ? 'Edit' : 'Add'; ?> Deduction</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <?php if ($edit_ded): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_ded['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Deduction Name</label>
                    <div class="form-icon-group">
                        <i class="fas fa-hand-holding-usd"></i>
                        <input type="text" name="name" required value="<?php echo $edit_ded['name'] ?? ''; ?>" placeholder="e.g. PAYE Tax">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Calculation Type</label>
                    <div class="form-icon-group">
                        <i class="fas fa-calculator"></i>
                        <select name="type" required>
                            <option value="fixed" <?php echo ($edit_ded['type'] ?? '') == 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
                            <option value="percentage" <?php echo ($edit_ded['type'] ?? '') == 'percentage' ? 'selected' : ''; ?>>Percentage (%)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Amount / Rate</label>
                    <div class="form-icon-group">
                        <i class="fas fa-coins"></i>
                        <input type="number" step="0.01" name="amount" required value="<?php echo $edit_ded['amount'] ?? '0.00'; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Applicability</label>
                    <div style="margin-top: 15px; padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--border);">
                        <input type="checkbox" name="is_mandatory" id="is_mandatory" <?php echo ($edit_ded['is_mandatory'] ?? 1) ? 'checked' : ''; ?>>
                        <label for="is_mandatory" style="display: inline; font-weight: 700; color: var(--text-main); margin-left: 8px;"> Mandatory for all employees</label>
                        <p style="margin: 5px 0 0 28px; font-size: 0.75rem; color: var(--text-muted);">If checked, this deduction will be automatically applied to every payroll record generated.</p>
                    </div>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary btn-block"><?php echo $edit_ded ? 'Update' : 'Save'; ?> Deduction</button>
                    <?php if ($edit_ded): ?>
                        <a href="deductions.php" class="btn btn-outline btn-block" style="margin-top: 10px;">Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- List Side -->
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-minus-circle" style="margin-right: 10px; color: var(--danger);"></i> Deductions Configuration</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Deduction</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $has_records = false;
                        while ($ded = mysqli_fetch_assoc($deductions)): 
                            $has_records = true;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($ded['name']); ?></strong></td>
                            <td><small style="font-weight: 700; text-transform: uppercase; color: var(--text-muted);"><?php echo $ded['type']; ?></small></td>
                            <td><strong style="color: var(--danger);"><?php echo $ded['type'] == 'percentage' ? $ded['amount'] . '%' : format_currency($ded['amount']); ?></strong></td>
                            <td>
                                <?php if ($ded['is_mandatory']): ?>
                                    <span class="badge badge-danger" style="font-size: 0.65rem;">MANDATORY</span>
                                <?php else: ?>
                                    <span class="badge badge-info" style="font-size: 0.65rem;">OPTIONAL</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="deductions.php?edit=<?php echo $ded['id']; ?>" class="btn btn-outline" style="padding: 6px 10px; font-size: 0.75rem;"><i class="fas fa-edit"></i></a>
                                    <a href="deductions.php?delete=<?php echo $ded['id']; ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.75rem;" onclick="return confirm('Delete this deduction?')"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php if (!$has_records): ?>
                    <div class="empty-state">
                        <i class="fas fa-receipt"></i>
                        <h3>No Deductions</h3>
                        <p>Define taxes, insurance, and other deductions to be applied to payroll.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
