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
    if (!isset($_POST['category_id']) || !isset($_POST['name']) || !isset($_POST['type']) || !isset($_POST['icon'])) {
        throw new Exception('Missing required fields');
    }
    
    $category_id = (int)$_POST['category_id'];
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
    
    // Check if category exists and belongs to user
    $check_query = "SELECT id, user_id FROM categories WHERE id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([$category_id]);
    $category = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$category) {
        throw new Exception('Category not found');
    }
    
    // Only allow editing user-created categories
    if ($category['user_id'] !== $user_id) {
        throw new Exception('You can only edit categories you created');
    }
    
    // Check if another category with same name and type exists
    $duplicate_query = "SELECT id FROM categories WHERE name = ? AND type = ? AND id != ? AND (user_id = ? OR user_id IS NULL)";
    $duplicate_stmt = $conn->prepare($duplicate_query);
    $duplicate_stmt->execute([$name, $type, $category_id, $user_id]);
    
    if ($duplicate_stmt->fetch()) {
        throw new Exception('A category with this name and type already exists');
    }
    
    // Update category
    $update_query = "UPDATE categories SET name = ?, type = ?, icon = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->execute([$name, $type, $icon, $category_id, $user_id]);
    
    if ($update_stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true, 
            'message' => 'Category updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update category or no changes made');
    }
    
} catch (Exception $e) {
    error_log("Update category error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
