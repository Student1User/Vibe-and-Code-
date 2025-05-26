<?php
require_once 'backend/db_config.php';
requireLogin();

try {
    $pdo = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    // Get all transactions for the user
    $stmt = $pdo->prepare("
        SELECT type, category, amount, description, transaction_date, created_at
        FROM transactions 
        WHERE user_id = ?
        ORDER BY transaction_date DESC, created_at DESC
    ");
    $stmt->execute([$user_id]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Set headers for CSV download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Create file pointer
    $output = fopen('php://output', 'w');
    
    // Add CSV headers
    fputcsv($output, ['Date', 'Type', 'Category', 'Amount', 'Description', 'Created At']);
    
    // Add transaction data
    foreach ($transactions as $transaction) {
        fputcsv($output, [
            $transaction['transaction_date'],
            ucfirst($transaction['type']),
            $transaction['category'],
            '$' . number_format($transaction['amount'], 2),
            $transaction['description'],
            $transaction['created_at']
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error: ' . $e->getMessage();
}
?>