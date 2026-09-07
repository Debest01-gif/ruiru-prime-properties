<?php
/**
 * Admin Login Page
 */
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Already logged in
if (isAdminLoggedIn()) {
    redirect(SITE_URL . '/admin/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']   = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_role'] = $user['role'];

            // Update last login
            $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

            redirect(SITE_URL . '/admin/index.php');
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    } else {
        $error = 'Please enter your email and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | <?= e(setting('company_name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .login-bg {
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse at 20% 50%, rgba(212,168,67,0.06) 0%, transparent 60%),
                radial-gradient(ellipse at 80% 20%, rgba(30,64,175,0.1) 0%, transparent 50%),
                #06091a;
            z-index: 0;
        }
        .admin-login-page { position: relative; z-index: 1; }

        .form-input {
            width: 100%;
            padding: 13px 16px 13px 44px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #e2e8f0;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            outline: none;
            font-family: 'Inter', sans-serif;
        }
        .form-input:focus {
            border-color: #d4a843;
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 0 3px rgba(212,168,67,0.12);
        }
        .form-input::-ms-reveal,
        .form-input::-ms-clear {
            display: none !important;
        }
        .form-input-wrap { position: relative; margin-bottom: 16px; }
        .form-input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 0.9rem;
        }
        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #d4a843, #b8902a);
            border: none;
            border-radius: 12px;
            color: #000;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 8px;
            font-family: 'Inter', sans-serif;
        }
        .login-btn:hover { opacity: 0.9; transform: translateY(-1px); box-shadow: 0 8px 25px rgba(212,168,67,0.35); }
    </style>
</head>
<body>
<div class="login-bg"></div>

<div class="admin-login-page">
    <div class="admin-login-box">
        <!-- Logo -->
        <div style="text-align:center;margin-bottom:32px;">
            <div style="width:60px;height:60px;background:linear-gradient(135deg,#d4a843,#b8902a);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-size:1.5rem;color:#000;box-shadow:0 8px 25px rgba(212,168,67,0.3);">
                <i class="fas fa-building"></i>
            </div>
            <h2 style="font-size:1.3rem;font-weight:700;color:#e2e8f0;margin-bottom:4px;"><?= e(setting('company_name')) ?></h2>
            <p style="color:#64748b;font-size:0.85rem;">Admin Dashboard — Secure Login</p>
        </div>

        <?php if ($error): ?>
        <div class="admin-alert admin-alert-danger" style="margin-bottom:20px;">
            <i class="fas fa-exclamation-circle"></i> <?= e($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <div style="margin-bottom:16px;">
                <label style="font-size:0.8rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:8px;">Email Address</label>
                <div class="form-input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" class="form-input"
                           placeholder="Enter your email"
                           autocomplete="off"
                           data-lpignore="true"
                           data-1p-ignore="true"
                           data-form-type="other"
                           value="<?= e($_POST['email'] ?? '') ?>" required autofocus>
                </div>
            </div>

            <div style="margin-bottom:24px;">
                <label style="font-size:0.8rem;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;display:block;margin-bottom:8px;">Password</label>
                <div class="form-input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" class="form-input" 
                           placeholder="Enter your password" 
                           autocomplete="new-password"
                           data-lpignore="true"
                           data-1p-ignore="true"
                           data-form-type="other"
                           required>
                </div>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login to Dashboard
            </button>
        </form>
    </div>
</div>
</body>
</html>
