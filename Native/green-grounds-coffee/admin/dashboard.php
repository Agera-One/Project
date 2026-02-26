<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_admin();

// Get dashboard statistics
try {
    // Today's revenue
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_orders,
            SUM(total) as total_revenue,
            AVG(total) as avg_order_value
        FROM orders
        WHERE DATE(created_at) = CURDATE() AND status = 'completed'
    ");
    $today_stats = $stmt->fetch();

    // This month's revenue
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_orders,
            SUM(total) as total_revenue
        FROM orders
        WHERE MONTH(created_at) = MONTH(NOW()) 
            AND YEAR(created_at) = YEAR(NOW())
            AND status = 'completed'
    ");
    $month_stats = $stmt->fetch();

    // Top products
    $stmt = $pdo->query("
        SELECT 
            p.name,
            p.price,
            SUM(oi.quantity) as total_sold,
            SUM(oi.total) as revenue
        FROM order_items oi
        JOIN products p ON p.id = oi.product_id
        JOIN orders o ON o.id = oi.order_id
        WHERE DATE(o.created_at) = CURDATE() AND o.status = 'completed'
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $top_products = $stmt->fetchAll();

    // Recent orders
    $stmt = $pdo->query("
        SELECT 
            o.id,
            o.receipt_number,
            o.customer_name,
            o.total,
            o.payment_method,
            o.order_type,
            o.created_at,
            u.name as cashier_name
        FROM orders o
        LEFT JOIN users u ON u.id = o.user_id
        WHERE o.status = 'completed'
        ORDER BY o.created_at DESC
        LIMIT 10
    ");
    $recent_orders = $stmt->fetchAll();

    // Total inventory value
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total_products,
            SUM(quantity) as total_items,
            SUM(quantity * price) as inventory_value
        FROM products
        WHERE status = 'available'
    ");
    $inventory_stats = $stmt->fetch();

    // Users count
    $stmt = $pdo->query("
        SELECT COUNT(*) as total_users FROM users WHERE status = 'active'
    ");
    $user_count = $stmt->fetchColumn();

} catch (PDOException $e) {
    die('Database error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Green Grounds Coffee POS</title>
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
            flex-wrap: wrap;
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

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem 2rem;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .dashboard-title {
            margin: 0;
            color: var(--primary);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
        }

        .stat-card.secondary {
            border-left-color: var(--secondary);
        }

        .stat-card.accent {
            border-left-color: var(--accent);
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .stat-change {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .section-card {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.75rem;
        }

        .top-products-list {
            list-style: none;
        }

        .product-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }

        .product-stats {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .product-revenue {
            text-align: right;
        }

        .product-sold {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .orders-table {
            width: 100%;
            border-collapse: collapse;
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

        .full-width {
            grid-column: 1 / -1;
        }

        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .orders-table {
                font-size: 0.9rem;
            }

            .orders-table th,
            .orders-table td {
                padding: 0.75rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="dashboard.php" class="logo">☕ Green Grounds Admin</a>
                <h2 style="flex: 1; text-align: center; margin: 0;">Dashboard</h2>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <a href="../api/logout.php" class="btn btn-outline btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Admin Navigation -->
    <nav class="admin-nav">
        <div class="nav-container">
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Dashboard</h1>
            <div style="color: var(--text-secondary); font-size: 0.95rem;">
                Today: <?php echo date('l, F d, Y'); ?>
            </div>
        </div>

        <!-- Key Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Today's Revenue</div>
                <div class="stat-value">$<?php echo number_format($today_stats['total_revenue'] ?? 0, 2); ?></div>
                <div class="stat-change"><?php echo ($today_stats['total_orders'] ?? 0); ?> orders</div>
            </div>

            <div class="stat-card secondary">
                <div class="stat-label">This Month</div>
                <div class="stat-value">$<?php echo number_format($month_stats['total_revenue'] ?? 0, 0); ?></div>
                <div class="stat-change"><?php echo ($month_stats['total_orders'] ?? 0); ?> total orders</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Average Order</div>
                <div class="stat-value">$<?php echo number_format($today_stats['avg_order_value'] ?? 0, 2); ?></div>
                <div class="stat-change">Per transaction</div>
            </div>

            <div class="stat-card accent">
                <div class="stat-label">Inventory Value</div>
                <div class="stat-value">$<?php echo number_format($inventory_stats['inventory_value'] ?? 0, 0); ?></div>
                <div class="stat-change"><?php echo ($inventory_stats['total_items'] ?? 0); ?> items</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Active Users</div>
                <div class="stat-value"><?php echo $user_count; ?></div>
                <div class="stat-change">Team members</div>
            </div>

            <div class="stat-card secondary">
                <div class="stat-label">Products</div>
                <div class="stat-value"><?php echo ($inventory_stats['total_products'] ?? 0); ?></div>
                <div class="stat-change">In catalog</div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Top Products -->
            <div class="section-card">
                <div class="section-title">Top Products Today</div>
                <?php if (!empty($top_products)): ?>
                    <ul class="top-products-list">
                        <?php foreach ($top_products as $product): ?>
                            <li class="product-item">
                                <div class="product-info">
                                    <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                    <div class="product-stats"><?php echo $product['total_sold']; ?> sold</div>
                                </div>
                                <div class="product-revenue">
                                    <div class="product-sold">$<?php echo number_format($product['revenue'], 2); ?></div>
                                    <div class="product-stats">@ $<?php echo number_format($product['price'], 2); ?></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">No sales yet today</p>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="section-card">
                <div class="section-title">Quick Actions</div>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <a href="products.php" class="btn btn-primary" style="text-align: center;">
                        ➕ Add New Product
                    </a>
                    <a href="users.php" class="btn btn-outline" style="text-align: center;">
                        👥 Manage Users
                    </a>
                    <a href="inventory.php" class="btn btn-outline" style="text-align: center;">
                        📦 Check Inventory
                    </a>
                    <a href="reports.php" class="btn btn-outline" style="text-align: center;">
                        📊 View Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="section-card full-width">
            <div class="section-title">Recent Orders</div>
            <?php if (!empty($recent_orders)): ?>
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Receipt</th>
                            <th>Customer</th>
                            <th>Cashier</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Type</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><a href="/cashier/receipt.php?order_id=<?php echo $order['id']; ?>" style="color: var(--primary); font-weight: 500;">
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
                                    <?php echo date('H:i', strtotime($order['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">No orders yet</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
