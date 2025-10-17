<?php
require_once '../admin/sass/db_config.php';

header('Content-Type: application/json');

// ✅ Check if logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['campus_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access"
    ]);
    http_response_code(401);
    exit;
}

$facultyId = (int)$_SESSION['admin_id'];
$schoolId  = (int)$_SESSION['campus_id'];

// ✅ Update all unread notifications for this faculty
$stmt = $conn->prepare("
    UPDATE notifications 
    SET is_read = 1 
    WHERE user_id = ? 
      AND user_type = 'faculty' 
      AND school_id = ?
");

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $conn->error
    ]);
    http_response_code(500);
    exit;
}

$stmt->bind_param("ii", $facultyId, $schoolId);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "All faculty notifications marked as read"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update notifications"
    ]);
}

$stmt->close();
$conn->close();
?>