<?php
require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_cashier();

// Get categories for sidebar
try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id AND p.status = 'available'
        GROUP BY c.id
        ORDER BY c.display_order ASC
    ");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier - Green Grounds Coffee POS</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .cashier-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 1.5rem;
            padding: 1.5rem;
            min-height: calc(100vh - 80px);
            background-color: var(--bg-primary);
        }

        .product-section {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .category-tabs {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }

        .category-tab {
            padding: 0.75rem 1.5rem;
            background-color: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            white-space: nowrap;
            transition: all 0.2s;
        }

        .category-tab:hover {
            border-color: var(--primary);
        }

        .category-tab.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }

        .product-card {
            background-color: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s;
        }

        .product-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .product-image {
            width: 100%;
            height: 120px;
            object-fit: cover;
            background-color: var(--bg-primary);
        }

        .product-details {
            padding: 0.75rem;
        }

        .product-name {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
            color: var(--text-primary);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .product-price {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary);
        }

        .cart-sidebar {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 100px;
            height: calc(100vh - 180px);
        }

        .cart-header {
            padding: 1rem;
            border-bottom: 2px solid var(--border-color);
            background-color: var(--primary);
            color: white;
            border-radius: 8px 8px 0 0;
        }

        .cart-content {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
        }

        .cart-empty {
            text-align: center;
            color: var(--text-secondary);
            padding: 2rem 1rem;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            background-color: var(--bg-primary);
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .cart-item-name {
            flex: 1;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.5rem 0;
        }

        .qty-btn {
            background-color: white;
            border: 1px solid var(--border-color);
            width: 24px;
            height: 24px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qty-display {
            width: 30px;
            text-align: center;
            font-weight: 600;
        }

        .cart-item-price {
            font-weight: 600;
            color: var(--primary);
            min-width: 60px;
            text-align: right;
        }

        .cart-footer {
            border-top: 2px solid var(--border-color);
            padding: 1rem;
            background-color: var(--bg-primary);
            border-radius: 0 0 8px 8px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .summary-total {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
            border-top: 1px solid var(--border-color);
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }

        .checkout-btn {
            width: 100%;
            margin-top: 1rem;
        }

        @media (max-width: 1024px) {
            .cashier-container {
                grid-template-columns: 1fr;
            }

            .cart-sidebar {
                position: static;
                height: auto;
            }

            .products-container {
                grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">☕ Green Grounds</a>
                <h2 style="flex: 1; text-align: center; margin: 0;">Cashier Terminal</h2>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                        <div class="user-role">Cashier</div>
                    </div>
                    <a href="../api/logout.php" class="btn btn-outline btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="cashier-container">
        <div class="product-section">
            <!-- Category Tabs -->
            <div class="category-tabs" id="categoryTabs">
                <button class="category-tab active" data-category-id="0">All Products</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="category-tab" data-category-id="<?php echo $cat['id']; ?>">
                        <?php echo htmlspecialchars($cat['name']); ?> (<?php echo $cat['product_count']; ?>)
                    </button>
                <?php endforeach; ?>
            </div>

            <!-- Products Grid -->
            <div class="products-container" id="productsContainer">
                <!-- Products will be loaded here by JavaScript -->
            </div>
        </div>

        <!-- Shopping Cart -->
        <div class="cart-sidebar">
            <div class="cart-header">
                <h3 style="margin: 0;">Shopping Cart</h3>
            </div>
            <div class="cart-content" id="cartContent">
                <div class="cart-empty">No items in cart</div>
            </div>
            <div class="cart-footer">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotalAmount">$0.00</span>
                </div>
                <div class="summary-row">
                    <span>Tax (10%):</span>
                    <span id="taxAmount">$0.00</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span id="totalAmount">$0.00</span>
                </div>
                <button class="btn btn-primary checkout-btn" id="checkoutBtn" disabled>
                    Proceed to Checkout
                </button>
                <button class="btn btn-outline checkout-btn" id="clearCartBtn">
                    Clear Cart
                </button>
            </div>
        </div>
    </div>

    <script src="../js/cashier.js"></script>
</body>
</html>
