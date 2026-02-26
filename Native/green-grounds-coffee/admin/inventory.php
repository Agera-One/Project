<?php
require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_admin();

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Get inventory
$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id WHERE 1=1";
$params = [];

if ($search) {
    $query .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$query .= " ORDER BY p.quantity ASC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    die('Error loading inventory: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Green Grounds Coffee POS</title>
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

        .search-card {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--bg-secondary);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .inventory-table th {
            background-color: var(--bg-primary);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }

        .inventory-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .inventory-table tbody tr:hover {
            background-color: var(--bg-primary);
        }

        .qty-low {
            color: var(--accent);
            font-weight: 600;
        }

        .qty-ok {
            color: var(--primary);
            font-weight: 600;
        }

        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-available {
            background-color: #e8f5e9;
            color: #1b5e20;
        }

        .badge-unavailable {
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
                <a href="/admin/dashboard.php" class="logo">☕ Green Grounds Admin</a>
                <h2 style="flex: 1; text-align: center; margin: 0;">Inventory</h2>
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
                <li><a href="inventory.php" class="active">Inventory</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="page-container">
        <h1 class="page-title">Inventory Management</h1>

        <!-- Search -->
        <div class="search-card">
            <form method="GET" class="search-form">
                <div style="display: flex; gap: 1rem;">
                    <input type="text" name="search" class="form-control" placeholder="Search by product name or SKU..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                    <a href="/admin/inventory.php" class="btn btn-outline">Clear</a>
                </div>
            </form>
        </div>

        <!-- Inventory Table -->
        <?php if (!empty($products)): ?>
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>SKU</th>
                        <th>Current Stock</th>
                        <th>Price</th>
                        <th>Stock Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($product['category_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></td>
                            <td class="<?php echo $product['quantity'] < 10 ? 'qty-low' : 'qty-ok'; ?>">
                                <?php echo $product['quantity']; ?>
                                <?php if ($product['quantity'] < 10): ?>
                                    <span style="font-size: 0.8rem;">⚠ Low</span>
                                <?php endif; ?>
                            </td>
                            <td>$<?php echo number_format($product['price'], 2); ?></td>
                            <td>$<?php echo number_format($product['price'] * $product['quantity'], 2); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $product['status']; ?>">
                                    <?php echo ucfirst($product['status']); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="alert alert-info">No products found</div>
        <?php endif; ?>
    </div>
</body>
</html>
