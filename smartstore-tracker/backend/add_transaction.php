<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff'); // Extra security header

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];

    // Get form data and sanitize
    $type = trim($_POST['type'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $date = trim($_POST['date'] ?? '');
    $description = htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');

    // Validate required fields
    if (empty($type) || $category_id <= 0 || $amount <= 0 || empty($date)) {
        throw new Exception('All required fields must be filled');
    }

    if (!in_array($type, ['income', 'expense'])) {
        throw new Exception('Invalid transaction type');
    }

    if (!DateTime::createFromFormat('Y-m-d', $date)) {
        throw new Exception('Invalid date format. Expected Y-m-d');
    }

    // Handle file upload
    $receipt_path = null;
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/receipts/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['receipt']['tmp_name']);
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Invalid file type. Allowed types: JPG, PNG, PDF');
        }

        // Check file size (max 10MB)
        if ($_FILES['receipt']['size'] > 10 * 1024 * 1024) {
            throw new Exception('File size too large. Maximum size is 10MB.');
        }

        $file_extension = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;

        if (!move_uploaded_file($_FILES['receipt']['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload receipt file');
        }

        $receipt_path = 'uploads/receipts/' . $filename;
    }

    // Insert transaction into the database
    $stmt = $conn->prepare("
        INSERT INTO transactions (user_id, type, category_id, amount, date, description, receipt_path, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $user_id,
        $type,
        $category_id,
        $amount,
        $date,
        $description,
        $receipt_path
    ]);

    $transaction_id = $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Transaction added successfully',
        'transaction_id' => $transaction_id
    ]);

} catch (Exception $e) {
    // Clean up uploaded file if an error occurred
    if (isset($upload_path) && file_exists($upload_path)) {
        unlink($upload_path);
    }

    error_log("Transaction error: " . $e->getMessage()); // Log error for server-side debugging

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
