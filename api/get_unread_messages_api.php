<?php
require_once '../admin/sass/db_config.php'; // ✅ Adjust path if needed

header('Content-Type: application/json; charset=UTF-8');

// ✅ Get student_id from POST or JSON
$data = json_decode(file_get_contents("php://input"), true);
$student_id = intval($data['student_id'] ?? $_POST['student_id'] ?? 0);

if ($student_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid student_id']);
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

$stmt->close();
$conn->close();
?>