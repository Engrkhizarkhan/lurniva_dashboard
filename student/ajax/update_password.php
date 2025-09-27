<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$student_id = $_SESSION['student_id'];

// Validate input
$current_password = $_POST['current_password'] ?? '';
$new_password     = $_POST['new_password'] ?? '';

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}

// Fetch current password hash from DB
$stmt = $conn->prepare("SELECT password FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    exit;
}

$row = $result->fetch_assoc();
$current_hash = $row['password'];

// Verify current password
if (!password_verify($current_password, $current_hash)) {
    echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
    exit;
}

// Hash new password
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update password in DB
$updateStmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
$updateStmt->bind_param("si", $new_hash, $student_id);

if ($updateStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
}
