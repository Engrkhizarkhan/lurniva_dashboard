<?php
session_start();
require_once '../admin/sass/db_config.php'; // adjust path if needed

header('Content-Type: application/json; charset=UTF-8');

// ✅ Verify logged-in student
$receiver_id = intval($_SESSION['student_id'] ?? 0);
if ($receiver_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// ✅ Collect and validate inputs
$sender_id = intval($_POST['sender_id'] ?? 0);
$sender_designation = trim($_POST['sender_designation'] ?? '');

if ($sender_id <= 0 || $sender_designation === '') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
    exit;
}

// ✅ Update unread messages
$stmt = $conn->prepare("
    UPDATE messages 
    SET status = 'read' 
    WHERE sender_id = ? 
      AND sender_designation = ? 
      AND receiver_id = ? 
      AND receiver_designation = 'student' 
      AND status = 'unread'
");
$stmt->bind_param("isi", $sender_id, $sender_designation, $receiver_id);

if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    echo json_encode([
        'status' => 'success',
        'message' => "$affected message(s) marked as read",
        'count' => $affected
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}