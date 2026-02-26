<?php
// Seed script to populate database with sample products and users
// Run this after setup_database.php to add demo data

require_once __DIR__ . '/../config/database.php';

$products = [
    // Coffee
    ['category_id' => 1, 'name' => 'Espresso', 'sku' => 'COFFEE-001', 'price' => 4.2, 'cost' => 1.5, 'quantity' => 50],
    ['category_id' => 1, 'name' => 'Americano', 'sku' => 'COFFEE-002', 'price' => 4.0, 'cost' => 1.3, 'quantity' => 45],
    ['category_id' => 1, 'name' => 'Cappuccino', 'sku' => 'COFFEE-003', 'price' => 5.2, 'cost' => 2.0, 'quantity' => 40],
    ['category_id' => 1, 'name' => 'Latte', 'sku' => 'COFFEE-004', 'price' => 5.0, 'cost' => 1.9, 'quantity' => 38],
    ['category_id' => 1, 'name' => 'Mocha', 'sku' => 'COFFEE-005', 'price' => 5.5, 'cost' => 2.2, 'quantity' => 35],
    ['category_id' => 1, 'name' => 'Iced Coffee Milk', 'sku' => 'COFFEE-006', 'price' => 5.3, 'cost' => 1.8, 'quantity' => 30],
    ['category_id' => 1, 'name' => 'Cold Brew', 'sku' => 'COFFEE-007', 'price' => 4.8, 'cost' => 1.6, 'quantity' => 25],
    ['category_id' => 1, 'name' => 'Caramel Mac', 'sku' => 'COFFEE-008', 'price' => 5.8, 'cost' => 2.1, 'quantity' => 28],
    ['category_id' => 1, 'name' => 'Salted Caramel', 'sku' => 'COFFEE-009', 'price' => 5.4, 'cost' => 2.0, 'quantity' => 32],
    ['category_id' => 1, 'name' => 'Hazelnut Latte', 'sku' => 'COFFEE-010', 'price' => 5.2, 'cost' => 1.9, 'quantity' => 29],
    ['category_id' => 1, 'name' => 'Flat White', 'sku' => 'COFFEE-011', 'price' => 5.1, 'cost' => 1.8, 'quantity' => 27],
    ['category_id' => 1, 'name' => 'Pour Over', 'sku' => 'COFFEE-012', 'price' => 4.5, 'cost' => 1.4, 'quantity' => 22],

    // Tea
    ['category_id' => 2, 'name' => 'Green Jasmine Tea', 'sku' => 'TEA-001', 'price' => 4.0, 'cost' => 1.2, 'quantity' => 50],
    ['category_id' => 2, 'name' => 'Earl Grey', 'sku' => 'TEA-002', 'price' => 4.5, 'cost' => 1.3, 'quantity' => 45],
    ['category_id' => 2, 'name' => 'Chamomile', 'sku' => 'TEA-003', 'price' => 3.8, 'cost' => 1.1, 'quantity' => 40],
    ['category_id' => 2, 'name' => 'Peppermint Tea', 'sku' => 'TEA-004', 'price' => 4.0, 'cost' => 1.2, 'quantity' => 38],
    ['category_id' => 2, 'name' => 'Hibiscus Berry Tea', 'sku' => 'TEA-005', 'price' => 4.2, 'cost' => 1.25, 'quantity' => 35],
    ['category_id' => 2, 'name' => 'Darjeeling', 'sku' => 'TEA-006', 'price' => 4.0, 'cost' => 1.2, 'quantity' => 32],
    ['category_id' => 2, 'name' => 'Genmaicha', 'sku' => 'TEA-007', 'price' => 3.8, 'cost' => 1.1, 'quantity' => 28],
    ['category_id' => 2, 'name' => 'Sencha', 'sku' => 'TEA-008', 'price' => 4.0, 'cost' => 1.2, 'quantity' => 30],
    ['category_id' => 2, 'name' => 'White Peony', 'sku' => 'TEA-009', 'price' => 4.2, 'cost' => 1.25, 'quantity' => 26],
    ['category_id' => 2, 'name' => 'Lemon Ginger', 'sku' => 'TEA-010', 'price' => 3.5, 'cost' => 1.0, 'quantity' => 24],
    ['category_id' => 2, 'name' => 'Moroccan Mint', 'sku' => 'TEA-011', 'price' => 4.0, 'cost' => 1.2, 'quantity' => 28],
    ['category_id' => 2, 'name' => 'Lapsang Souchong', 'sku' => 'TEA-012', 'price' => 4.5, 'cost' => 1.35, 'quantity' => 20],
    ['category_id' => 2, 'name' => 'Dragon Well', 'sku' => 'TEA-013', 'price' => 5.0, 'cost' => 1.5, 'quantity' => 18],
    ['category_id' => 2, 'name' => 'Lemongrass Tea', 'sku' => 'TEA-014', 'price' => 3.5, 'cost' => 1.0, 'quantity' => 22],
    ['category_id' => 2, 'name' => 'Rooibos Chai', 'sku' => 'TEA-015', 'price' => 4.0, 'cost' => 1.2, 'quantity' => 25],

    // Snacks
    ['category_id' => 3, 'name' => 'Avocado Toast', 'sku' => 'SNACK-001', 'price' => 4.0, 'cost' => 1.5, 'quantity' => 20],
    ['category_id' => 3, 'name' => 'Quinoa Salad', 'sku' => 'SNACK-002', 'price' => 5.5, 'cost' => 2.2, 'quantity' => 18],
    ['category_id' => 3, 'name' => 'Hummus Plate', 'sku' => 'SNACK-003', 'price' => 3.5, 'cost' => 1.3, 'quantity' => 22],
    ['category_id' => 3, 'name' => 'Acai Bowl', 'sku' => 'SNACK-004', 'price' => 5.3, 'cost' => 2.0, 'quantity' => 15],
    ['category_id' => 3, 'name' => 'Vegan Energy', 'sku' => 'SNACK-005', 'price' => 5.0, 'cost' => 1.9, 'quantity' => 17],
    ['category_id' => 3, 'name' => 'Spinach Feta Pastry', 'sku' => 'SNACK-006', 'price' => 4.0, 'cost' => 1.5, 'quantity' => 19],
    ['category_id' => 3, 'name' => 'Banana Bread', 'sku' => 'SNACK-007', 'price' => 4.5, 'cost' => 1.6, 'quantity' => 20],
    ['category_id' => 3, 'name' => 'Quiche Lorraine', 'sku' => 'SNACK-008', 'price' => 3.8, 'cost' => 1.4, 'quantity' => 16],
    ['category_id' => 3, 'name' => 'Coconut Macaroons', 'sku' => 'SNACK-009', 'price' => 4.0, 'cost' => 1.5, 'quantity' => 25],
    ['category_id' => 3, 'name' => 'Greek Yogurt Parfait', 'sku' => 'SNACK-010', 'price' => 4.2, 'cost' => 1.6, 'quantity' => 14],
    ['category_id' => 3, 'name' => 'Cheese Scones', 'sku' => 'SNACK-011', 'price' => 4.0, 'cost' => 1.5, 'quantity' => 18],
    ['category_id' => 3, 'name' => 'Sweet Potato Fries', 'sku' => 'SNACK-012', 'price' => 3.8, 'cost' => 1.4, 'quantity' => 17],
    ['category_id' => 3, 'name' => 'Almond Butter Toast', 'sku' => 'SNACK-013', 'price' => 4.0, 'cost' => 1.5, 'quantity' => 19],
    ['category_id' => 3, 'name' => 'Veggie Plate', 'sku' => 'SNACK-014', 'price' => 3.8, 'cost' => 1.4, 'quantity' => 16],
    ['category_id' => 3, 'name' => 'Blueberry Muffin', 'sku' => 'SNACK-015', 'price' => 4.0, 'cost' => 1.5, 'quantity' => 22],
];

// Add demo cashier user
$users = [
    ['name' => 'Sarah Johnson', 'email' => 'sarah@greengrounds.local', 'password' => password_hash('cashier123', PASSWORD_BCRYPT), 'role' => 'cashier', 'status' => 'active'],
    ['name' => 'Mike Chen', 'email' => 'mike@greengrounds.local', 'password' => password_hash('cashier123', PASSWORD_BCRYPT), 'role' => 'cashier', 'status' => 'active'],
    ['name' => 'Emma Davis', 'email' => 'emma@greengrounds.local', 'password' => password_hash('manager123', PASSWORD_BCRYPT), 'role' => 'manager', 'status' => 'active'],
];

try {
    // Insert products
    $stmt = $pdo->prepare("
        INSERT INTO products (category_id, name, sku, price, cost, quantity, status)
        VALUES (?, ?, ?, ?, ?, ?, 'available')
    ");

    foreach ($products as $product) {
        $stmt->execute([
            $product['category_id'],
            $product['name'],
            $product['sku'],
            $product['price'],
            $product['cost'],
            $product['quantity']
        ]);
    }

    echo "✓ " . count($products) . " products inserted\n";

    // Insert users
    $user_stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role, status)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($users as $user) {
        try {
            $user_stmt->execute([
                $user['name'],
                $user['email'],
                $user['password'],
                $user['role'],
                $user['status']
            ]);
        } catch (PDOException $e) {
            // Skip if user already exists
        }
    }

    echo "✓ Demo users created\n";
    echo "\n✓ Seed data completed successfully!\n";

} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
