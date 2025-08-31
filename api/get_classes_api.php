<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'admin/sass/db_config.php';

// ✅ Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['school_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing school_id"]);
    exit;
}

$school_id = intval($data['school_id']);

// ✅ Fetch distinct classes
$stmt = $conn->prepare("
    SELECT DISTINCT class_name 
    FROM class_timetable_meta 
    WHERE school_id = ? 
    ORDER BY class_name ASC
");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$classes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row['class_name'];
    }
}

echo json_encode([
    "status" => "success",
    "count"  => count($classes),
    "data"   => $classes
]);

$stmt->close();
$conn->close();
?>