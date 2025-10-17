<?php
session_start();
require_once '../admin/sass/db_config.php';
header('Content-Type: application/json; charset=UTF-8');

// ✅ Verify student session
if (!isset($_SESSION['student_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$studentId = intval($_SESSION['student_id']);
$notifId   = intval($_POST['notif_id'] ?? 0);

if (!$notifId) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Notification ID missing']);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE id = ? AND user_id = ? AND user_type = 'student'
    ");
    $stmt->bind_param("ii", $notifId, $studentId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Notification not found or already read']);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}