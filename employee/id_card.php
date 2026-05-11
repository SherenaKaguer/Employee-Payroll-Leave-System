<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

$user = hr_get_current_user();
$user_id = $_SESSION['user_id'];

// Get full info
$query = "SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = $user_id";
$emp = mysqli_fetch_assoc(mysqli_query($conn, $query));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Digital ID Card - <?php echo $emp['full_name']; ?></title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; }
        
        .id-card {
            width: 350px;
            height: 550px;
            background: white;
            border-radius: 30px;
            box-shadow: 0 30px 60px -12px rgba(0,0,0,0.15);
            overflow: hidden;
            position: relative;
            text-align: center;
            border: 1px solid var(--border);
            animation: fadeIn 0.8s ease-out;
        }

        .id-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            z-index: 1;
        }

        .id-card-content {
            position: relative;
            z-index: 2;
            padding: 40px 20px;
        }

        .id-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            color: white;
            margin-bottom: 30px;
        }

        .id-photo-wrapper {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            margin: 0 auto 20px;
            overflow: hidden;
            background: white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .id-photo-wrapper img, .id-photo-wrapper .initial-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .id-name {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 5px;
        }

        .id-position {
            font-size: 0.9rem;
            color: var(--primary);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }

        .id-details {
            text-align: left;
            padding: 0 20px;
            border-top: 1px solid var(--border);
            padding-top: 25px;
        }

        .id-detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .id-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: var(--text-muted);
            font-weight: 800;
        }

        .id-value {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .id-footer {
            margin-top: 30px;
            padding: 20px;
            background: #f8fafc;
            border-top: 1px solid var(--border);
        }

        .qr-placeholder {
            width: 60px;
            height: 60px;
            margin: 0 auto;
            border: 1px solid var(--border);
            padding: 5px;
            background: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--text-main);
        }

        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
            .id-card { box-shadow: none; border: 1px solid #eee; margin: 0 auto; }
        }
    </style>
</head>
<body>

    <div class="id-card">
        <div class="id-card-content">
            <div class="id-logo">
                <img src="../favicon.svg" width="30">
                <h2 style="font-size: 1.2rem; font-weight: 800; margin: 0;"><?php echo get_setting('company_name') ?? 'HRMS'; ?></h2>
            </div>

            <div class="id-photo-wrapper">
                <?php 
                $pic = get_profile_pic($emp);
                if ($pic): ?>
                    <img src="<?php echo $pic; ?>" alt="Profile">
                <?php else: ?>
                    <div class="initial-avatar" style="background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 3rem; font-weight: 800;">
                        <?php echo strtoupper(substr($emp['full_name'], 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>

            <h1 class="id-name"><?php echo htmlspecialchars($emp['full_name']); ?></h1>
            <p class="id-position"><?php echo htmlspecialchars($emp['position']); ?></p>

            <div class="id-details">
                <div class="id-detail-row">
                    <span class="id-label">Employee ID</span>
                    <span class="id-value"><?php echo $emp['employee_id']; ?></span>
                </div>
                <div class="id-detail-row">
                    <span class="id-label">Department</span>
                    <span class="id-value"><?php echo htmlspecialchars($emp['department_name'] ?? 'General'); ?></span>
                </div>
                <div class="id-detail-row">
                    <span class="id-label">Date Joined</span>
                    <span class="id-value"><?php echo date('M Y', strtotime($emp['hire_date'])); ?></span>
                </div>
            </div>

            <div class="id-footer">
                <div class="qr-placeholder">
                    <i class="fas fa-qrcode"></i>
                </div>
                <p style="font-size: 0.6rem; color: var(--text-muted); margin-top: 10px; font-weight: 700;">OFFICIAL DIGITAL IDENTITY</p>
            </div>
        </div>
    </div>

    <div class="no-print" style="position: fixed; top: 20px; right: 20px; display: flex; gap: 10px;">
        <button onclick="window.print()" class="btn btn-primary" style="border-radius: 100px; padding: 10px 25px;"><i class="fas fa-print"></i> Print ID</button>
        <a href="profile.php" class="btn btn-outline" style="border-radius: 100px; padding: 10px 20px;">Back to Profile</a>
    </div>

</body>
</html>