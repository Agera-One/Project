<?php
require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_admin();

// Get filter parameters
$date_from = isset($_GET['date_from']) ? sanitize_input($_GET['date_from']) : '';
$date_to = isset($_GET['date_to']) ? sanitize_input($_GET['date_to']) : '';
$payment_method = isset($_GET['payment_method']) ? sanitize_input($_GET['payment_method']) : '';

// Build query
$query = "SELECT o.*, u.name as cashier_name FROM orders o LEFT JOIN users u ON u.id = o.user_id WHERE 1=1";
$params = [];

if ($date_from) {
    $query .= " AND DATE(o.created_at) >= ?";
    $params[] = $date_from;
}

if ($date_to) {
    $query .= " AND DATE(o.created_at) <= ?";
    $params[] = $date_to;
}

if ($payment_method) {
    $query .= " AND o.payment_method = ?";
    $params[] = $payment_method;
}

$query .= " ORDER BY o.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error loading orders: ' . $e->getMessage());
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Green Grounds Coffee POS</title>
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

        .page-title {
            margin: 0 0 1.5rem 0;
            color: var(--primary);
        }

        .filter-card {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--bg-secondary);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .orders-table th {
            background-color: var(--bg-primary);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }

        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .orders-table tbody tr:hover {
            background-color: var(--bg-primary);
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-cash {
            background-color: #e8f5e9;
            color: #1b5e20;
        }

        .badge-card {
            background-color: #e3f2fd;
            color: #1565c0;
        }

        .badge-digital {
            background-color: #f3e5f5;
            color: #6a1b9a;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="dashboard.php" class="logo">☕ Green Grounds Admin</a>
                <h2 style="flex: 1; text-align: center; margin: 0;">Orders</h2>
                <div class="user-menu">
                    <a href="..../api/logout.php" class="btn btn-outline btn-sm">Logout</a>
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
                <li><a href="orders.php" class="active">Orders</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="page-container">
        <h1 class="page-title">Orders Management</h1>

        <!-- Filters -->
        <div class="filter-card">
            <h3 style="margin-top: 0; margin-bottom: 1rem;">Filter Orders</h3>
            <form method="GET" class="filter-form">
                <div class="filter-grid">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="">All Methods</option>
                            <option value="cash" <?php echo $payment_method === 'cash' ? 'selected' : ''; ?>>Cash</option>
                            <option value="card" <?php echo $payment_method === 'card' ? 'selected' : ''; ?>>Card</option>
                            <option value="digital" <?php echo $payment_method === 'digital' ? 'selected' : ''; ?>>Digital</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="orders.php" class="btn btn-outline">Clear Filters</a>
                </div>
            </form>
        </div>

        <!-- Orders Table -->
        <?php if (!empty($orders)): ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Receipt</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Type</th>
                        <th>Date & Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><a href="../cashier/receipt.php?order_id=<?php echo $order['id']; ?>" style="color: var(--primary); font-weight: 500;">
                                <?php echo htmlspecialchars($order['receipt_number']); ?>
                            </a></td>
                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['cashier_name'] ?? 'N/A'); ?></td>
                            <td style="font-weight: 600;">$<?php echo number_format($order['total'], 2); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $order['payment_method']; ?>">
                                    <?php echo ucfirst($order['payment_method']); ?>
                                </span>
                            </td>
                            <td style="text-transform: capitalize;"><?php echo str_replace('-', ' ', $order['order_type']); ?></td>
                            <td style="color: var(--text-secondary); font-size: 0.9rem;">
                                <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?>
                            </td>
                            <td>
                                <a href="../cashier/receipt.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline btn-sm">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No orders found matching your filters</div>
        <?php endif; ?>
    </div>
</body>
</html>
