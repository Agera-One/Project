<?php
require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_admin();

$report_type = isset($_GET['type']) ? sanitize_input($_GET['type']) : 'daily';
$date_from = isset($_GET['date_from']) ? sanitize_input($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? sanitize_input($_GET['date_to']) : date('Y-m-d');

$data = [];

try {
    // Daily Sales Report
    if ($report_type === 'daily') {
        $stmt = $pdo->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as orders,
                SUM(total) as revenue,
                SUM(tax) as tax,
                AVG(total) as avg_order
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
            GROUP BY DATE(created_at)
            ORDER BY created_at DESC
        ");
        $stmt->execute([$date_from, $date_to]);
        $data = $stmt->fetchAll();
    }

    // Payment Method Report
    elseif ($report_type === 'payment') {
        $stmt = $pdo->prepare("
            SELECT 
                payment_method,
                COUNT(*) as orders,
                SUM(total) as amount,
                AVG(total) as avg_order
            FROM orders
            WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
            GROUP BY payment_method
            ORDER BY amount DESC
        ");
        $stmt->execute([$date_from, $date_to]);
        $data = $stmt->fetchAll();
    }

    // Product Sales Report
    elseif ($report_type === 'products') {
        $stmt = $pdo->prepare("
            SELECT 
                p.name,
                SUM(oi.quantity) as qty_sold,
                SUM(oi.total) as revenue,
                AVG(oi.unit_price) as avg_price
            FROM order_items oi
            JOIN products p ON p.id = oi.product_id
            JOIN orders o ON o.id = oi.order_id
            WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status = 'completed'
            GROUP BY p.id
            ORDER BY revenue DESC
        ");
        $stmt->execute([$date_from, $date_to]);
        $data = $stmt->fetchAll();
    }

    // Cashier Performance Report
    elseif ($report_type === 'cashier') {
        $stmt = $pdo->prepare("
            SELECT 
                u.name,
                COUNT(o.id) as orders,
                SUM(o.total) as revenue,
                AVG(o.total) as avg_order,
                MAX(o.created_at) as last_order
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status = 'completed'
            GROUP BY u.id
            ORDER BY revenue DESC
        ");
        $stmt->execute([$date_from, $date_to]);
        $data = $stmt->fetchAll();
    }

    // Get summary stats
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_orders,
            SUM(total) as total_revenue,
            SUM(tax) as total_tax,
            AVG(total) as avg_order
        FROM orders
        WHERE DATE(created_at) BETWEEN ? AND ? AND status = 'completed'
    ");
    $stmt->execute([$date_from, $date_to]);
    $summary = $stmt->fetch();

} catch (PDOException $e) {
    die('Error generating report: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Green Grounds Coffee POS</title>
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

        .controls-card {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .control-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary);
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--bg-secondary);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .report-table th {
            background-color: var(--bg-primary);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }

        .report-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .report-table tbody tr:hover {
            background-color: var(--bg-primary);
        }

        .print-btn {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }

        @media print {
            .controls-card {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="/admin/dashboard.php" class="logo">☕ Green Grounds Admin</a>
                <h2 style="flex: 1; text-align: center; margin: 0;">Reports</h2>
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
                <li><a href="/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/admin/products.php">Products</a></li>
                <li><a href="/admin/inventory.php">Inventory</a></li>
                <li><a href="/admin/orders.php">Orders</a></li>
                <li><a href="/admin/users.php">Users</a></li>
                <li><a href="/admin/reports.php" class="active">Reports</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="page-container">
        <h1 class="page-title">Reports & Analytics</h1>

        <!-- Controls -->
        <div class="controls-card">
            <form method="GET" class="report-form">
                <div class="control-grid">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Report Type</label>
                        <select name="type" class="form-control" onchange="this.form.submit()">
                            <option value="daily" <?php echo $report_type === 'daily' ? 'selected' : ''; ?>>Daily Sales</option>
                            <option value="payment" <?php echo $report_type === 'payment' ? 'selected' : ''; ?>>Payment Method</option>
                            <option value="products" <?php echo $report_type === 'products' ? 'selected' : ''; ?>>Product Sales</option>
                            <option value="cashier" <?php echo $report_type === 'cashier' ? 'selected' : ''; ?>>Cashier Performance</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                    <button type="button" class="print-btn" onclick="window.print();">🖨️ Print</button>
                </div>
            </form>
        </div>

        <!-- Summary Statistics -->
        <div class="summary-cards">
            <div class="summary-card">
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo $summary['total_orders'] ?? 0; ?></div>
            </div>
            <div class="summary-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">$<?php echo number_format($summary['total_revenue'] ?? 0, 0); ?></div>
            </div>
            <div class="summary-card">
                <div class="stat-label">Total Tax</div>
                <div class="stat-value">$<?php echo number_format($summary['total_tax'] ?? 0, 2); ?></div>
            </div>
            <div class="summary-card">
                <div class="stat-label">Average Order</div>
                <div class="stat-value">$<?php echo number_format($summary['avg_order'] ?? 0, 2); ?></div>
            </div>
        </div>

        <!-- Report Table -->
        <?php if (!empty($data)): ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <?php if ($report_type === 'daily'): ?>
                            <th>Date</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                            <th>Tax</th>
                            <th>Avg Order</th>
                        <?php elseif ($report_type === 'payment'): ?>
                            <th>Payment Method</th>
                            <th>Orders</th>
                            <th>Amount</th>
                            <th>Avg Order</th>
                        <?php elseif ($report_type === 'products'): ?>
                            <th>Product</th>
                            <th>Qty Sold</th>
                            <th>Revenue</th>
                            <th>Avg Price</th>
                        <?php elseif ($report_type === 'cashier'): ?>
                            <th>Cashier</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                            <th>Avg Order</th>
                            <th>Last Order</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php if ($report_type === 'daily'): ?>
                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                <td><?php echo $row['orders']; ?></td>
                                <td><strong>$<?php echo number_format($row['revenue'], 2); ?></strong></td>
                                <td>$<?php echo number_format($row['tax'], 2); ?></td>
                                <td>$<?php echo number_format($row['avg_order'], 2); ?></td>
                            <?php elseif ($report_type === 'payment'): ?>
                                <td style="text-transform: capitalize;"><strong><?php echo htmlspecialchars($row['payment_method']); ?></strong></td>
                                <td><?php echo $row['orders']; ?></td>
                                <td>$<?php echo number_format($row['amount'], 2); ?></td>
                                <td>$<?php echo number_format($row['avg_order'], 2); ?></td>
                            <?php elseif ($report_type === 'products'): ?>
                                <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td><?php echo $row['qty_sold']; ?></td>
                                <td>$<?php echo number_format($row['revenue'], 2); ?></td>
                                <td>$<?php echo number_format($row['avg_price'], 2); ?></td>
                            <?php elseif ($report_type === 'cashier'): ?>
                                <td><strong><?php echo htmlspecialchars($row['name'] ?? 'Unknown'); ?></strong></td>
                                <td><?php echo $row['orders']; ?></td>
                                <td>$<?php echo number_format($row['revenue'], 2); ?></td>
                                <td>$<?php echo number_format($row['avg_order'], 2); ?></td>
                                <td style="font-size: 0.9rem; color: var(--text-secondary);">
                                    <?php echo $row['last_order'] ? date('M d H:i', strtotime($row['last_order'])) : 'N/A'; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No data available for the selected period</div>
        <?php endif; ?>
    </div>
</body>
</html>
