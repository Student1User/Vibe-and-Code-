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
    if (!isset($_POST['name']) || !isset($_POST['type']) || !isset($_POST['icon'])) {
        throw new Exception('Missing required fields');
    }
    
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $icon = $_POST['icon'];
    
    // Validate data
    if (empty($name)) {
        throw new Exception('Category name is required');
    }
    
    if (!in_array($type, ['income', 'expense'])) {
        throw new Exception('Invalid category type');
    }
    
    if (empty($icon)) {
        throw new Exception('Icon is required');
    }
    
    // Check if category already exists for this user
    $check_query = "SELECT id FROM categories WHERE name = ? AND type = ? AND (user_id = ? OR user_id IS NULL)";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([$name, $type, $user_id]);
    
    if ($check_stmt->fetch()) {
        throw new Exception('A category with this name and type already exists');
    }
    
    // Insert new category
    $insert_query = "INSERT INTO categories (name, type, icon, user_id, created_at) VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->execute([$name, $type, $icon, $user_id]);
    
    if ($insert_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Category added successfully',
            'category_id' => $conn->lastInsertId()
        ]);
    } else {
        throw new Exception('Failed to add category');
    }
    
} catch (Exception $e) {
    error_log("Add category error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
