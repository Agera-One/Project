<?php
require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_cashier();

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT c.*, COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON p.category_id = c.id AND p.status = 'available'
        GROUP BY c.id
        ORDER BY c.display_order ASC, c.name ASC
    ");

    $categories = $stmt->fetchAll();
    json_response(['success' => true, 'categories' => $categories]);
} catch (PDOException $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 500);
}
