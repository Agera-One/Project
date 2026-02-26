<?php
require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_cashier();

$errors = [];
$receipt_number = '';
$order_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid request token. Please try again.';
    } else {
        try {
            // Get cart data from POST
            $cart_items = isset($_POST['cart_items']) ? json_decode($_POST['cart_items'], true) : [];
            $subtotal = isset($_POST['subtotal']) ? (float)$_POST['subtotal'] : 0;
            $tax = isset($_POST['tax']) ? (float)$_POST['tax'] : 0;
            $total = isset($_POST['total']) ? (float)$_POST['total'] : 0;
            $payment_method = sanitize_input($_POST['payment_method'] ?? 'cash');
            $customer_name = sanitize_input($_POST['customer_name'] ?? 'Walk-in Customer');
            $table_number = sanitize_input($_POST['table_number'] ?? '');
            $order_type = sanitize_input($_POST['order_type'] ?? 'dine-in');
            $notes = sanitize_input($_POST['notes'] ?? '');

            if (empty($cart_items)) {
                throw new Exception('Cart is empty');
            }

            // Validate payment method
            if (!in_array($payment_method, ['cash', 'card', 'digital'])) {
                throw new Exception('Invalid payment method');
            }

            // Start transaction
            $pdo->beginTransaction();

            // Generate receipt number
            $receipt_number = generate_receipt_number();

            // Create order
            $stmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, receipt_number, subtotal, tax, total,
                    payment_method, status, customer_name, table_number,
                    order_type, notes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $receipt_number,
                $subtotal,
                $tax,
                $total,
                $payment_method,
                'completed',
                $customer_name,
                $table_number,
                $order_type,
                $notes
            ]);

            $order_id = $pdo->lastInsertId();

            // Add order items
            $item_stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price, total)
                VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($cart_items as $item) {
                $product_id = (int)$item['id'];
                $quantity = (int)$item['quantity'];
                $unit_price = (float)$item['price'];
                $item_total = $unit_price * $quantity;

                // Insert order item
                $item_stmt->execute([
                    $order_id,
                    $product_id,
                    $quantity,
                    $unit_price,
                    $item_total
                ]);

                // Update product quantity
                $update_stmt = $pdo->prepare("
                    UPDATE products SET quantity = quantity - ? WHERE id = ?
                ");
                $update_stmt->execute([$quantity, $product_id]);
            }

            // Create transaction record
            $trans_stmt = $pdo->prepare("
                INSERT INTO transactions (order_id, user_id, amount, payment_method, status, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $trans_stmt->execute([
                $order_id,
                $_SESSION['user_id'],
                $total,
                $payment_method,
                'completed'
            ]);

            // Log activity
            log_activity($pdo, $_SESSION['user_id'], 'create_order', "Order created: {$receipt_number}");

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Error processing order: ' . $e->getMessage();
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Green Grounds Coffee POS</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .checkout-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }

        .checkout-form {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 2rem;
            box-shadow: var(--shadow);
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid var(--border-color);
        }

        .form-section:last-of-type {
            border-bottom: none;
        }

        .form-section h3 {
            margin-bottom: 1rem;
            color: var(--primary);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-row.full {
            grid-template-columns: 1fr;
        }

        .order-summary {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow);
        }

        .summary-header {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
            color: var(--primary);
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.9rem;
        }

        .order-item-name {
            flex: 1;
        }

        .order-item-qty {
            width: 50px;
            text-align: center;
        }

        .order-item-total {
            width: 70px;
            text-align: right;
            font-weight: 600;
        }

        .summary-section {
            margin-top: 1rem;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.95rem;
        }

        .summary-total {
            font-size: 1.25rem;
            font-weight: 700;
            border-top: 2px solid var(--border-color);
            padding-top: 0.75rem;
            margin-top: 0.75rem;
            color: var(--primary);
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .payment-option {
            position: relative;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-label {
            display: block;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }

        .payment-option input[type="radio"]:checked + .payment-label {
            border-color: var(--primary);
            background-color: var(--primary);
            color: white;
        }

        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .payment-methods {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: static;
            }
        }

        .receipt-success {
            background-color: var(--bg-secondary);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow);
        }

        .receipt-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 1rem 0;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .button-group .btn {
            flex: 1;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">☕ Green Grounds</a>
                <h2 style="flex: 1; text-align: center; margin: 0;">Checkout</h2>
                <div class="user-menu">
                    <a href="../api/logout.php" class="btn btn-outline btn-sm">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="checkout-container">
        <?php if ($order_id && !empty($errors) === false): ?>
            <!-- Success Message -->
            <div class="receipt-success">
                <h2 style="color: var(--primary); margin-bottom: 1rem;">✓ Order Completed!</h2>
                <p style="font-size: 1.1rem; margin-bottom: 1rem;">Your order has been successfully processed.</p>
                <div class="receipt-number"><?php echo htmlspecialchars($receipt_number); ?></div>
                <div class="order-summary" style="margin-top: 2rem; box-shadow: none; border: 2px solid var(--primary);">
                    <div style="text-align: left; padding: 0;">
                        <p><strong>Total Amount:</strong> <span style="float: right; color: var(--primary); font-size: 1.25rem; font-weight: 700;">$<?php echo number_format(isset($_POST['total']) ? (float)$_POST['total'] : 0, 2); ?></span></p>
                        <p><strong>Payment Method:</strong> <span style="float: right; text-transform: capitalize;"><?php echo htmlspecialchars($_POST['payment_method'] ?? 'cash'); ?></span></p>
                        <p style="margin-bottom: 0;"><strong>Time:</strong> <span style="float: right;"><?php echo date('M d, Y h:i A'); ?></span></p>
                    </div>
                </div>
                <div class="button-group">
                    <a href="index.php" class="btn btn-primary btn-lg">New Order</a>
                    <a href="receipt.php?order_id=<?php echo $order_id; ?>" class="btn btn-secondary btn-lg">Print Receipt</a>
                </div>
            </div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="margin-bottom: 2rem;">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
                <a href="index.php" class="btn btn-primary">Back to POS</a>
            <?php else: ?>
                <div class="checkout-grid">
                    <!-- Checkout Form -->
                    <form method="POST" class="checkout-form" id="checkoutForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" id="cartItems" name="cart_items" value="">
                        <input type="hidden" id="subtotal" name="subtotal" value="">
                        <input type="hidden" id="tax" name="tax" value="">
                        <input type="hidden" id="total" name="total" value="">

                        <!-- Customer Information -->
                        <div class="form-section">
                            <h3>Customer Information</h3>
                            <div class="form-group">
                                <label class="form-label" for="customer_name">Customer Name</label>
                                <input type="text" id="customer_name" name="customer_name" class="form-control" value="Walk-in Customer" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="order_type">Order Type</label>
                                    <select id="order_type" name="order_type" class="form-control" required>
                                        <option value="dine-in">Dine In</option>
                                        <option value="takeaway">Takeaway</option>
                                        <option value="delivery">Delivery</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="table_number">Table Number</label>
                                    <input type="text" id="table_number" name="table_number" class="form-control" placeholder="e.g., T-01">
                                </div>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-section">
                            <h3>Payment Method</h3>
                            <div class="payment-methods">
                                <div class="payment-option">
                                    <input type="radio" id="cash" name="payment_method" value="cash" checked required>
                                    <label for="cash" class="payment-label">💵 Cash</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="card" name="payment_method" value="card" required>
                                    <label for="card" class="payment-label">💳 Card</label>
                                </div>
                                <div class="payment-option">
                                    <input type="radio" id="digital" name="payment_method" value="digital" required>
                                    <label for="digital" class="payment-label">📱 Digital</label>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="form-section">
                            <h3>Order Notes</h3>
                            <div class="form-group">
                                <label class="form-label" for="notes">Special Instructions</label>
                                <textarea id="notes" name="notes" class="form-control" placeholder="e.g., Less sugar, extra shots..."></textarea>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div style="display: flex; gap: 1rem;">
                            <a href="index.php" class="btn btn-outline" style="flex: 1;">Back to POS</a>
                            <button type="submit" class="btn btn-primary" style="flex: 1;">Complete Order</button>
                        </div>
                    </form>

                    <!-- Order Summary Sidebar -->
                    <div class="order-summary" id="orderSummary">
                        <div class="summary-header">Order Summary</div>
                        <div id="orderItems"></div>
                        <div class="summary-section">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="summarySubtotal">$0.00</span>
                            </div>
                            <div class="summary-row">
                                <span>Tax (10%):</span>
                                <span id="summaryTax">$0.00</span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Total:</span>
                                <span id="summaryTotal">$0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script>
        // Load cart from session storage
        function initCheckout() {
            const cartItems = JSON.parse(sessionStorage.getItem('cartItems') || '[]');
            const subtotal = parseFloat(sessionStorage.getItem('cartSubtotal') || '0');
            const tax = parseFloat(sessionStorage.getItem('cartTax') || '0');
            const total = subtotal + tax;

            // Populate hidden form fields
            document.getElementById('cartItems').value = JSON.stringify(cartItems);
            document.getElementById('subtotal').value = subtotal;
            document.getElementById('tax').value = tax;
            document.getElementById('total').value = total;

            // Render order items
            const itemsContainer = document.getElementById('orderItems');
            itemsContainer.innerHTML = cartItems.map(item => `
                <div class="order-item">
                    <div class="order-item-name">${escapeHtml(item.name)}</div>
                    <div class="order-item-qty">x${item.quantity}</div>
                    <div class="order-item-total">$${(item.price * item.quantity).toFixed(2)}</div>
                </div>
            `).join('');

            // Update summary
            document.getElementById('summarySubtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('summaryTax').textContent = `$${tax.toFixed(2)}`;
            document.getElementById('summaryTotal').textContent = `$${total.toFixed(2)}`;

            // Clear session storage
            sessionStorage.removeItem('cartItems');
            sessionStorage.removeItem('cartSubtotal');
            sessionStorage.removeItem('cartTax');
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        document.addEventListener('DOMContentLoaded', initCheckout);
    </script>
</body>
</html>
