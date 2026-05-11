<?php
require_once 'includes/auth.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';

hr_session_start();

// Redirect logged-in users
if (hr_is_logged_in()) {
    if (hr_is_admin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: employee/dashboard.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo get_setting('company_name') ?? 'Elite HRMS'; ?> - Workforce Excellence</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .landing-body {
            background: #020617;
            color: white;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .landing-nav {
            padding: 30px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background: rgba(2, 6, 23, 0.8);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .landing-hero {
            padding: 200px 50px 100px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .landing-title {
            font-size: 4.5rem;
            font-weight: 900;
            letter-spacing: -3px;
            line-height: 1;
            margin-bottom: 25px;
            animation: fadeInScale 1s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .landing-subtitle {
            font-size: 1.4rem;
            color: #94a3b8;
            max-width: 800px;
            margin: 0 auto 50px;
            line-height: 1.6;
            animation: fadeIn 1s 0.2s forwards;
            opacity: 0;
        }

        .cta-group {
            display: flex;
            gap: 20px;
            justify-content: center;
            animation: fadeIn 1s 0.4s forwards;
            opacity: 0;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding: 100px 50px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .feature-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.05);
            padding: 40px;
            border-radius: 32px;
            transition: all 0.3s;
            cursor: default;
        }

        .feature-card:hover {
            background: rgba(255,255,255,0.05);
            transform: translateY(-10px);
            border-color: var(--primary);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: rgba(99, 102, 241, 0.1);
            color: var(--primary);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 25px;
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        @media (max-width: 768px) {
            .landing-title { font-size: 2.8rem; }
            .landing-subtitle { font-size: 1.1rem; }
            .cta-group { flex-direction: column; }
        }
    </style>
</head>
<body class="landing-body">
    <div class="bg-blob blob-1" style="opacity: 0.2;"></div>
    <div class="bg-blob blob-2" style="opacity: 0.2;"></div>

    <nav class="landing-nav">
        <div class="logo-wrapper">
            <img src="favicon.svg" width="40">
            <h2 class="shine-text"><?php echo get_setting('company_name') ?? 'Elite HRMS'; ?></h2>
        </div>
        <div style="display: flex; gap: 15px;">
            <a href="login.php" class="btn btn-outline" style="border-radius: 100px; color: white;">Sign In</a>
            <?php if(get_setting('allow_self_registration') == '1'): ?>
                <a href="register.php" class="btn btn-primary" style="border-radius: 100px;">Join Us</a>
            <?php endif; ?>
        </div>
    </nav>

    <main>
        <section class="landing-hero">
            <h1 class="landing-title">Modern Workforce <br><span class="shine-text">Orchestration.</span></h1>
            <p class="landing-subtitle">The enterprise-grade solution for payroll intelligence, leave management, and employee engagement. Polished to perfection for the high-performance organization.</p>
            <div class="cta-group">
                <a href="login.php" class="btn btn-primary" style="padding: 16px 40px; font-size: 1rem; border-radius: 100px;">Access Dashboard <i class="fas fa-arrow-right" style="margin-left: 10px;"></i></a>
                <a href="#features" class="btn btn-outline" style="padding: 16px 40px; font-size: 1rem; border-radius: 100px; color: white; border-color: rgba(255,255,255,0.2);">Explore Features</a>
            </div>
        </section>

        <section id="features" class="feature-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-chart-line"></i></div>
                <h3 style="font-weight: 800; margin-bottom: 10px;">Smart Analytics</h3>
                <p style="color: #94a3b8; font-size: 0.9rem;">High-fidelity visualizations of payroll trends and workforce distribution using interactive Chart.js modules.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-calendar-check"></i></div>
                <h3 style="font-weight: 800; margin-bottom: 10px;">Leave Management</h3>
                <p style="color: #94a3b8; font-size: 0.9rem;">Sophisticated request workflows with real-time balance projectors and interactive workforce calendars.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                <h3 style="font-weight: 800; margin-bottom: 10px;">Forensic Security</h3>
                <p style="color: #94a3b8; font-size: 0.9rem;">Complete administrative audit trails and login history tracking to ensure absolute organizational integrity.</p>
            </div>
        </section>
    </main>

    <footer style="margin-top: auto; padding: 50px; text-align: center; border-top: 1px solid rgba(255,255,255,0.05); background: rgba(255,255,255,0.01);">
        <p style="color: #64748b; font-size: 0.85rem;">&copy; <?php echo date('Y'); ?> <?php echo get_setting('company_name') ?? 'Elite HRMS'; ?>. Precision Engineered Workforce Suite.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Parallax Blobs
            $(document).on('mousemove', function(e) {
                const x = e.clientX / window.innerWidth;
                const y = e.clientY / window.innerHeight;
                $('.blob-1').css('transform', `translate(${x * 80}px, ${y * 80}px)`);
                $('.blob-2').css('transform', `translate(-${x * 50}px, -${y * 50}px)`);
            });
        });
    </script>
</body>
</html>
