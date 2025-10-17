<?php
require_once '../admin/sass/db_config.php';


header('Content-Type: application/json');

// ✅ Enable error display for debugging (optional)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Validate session
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['campus_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized access"
    ]);
    http_response_code(401);
    exit;
}

$facultyId = (int)$_SESSION['admin_id'];
$campusId  = (int)$_SESSION['campus_id'];

// ✅ Prepare query
$stmt = $conn->prepare("
    SELECT id, type, title, is_read, module, created_at 
    FROM notifications 
    WHERE user_id = ? 
      AND user_type = 'faculty' 
      AND school_id = ?
    ORDER BY created_at DESC 
    LIMIT 10
");

if (!$stmt) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $conn->error
    ]);
    http_response_code(500);
    exit;
}

$stmt->bind_param("ii", $facultyId, $campusId);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Format response
$data = [];
while ($row = $result->fetch_assoc()) {
    $link = "#";

    switch (strtolower($row['type'])) {
        case "meeting":    $link = "teacher_meetings.php"; break;
        case "attendance": $link = "faculty_attendance.php"; break;
        case "notice":     $link = "faculty_notice_board.php"; break;
        case "exam":       $link = "exam_results.php"; break;
        case "leaved":     $link = "leaved.php"; break;
        case "library":    $link = "teacher_library.php"; break;
    }

    $data[] = [
        "id"         => (int)$row['id'],
        "title"      => $row['title'],
        "is_read"    => (bool)$row['is_read'],
        "created_at" => $row['created_at'],
        "link"       => $link
    ];
}

// ✅ Send JSON
echo json_encode([
    "status" => "success",
    "count"  => count($data),
    "data"   => $data
]);

$stmt->close();
$conn->close();
?>