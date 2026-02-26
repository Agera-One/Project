<?php
// Database Setup Script
// This script creates all necessary tables for the POS system

require_once __DIR__ . '/../config/database.php';

$sql_statements = [
    // Users Table
    "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'cashier', 'manager') DEFAULT 'cashier',
        status ENUM('active', 'inactive') DEFAULT 'active',
        phone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL
    )",

    // Categories Table
    "CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        icon VARCHAR(50),
        display_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    // Products Table
    "CREATE TABLE IF NOT EXISTS products (
        id INT PRIMARY KEY AUTO_INCREMENT,
        category_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        cost DECIMAL(10, 2),
        quantity INT DEFAULT 0,
        sku VARCHAR(100) UNIQUE,
        image_url VARCHAR(255),
        status ENUM('available', 'unavailable') DEFAULT 'available',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
        INDEX idx_category (category_id),
        INDEX idx_status (status)
    )",

    // Orders Table
    "CREATE TABLE IF NOT EXISTS orders (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        receipt_number VARCHAR(50) UNIQUE NOT NULL,
        subtotal DECIMAL(10, 2) NOT NULL,
        tax DECIMAL(10, 2) DEFAULT 0,
        discount DECIMAL(10, 2) DEFAULT 0,
        total DECIMAL(10, 2) NOT NULL,
        payment_method ENUM('cash', 'card', 'digital') DEFAULT 'cash',
        status ENUM('completed', 'cancelled') DEFAULT 'completed',
        customer_name VARCHAR(255),
        table_number VARCHAR(10),
        order_type ENUM('dine-in', 'takeaway', 'delivery') DEFAULT 'dine-in',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        INDEX idx_user (user_id),
        INDEX idx_status (status),
        INDEX idx_receipt (receipt_number),
        INDEX idx_date (created_at)
    )",

    // Order Items Table
    "CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10, 2) NOT NULL,
        total DECIMAL(10, 2) NOT NULL,
        special_instructions TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id),
        INDEX idx_order (order_id),
        INDEX idx_product (product_id)
    )",

    // Transactions Table
    "CREATE TABLE IF NOT EXISTS transactions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT,
        user_id INT NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        payment_method ENUM('cash', 'card', 'digital') DEFAULT 'cash',
        status ENUM('completed', 'pending', 'failed') DEFAULT 'completed',
        reference_number VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id),
        FOREIGN KEY (user_id) REFERENCES users(id),
        INDEX idx_user (user_id),
        INDEX idx_status (status),
        INDEX idx_date (created_at)
    )",

    // Activity Log Table
    "CREATE TABLE IF NOT EXISTS activity_log (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        action VARCHAR(255) NOT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        INDEX idx_user (user_id),
        INDEX idx_date (created_at)
    )"
];

try {
    foreach ($sql_statements as $sql) {
        $pdo->exec($sql);
        echo "✓ Table created/verified\n";
    }

    // Insert default categories if they don't exist
    $checkCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($checkCategories == 0) {
        $categories = [
            ['name' => 'Coffee', 'icon' => '☕', 'display_order' => 1],
            ['name' => 'Tea', 'icon' => '🍵', 'display_order' => 2],
            ['name' => 'Snacks', 'icon' => '🍪', 'display_order' => 3]
        ];
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, icon, display_order) VALUES (?, ?, ?)");
        foreach ($categories as $cat) {
            $stmt->execute([$cat['name'], $cat['icon'], $cat['display_order']]);
        }
        echo "✓ Default categories inserted\n";
    }

    // Insert default admin user if no users exist
    $checkUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($checkUsers == 0) {
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role, status)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            'Admin User',
            'admin@greengrounds.local',
            password_hash('admin123', PASSWORD_BCRYPT),
            'admin',
            'active'
        ]);
        echo "✓ Default admin user created (email: admin@greengrounds.local, password: admin123)\n";
    }

    echo "\n✓ Database setup completed successfully!\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
