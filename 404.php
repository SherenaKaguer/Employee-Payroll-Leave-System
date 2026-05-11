<?php
require_once 'includes/functions.php';
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #020617;
            color: white;
            text-align: center;
            padding: 20px;
        }
        .error-card {
            background: rgba(255,255,255,0.03);
            backdrop-filter: blur(20px);
            padding: 60px;
            border-radius: 40px;
            border: 1px solid rgba(255,255,255,0.1);
            max-width: 600px;
            width: 100%;
            animation: fadeInScale 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        .error-code {
            font-size: 8rem;
            font-weight: 900;
            margin: 0;
            line-height: 1;
            background: linear-gradient(to right, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            opacity: 0.8;
        }
    </style>
</head>
<body class="dark-mode">
    <div class="bg-blob blob-1"></div>
    <div class="bg-blob blob-2"></div>
    
    <div class="error-page">
        <div class="error-card">
            <h1 class="error-code">404</h1>
            <h2 style="font-size: 2rem; font-weight: 800; margin: 20px 0;">Destination Not Found</h2>
            <p style="color: #94a3b8; font-size: 1.1rem; margin-bottom: 40px;">The resource you are looking for has been moved, archived, or simply doesn't exist in our high-fidelity suite.</p>
            
            <div style="display: flex; gap: 15px; justify-content: center;">
                <a href="index.php" class="btn btn-primary" style="padding: 14px 30px; border-radius: 100px;">Back to Dashboard</a>
                <button onclick="history.back()" class="btn btn-outline" style="padding: 14px 30px; border-radius: 100px; color: white;">Return Previous</button>
            </div>
        </div>
    </div>
</body>
</html>