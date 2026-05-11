<?php
require_once 'includes/session.php';
hr_session_start();
hr_session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goodbye - HR Payroll System</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .logout-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .logout-card {
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(20px);
            padding: 60px;
            border-radius: 40px;
            border: 1px solid rgba(255,255,255,0.1);
            max-width: 500px;
            width: 100%;
            animation: fadeInScale 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }
        .success-checkmark {
            width: 80px;
            height: 80px;
            background: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 2rem;
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.4);
        }
    </style>
</head>
<body class="dark-mode">
    <div class="logout-container">
        <div class="logout-card">
            <div class="success-checkmark">
                <i class="fas fa-check"></i>
            </div>
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 10px;">Safe Travels!</h1>
            <p style="color: #94a3b8; font-size: 1.1rem; margin-bottom: 40px;">You have been securely logged out. We've enjoyed having you today.</p>
            
            <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 20px; margin-bottom: 40px;">
                <p style="font-size: 0.9rem; color: #cbd5e1;">Redirecting to sign-in in <span id="countdown">5</span> seconds...</p>
            </div>
            
            <a href="login.php" class="btn btn-primary btn-block" style="padding: 15px;">Return to Login Now</a>
        </div>
    </div>

    <script>
        let count = 5;
        const countdownEl = document.getElementById('countdown');
        const timer = setInterval(() => {
            count--;
            countdownEl.innerText = count;
            if (count <= 0) {
                clearInterval(timer);
                window.location.href = 'login.php';
            }
        }, 1000);
    </script>
</body>
</html>
