<?php
require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_admin();

$errors = [];
$success = '';
$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : 'list';
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = null;

// Load user if editing
if ($action === 'edit' && $user_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        if (!$user) {
            $action = 'list';
        }
    } catch (PDOException $e) {
        $errors[] = 'Error loading user: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid request token. Please try again.';
    } else {
        try {
            $name = sanitize_input($_POST['name'] ?? '');
            $email = sanitize_input($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = sanitize_input($_POST['role'] ?? 'cashier');
            $status = sanitize_input($_POST['status'] ?? 'active');

            if (empty($name)) {
                throw new Exception('Name is required');
            }
            if (empty($email) || !validate_email($email)) {
                throw new Exception('Valid email is required');
            }
            if ($action === 'add' && empty($password)) {
                throw new Exception('Password is required for new users');
            }
            if (!in_array($role, ['admin', 'cashier', 'manager'])) {
                throw new Exception('Invalid role selected');
            }

            if ($action === 'add') {
                // Check if email exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    throw new Exception('Email already exists');
                }

                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, role, status)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $email, hash_password($password), $role, $status]);
                log_activity($pdo, $_SESSION['user_id'], 'create_user', "User created: {$name}");
                $success = 'User added successfully';
                $action = 'list';
            } elseif ($action === 'edit' && $user_id > 0) {
                // Check if email is used by another user
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $user_id]);
                if ($stmt->fetch()) {
                    throw new Exception('Email is already in use by another user');
                }

                if (!empty($password)) {
                    $stmt = $pdo->prepare("
                        UPDATE users SET name = ?, email = ?, password = ?, role = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $email, hash_password($password), $role, $status, $user_id]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE users SET name = ?, email = ?, role = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $email, $role, $status, $user_id]);
                }

                log_activity($pdo, $_SESSION['user_id'], 'update_user', "User updated: {$name}");
                $success = 'User updated successfully';
                $user = null;
                $action = 'list';
            }
        } catch (Exception $e) {
            $errors[] = 'Error: ' . $e->getMessage();
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    if ($delete_id === $_SESSION['user_id']) {
        $errors[] = 'You cannot delete your own account';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $stmt->execute([$delete_id]);
            $del_user = $stmt->fetch();

            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$delete_id]);
            log_activity($pdo, $_SESSION['user_id'], 'delete_user', "User deleted: {$del_user['name']}");
            $success = 'User deleted successfully';
        } catch (PDOException $e) {
            $errors[] = 'Error deleting user: ' . $e->getMessage();
        }
    }
}

// Get users list
$users = [];
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY name ASC");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Error loading users: ' . $e->getMessage();
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Green Grounds Coffee POS</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-nav {
            background-color: var(--bg-secondary);
            box-shadow: var(--shadow);
            padding: 1rem 0;
            margin-bottom: 2rem;
            border-bottom: 3px solid var(--primary);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            color: var(--text-primary);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background-color: var(--primary);
            color: white;
        }

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem 2rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .page-title {
            margin: 0;
            color: var(--primary);
        }

        .form-container {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .form-grid.full {
            grid-template-columns: 1fr;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--bg-secondary);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .users-table th {
            background-color: var(--bg-primary);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }

        .users-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .users-table tbody tr:hover {
            background-color: var(--bg-primary);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .action-buttons .btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-admin {
            background-color: #f3e5f5;
            color: #6a1b9a;
        }

        .badge-cashier {
            background-color: #e3f2fd;
            color: #1565c0;
        }

        .badge-manager {
            background-color: #fff3e0;
            color: #e65100;
        }

        .badge-active {
            background-color: #e8f5e9;
            color: #1b5e20;
        }

        .badge-inactive {
            background-color: #ffebee;
            color: #c53030;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="dashboard.php" class="logo">☕ Green Grounds Admin</a>
                <h2 style="flex: 1; text-align: center; margin: 0;">Users</h2>
                <div class="user-menu">
                    <a href="../api/logout.php" class="btn btn-outline btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="admin-nav">
        <div class="nav-container">
            <ul class="nav-links">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="users.php" class="active">Users</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="page-container">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin-bottom: 1.5rem;">✓ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- User Form -->
            <div class="form-container">
                <h2 class="page-title" style="margin-bottom: 1.5rem;">
                    <?php echo $action === 'add' ? 'Add New User' : 'Edit User'; ?>
                </h2>

                <form method="POST" class="user-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="name">Name *</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password">
                                Password <?php echo ($action === 'edit') ? '(Leave blank to keep current)' : '*'; ?>
                            </label>
                            <input type="password" id="password" name="password" class="form-control" <?php echo ($action === 'add') ? 'required' : ''; ?>>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="role">Role *</label>
                            <select id="role" name="role" class="form-control" required>
                                <option value="cashier" <?php echo (!$user || $user['role'] === 'cashier') ? 'selected' : ''; ?>>Cashier</option>
                                <option value="manager" <?php echo ($user && $user['role'] === 'manager') ? 'selected' : ''; ?>>Manager</option>
                                <option value="admin" <?php echo ($user && $user['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="status">Status *</label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="active" <?php echo (!$user || $user['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($user && $user['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <a href="users.php" class="btn btn-outline" style="flex: 1;">Cancel</a>
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <?php echo $action === 'add' ? 'Add User' : 'Update User'; ?>
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Users List -->
            <div class="page-header">
                <h1 class="page-title">Users Management</h1>
                <a href="users.php?action=add" class="btn btn-primary">➕ Add New User</a>
            </div>

            <?php if (!empty($users)): ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($u['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $u['role']; ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $u['status']; ?>">
                                        <?php echo ucfirst($u['status']); ?>
                                    </span>
                                </td>
                                <td style="color: var(--text-secondary); font-size: 0.9rem;">
                                    <?php echo $u['last_login'] ? date('M d, Y H:i', strtotime($u['last_login'])) : 'Never'; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="users.php?action=edit&id=<?php echo $u['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                        <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                            <a href="users.php?delete=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?');">Delete</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No users found</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
