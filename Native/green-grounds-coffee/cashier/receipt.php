<?php
require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_cashier();

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$order = null;
$order_items = [];

if ($order_id > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, u.name as cashier_name
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->execute([$order_id, $_SESSION['user_id']]);
        $order = $stmt->fetch();

        if ($order) {
            $stmt = $pdo->prepare("
                SELECT oi.*, p.name as product_name
                FROM order_items oi
                LEFT JOIN products p ON p.id = oi.product_id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        die('Error loading order: ' . $e->getMessage());
    }
}

if (!$order) {
    die('Order not found');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - <?php echo htmlspecialchars($order['receipt_number']); ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .receipt-container {
            max-width: 400px;
            margin: 2rem auto;
            background-color: white;
            padding: 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.4;
        }

        .receipt-paper {
            background-color: white;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #333;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .receipt-title {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .receipt-subtitle {
            font-size: 12px;
            color: #666;
            margin: 2px 0 0 0;
        }

        .receipt-line {
            border-top: 1px dashed #333;
            margin: 10px 0;
        }

        .receipt-info {
            font-size: 12px;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .receipt-label {
            display: inline-block;
            width: 80px;
        }

        .receipt-items {
            margin: 10px 0;
            border-top: 1px dashed #333;
            border-bottom: 1px dashed #333;
            padding: 10px 0;
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .item-name {
            flex: 1;
        }

        .item-qty {
            width: 30px;
            text-align: right;
        }

        .item-price {
            width: 50px;
            text-align: right;
        }

        .receipt-totals {
            margin: 10px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            font-size: 12px;
        }

        .total-row.grand-total {
            border-top: 1px dashed #333;
            border-bottom: 1px dashed #333;
            padding: 5px 0;
            font-weight: bold;
            font-size: 14px;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 10px;
            font-size: 11px;
            color: #666;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            .receipt-container {
                max-width: 100%;
                margin: 0;
                border: none;
            }
            .print-button {
                display: none;
            }
        }

        .print-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }

        .action-buttons a,
        .action-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }

        .action-buttons .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .action-buttons .btn-secondary {
            background-color: #f0f0f0;
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-paper">
            <!-- Header -->
            <div class="receipt-header">
                <p class="receipt-title">☕ GREEN GROUNDS COFFEE</p>
                <p class="receipt-subtitle">Point of Sale Receipt</p>
            </div>

            <!-- Receipt Number and Date -->
            <div class="receipt-info">
                <div><span class="receipt-label">Receipt:</span> <?php echo htmlspecialchars($order['receipt_number']); ?></div>
                <div><span class="receipt-label">Date:</span> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></div>
                <div><span class="receipt-label">Cashier:</span> <?php echo htmlspecialchars($order['cashier_name'] ?? 'N/A'); ?></div>
            </div>

            <div class="receipt-line"></div>

            <!-- Order Details -->
            <div class="receipt-info">
                <div><span class="receipt-label">Customer:</span> <?php echo htmlspecialchars($order['customer_name']); ?></div>
                <?php if (!empty($order['table_number'])): ?>
                    <div><span class="receipt-label">Table:</span> <?php echo htmlspecialchars($order['table_number']); ?></div>
                <?php endif; ?>
                <div><span class="receipt-label">Type:</span> <?php echo ucfirst(str_replace('-', ' ', $order['order_type'])); ?></div>
            </div>

            <div class="receipt-line"></div>

            <!-- Items -->
            <div class="receipt-items">
                <?php foreach ($order_items as $item): ?>
                    <div class="receipt-item">
                        <div class="item-name"><?php echo htmlspecialchars(substr($item['product_name'], 0, 20)); ?></div>
                        <div class="item-qty">x<?php echo $item['quantity']; ?></div>
                        <div class="item-price">$<?php echo number_format($item['total'], 2); ?></div>
                    </div>
                    <div style="font-size: 10px; color: #999; margin-bottom: 3px;">
                        <?php echo $item['quantity']; ?> × $<?php echo number_format($item['unit_price'], 2); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Totals -->
            <div class="receipt-totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>$<?php echo number_format($order['subtotal'], 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Tax (10%):</span>
                    <span>$<?php echo number_format($order['tax'], 2); ?></span>
                </div>
                <div class="total-row grand-total">
                    <span>TOTAL:</span>
                    <span>$<?php echo number_format($order['total'], 2); ?></span>
                </div>
                <div class="total-row">
                    <span>Payment:</span>
                    <span style="text-transform: capitalize;"><?php echo $order['payment_method']; ?></span>
                </div>
            </div>

            <?php if (!empty($order['notes'])): ?>
                <div class="receipt-line"></div>
                <div class="receipt-info" style="font-size: 11px;">
                    <strong>Notes:</strong><br>
                    <?php echo htmlspecialchars($order['notes']); ?>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div class="receipt-line"></div>
            <div class="receipt-footer">
                <p>Thank you for your purchase!</p>
                <p>Green Grounds Coffee © 2026</p>
            </div>
        </div>
    </div>

    <div class="action-buttons" style="margin-top: 20px;">
        <button class="print-button" onclick="window.print();">🖨️ Print Receipt</button>
    </div>

    <div class="action-buttons">
        <a href="index.php" class="btn-primary">New Order</a>
        <a href="../admin/dashboard.php" class="btn-secondary">Dashboard</a>
    </div>
</body>
</html>
