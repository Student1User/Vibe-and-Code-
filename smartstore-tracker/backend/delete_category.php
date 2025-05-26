<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

require_once 'db_config.php';

try {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    // Validate input
    if (!isset($_POST['category_id'])) {
        throw new Exception('Category ID is required');
    }
    
    $category_id = (int)$_POST['category_id'];
    
    // Check if category exists and belongs to user
    $check_query = "SELECT id, user_id, name FROM categories WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([$category_id]);
    $category = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        throw new Exception('Category not found');
    }
    
    // Only allow deleting user-created categories
    if ($category['user_id'] !== $user_id) {
        throw new Exception('You can only delete categories you created');
    }
    
    // Check if category is being used in transactions
    $usage_query = "SELECT COUNT(*) as count FROM transactions WHERE category_id = ? AND user_id = ?";
    $usage_stmt = $conn->prepare($usage_query);
    $usage_stmt->execute([$category_id, $user_id]);
    $usage = $usage_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usage['count'] > 0) {
        throw new Exception('Cannot delete category that is being used in transactions. Please reassign or delete those transactions first.');
    }
    
    // Delete category
    $delete_query = "DELETE FROM categories WHERE id = ? AND user_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->execute([$category_id, $user_id]);
    
    if ($delete_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Category deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete category');
    }
    
} catch (Exception $e) {
    error_log("Delete category error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
