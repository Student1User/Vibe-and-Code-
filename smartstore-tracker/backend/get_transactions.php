<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $pdo = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    // Build query with optional filters
    $where_conditions = ['t.user_id = ?'];
    $params = [$user_id];
    
    if (!empty($_GET['date'])) {
        $where_conditions[] = 'DATE(t.date) = ?';
        $params[] = $_GET['date'];
    }
    
    if (!empty($_GET['category'])) {
        $where_conditions[] = 'c.name = ?';
        $params[] = $_GET['category'];
    }
    
    if (!empty($_GET['type'])) {
        $where_conditions[] = 't.type = ?';
        $params[] = $_GET['type'];
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Get transactions with category names
    $stmt = $pdo->prepare("
        SELECT 
            t.id, 
            t.type, 
            c.name as category, 
            t.amount, 
            t.description, 
            t.receipt_path as receipt_image, 
            t.date as transaction_date, 
            t.created_at
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE $where_clause
        ORDER BY t.date DESC, t.created_at DESC
        LIMIT 1000
    ");
    
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get category breakdown for charts
    $stmt = $pdo->prepare("
        SELECT 
            c.name as category, 
            t.type, 
            SUM(t.amount) as total
        FROM transactions t
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = ?
        GROUP BY c.name, t.type
        ORDER BY total DESC
    ");
    $stmt->execute([$user_id]);
    $category_breakdown = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get monthly data for charts
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(t.date, '%Y-%m') as month,
            t.type,
            SUM(t.amount) as total
        FROM transactions t
        WHERE t.user_id = ? AND t.date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY month, t.type
        ORDER BY month
    ");
    $stmt->execute([$user_id]);
    $monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'transactions' => $transactions,
        'category_breakdown' => $category_breakdown,
        'monthly_data' => $monthly_data
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
