<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php';

// Ensure logged-in student
if (!isset($_SESSION['student_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$studentId = $_SESSION['student_id'];

// Fetch unread notifications for student
$stmt = $conn->prepare("
    SELECT id, school_id, module, title, created_at 
    FROM notifications 
    WHERE user_id = ? AND user_type = 'student' AND is_read=0
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->bind_param("i", $studentId);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

echo json_encode(["success" => true, "notifications" => $notifications]);

$stmt->close();
$conn->close();