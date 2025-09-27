<?php
session_start();
require '../sass/db_config.php';

if (!isset($_SESSION['student_id'])) {
    http_response_code(401);
    exit;
}

$studentId = $_SESSION['student_id'];
$notifId = $_POST['notif_id'] ?? null;

if (!$notifId) {
    http_response_code(400);
    exit;
}

// Mark single notification as read
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ? AND user_type = 'student'");
$stmt->bind_param("ii", $notifId, $studentId);
$stmt->execute();
$stmt->close();
$conn->close();

echo "success";