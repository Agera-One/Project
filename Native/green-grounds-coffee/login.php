<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/utils.php';
require_once __DIR__ . '/config/session.php';

// if (is_logged_in()) {
//     redirect($_SESSION['user_role'] === 'admin'
//         ? 'admin/dashboard.php'
//         : 'cashier/index.php');
// }

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid request token. Please try again.';
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $errors[] = 'Please enter both email and password.';
        } elseif (!validate_email($email)) {
            $errors[] = 'Please enter a valid email address.';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if (!$user) {
                    $errors[] = 'Invalid email or password.';
                } elseif ($user['status'] !== 'active') {
                    $errors[] = 'Your account has been deactivated. Please contact administrator.';
                } elseif (!verify_password($password, $user['password'])) {
                    $errors[] = 'Invalid email or password.';
                } else {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    // Update last login
                    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $stmt->execute([$user['id']]);

                    // Log activity
                    log_activity($pdo, $user['id'], 'login', 'User logged in');

                    // Redirect based on role
                    redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'cashier/index.php');
                }
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$csrf_token = generate_csrf_token();
$session_expired = isset($_GET['session']) && $_GET['session'] === 'expired';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Green Grounds Coffee POS</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .login-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 100%);
        }

        .login-card {
            background-color: var(--bg-secondary);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .login-title {
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .login-btn {
            width: 100%;
            margin-top: 1.5rem;
        }

        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .demo-info {
            background-color: #f0f9ff;
            border: 1px solid #0369a1;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #0369a1;
        }

        .demo-info strong {
            display: block;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">☕</div>
                <h1 class="login-title">Green Grounds Coffee</h1>
                <p class="login-subtitle">Point of Sale System</p>
            </div>

            <?php if ($session_expired): ?>
                <div class="alert alert-warning">
                    Your session has expired. Please log in again.
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="demo-info">
                <strong>Demo Credentials:</strong>
                Email: admin@greengrounds.local<br>
                Password: admin123
            </div>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="admin@greengrounds.local"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-lg login-btn">
                    Sign In
                </button>
            </form>

            <div class="login-footer">
                <p>Green Grounds Coffee © 2026. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
