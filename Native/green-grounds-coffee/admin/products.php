<?php
require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_admin();

$errors = [];
$success = '';
$action = isset($_GET['action']) ? sanitize_input($_GET['action']) : 'list';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$categories = [];

// Get categories
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Error loading categories: ' . $e->getMessage();
}

// Load product if editing
if ($action === 'edit' && $product_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        if (!$product) {
            $action = 'list';
        }
    } catch (PDOException $e) {
        $errors[] = 'Error loading product: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid request token. Please try again.';
    } else {
        try {
            $name = sanitize_input($_POST['name'] ?? '');
            $category_id = (int)($_POST['category_id'] ?? 0);
            $price = (float)($_POST['price'] ?? 0);
            $cost = (float)($_POST['cost'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 0);
            $sku = sanitize_input($_POST['sku'] ?? '');
            $description = sanitize_input($_POST['description'] ?? '');
            $status = sanitize_input($_POST['status'] ?? 'available');

            if (empty($name)) {
                throw new Exception('Product name is required');
            }
            if ($category_id <= 0) {
                throw new Exception('Please select a category');
            }
            if ($price <= 0) {
                throw new Exception('Price must be greater than 0');
            }

            if ($action === 'add') {
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, category_id, price, cost, quantity, sku, description, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$name, $category_id, $price, $cost, $quantity, $sku, $description, $status]);
                log_activity($pdo, $_SESSION['user_id'], 'create_product', "Product created: {$name}");
                $success = 'Product added successfully';
                $action = 'list';
            } elseif ($action === 'edit' && $product_id > 0) {
                $stmt = $pdo->prepare("
                    UPDATE products SET
                        name = ?, category_id = ?, price = ?, cost = ?,
                        quantity = ?, sku = ?, description = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([$name, $category_id, $price, $cost, $quantity, $sku, $description, $status, $product_id]);
                log_activity($pdo, $_SESSION['user_id'], 'update_product', "Product updated: {$name}");
                $success = 'Product updated successfully';
                $product = null;
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
    try {
        $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
        $stmt->execute([$delete_id]);
        $del_product = $stmt->fetch();

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$delete_id]);
        log_activity($pdo, $_SESSION['user_id'], 'delete_product', "Product deleted: {$del_product['name']}");
        $success = 'Product deleted successfully';
    } catch (PDOException $e) {
        $errors[] = 'Error deleting product: ' . $e->getMessage();
    }
}

// Get products list
$products = [];
try {
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON c.id = p.category_id
        ORDER BY p.name ASC
    ");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Error loading products: ' . $e->getMessage();
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Green Grounds Coffee POS</title>
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

        .products-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--bg-secondary);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .products-table th {
            background-color: var(--bg-primary);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
        }

        .products-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .products-table tbody tr:hover {
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

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .page-header .btn {
                width: 100%;
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
                <h2 style="flex: 1; text-align: center; margin: 0;">Products</h2>
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
                <li><a href="products.php" class="active">Products</a></li>
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="reports.php">Reports</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="page-container">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                ✓ <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($action === 'add' || $action === 'edit'): ?>
            <!-- Product Form -->
            <div class="form-container">
                <h2 class="page-title" style="margin-bottom: 1.5rem;">
                    <?php echo $action === 'add' ? 'Add New Product' : 'Edit Product'; ?>
                </h2>

                <form method="POST" class="product-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="name">Product Name *</label>
                            <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="category_id">Category *</label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($product && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="price">Selling Price *</label>
                            <input type="number" id="price" name="price" class="form-control" step="0.01" value="<?php echo htmlspecialchars($product['price'] ?? ''); ?>" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="cost">Cost Price</label>
                            <input type="number" id="cost" name="cost" class="form-control" step="0.01" value="<?php echo htmlspecialchars($product['cost'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="quantity">Quantity</label>
                            <input type="number" id="quantity" name="quantity" class="form-control" value="<?php echo htmlspecialchars($product['quantity'] ?? '0'); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="sku">SKU</label>
                            <input type="text" id="sku" name="sku" class="form-control" value="<?php echo htmlspecialchars($product['sku'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group form-grid full">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group form-grid full">
                        <label class="form-label" for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="available" <?php echo (!$product || $product['status'] === 'available') ? 'selected' : ''; ?>>Available</option>
                            <option value="unavailable" <?php echo ($product && $product['status'] === 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <a href="products.php" class="btn btn-outline" style="flex: 1;">Cancel</a>
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            <?php echo $action === 'add' ? 'Add Product' : 'Update Product'; ?>
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Products List -->
            <div class="page-header">
                <h1 class="page-title">Products Management</h1>
                <a href="products.php?action=add" class="btn btn-primary">➕ Add New Product</a>
            </div>

            <?php if (!empty($products)): ?>
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Cost</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $prod): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($prod['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($prod['category_name'] ?? 'N/A'); ?></td>
                                <td>$<?php echo number_format($prod['price'], 2); ?></td>
                                <td>$<?php echo number_format($prod['cost'], 2); ?></td>
                                <td><?php echo $prod['quantity']; ?></td>
                                <td>
                                    <span class="badge <?php echo $prod['status'] === 'available' ? 'badge-cash' : 'badge-card'; ?>">
                                        <?php echo ucfirst($prod['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="products.php?action=edit&id=<?php echo $prod['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
                                        <a href="products.php?delete=<?php echo $prod['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?');">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">No products found. <a href="products.php?action=add">Add your first product</a></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
