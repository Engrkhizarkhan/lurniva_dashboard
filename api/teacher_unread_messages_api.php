<?php
require_once '../admin/sass/db_config.php';

// ✅ JSON & CORS headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// ✅ Handle OPTIONS preflight (for Flutter/Postman)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Get teacher_id from POST
$teacher_id = intval($_POST['teacher_id'] ?? 0);

if (!$teacher_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Teacher ID is required'
    ]);
    exit;
}

// ✅ Query unread message count for teacher
$stmt = $conn->prepare("
    SELECT COUNT(*) AS unread_count
    FROM messages
    WHERE receiver_designation = 'teacher'
      AND receiver_id = ?
      AND status = 'unread'
");
$stmt->bind_param('i', $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$unreadCount = (int)($row['unread_count'] ?? 0);

// ✅ Return JSON response
echo json_encode([
    'status' => 'success',
    'unread_count' => $unreadCount
]);

$stmt->close();
$conn->close();
?>