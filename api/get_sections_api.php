<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'admin/sass/db_config.php';

// ✅ Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['school_id']) || !isset($data['class_name'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

$school_id  = intval($data['school_id']);
$class_name = trim($data['class_name']);

// ✅ Fetch distinct sections
$stmt = $conn->prepare("
    SELECT DISTINCT section 
    FROM class_timetable_meta 
    WHERE school_id = ? AND class_name = ?
    ORDER BY section ASC
");
$stmt->bind_param("is", $school_id, $class_name);
$stmt->execute();
$result = $stmt->get_result();

$sections = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row['section'];
    }
}

echo json_encode([
    "status" => "success",
    "count"  => count($sections),
    "data"   => $sections
]);

$stmt->close();
$conn->close();
?>