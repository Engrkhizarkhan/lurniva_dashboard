<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Validate input
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// Fetch current password hash from DB
$stmt = $conn->prepare("SELECT password FROM schools WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'School not found']);
    exit;
}

$row = $result->fetch_assoc();
$current_hash = $row['password'];

// Verify current password (assuming passwords are hashed using password_hash)
if (!password_verify($current_password, $current_hash)) {
    echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
    exit;
}

// Hash new password
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update password in DB
$updateStmt = $conn->prepare("UPDATE schools SET password = ? WHERE id = ?");
$updateStmt->bind_param("si", $new_hash, $admin_id);

if ($updateStmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
}