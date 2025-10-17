<?php
session_start();
require_once '../admin/sass/db_config.php'; // ✅ Adjust path if needed

header('Content-Type: application/json; charset=UTF-8');

// ✅ Check session
$student_id = intval($_SESSION['student_id'] ?? 0);

if ($student_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// ✅ Get unread message count
$stmt = $conn->prepare("
    SELECT COUNT(*) AS unread_count 
    FROM messages 
    WHERE receiver_designation = 'student' 
      AND receiver_id = ? 
      AND status = 'unread'
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$unread_count = intval($row['unread_count'] ?? 0);

// ✅ Respond in JSON
echo json_encode([
    'status' => 'success',
    'unread_count' => $unread_count
]);