<?php
require_once '../admin/sass/db_config.php';


// --- ✅ Enable CORS ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// --- ✅ Handle preflight (CORS) ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- ✅ Parse JSON input ---
$data = json_decode(file_get_contents("php://input"), true);
$id        = intval($data['id'] ?? 0);
$status    = trim($data['status'] ?? '');
$teacher_id = intval($data['teacher_id'] ?? 0); // sent from Flutter or Postman for auth
$school_id  = intval($data['school_id'] ?? 0);

// --- ✅ Validate input ---
$validStatuses = ['Pending', 'Approved', 'Rejected'];
if ($id <= 0 || !$teacher_id || !$school_id || !in_array($status, $validStatuses)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid input or missing parameters."
    ]);
    exit;
}

// --- ✅ Verify the leave belongs to this teacher/school ---
$check = $conn->prepare("SELECT id FROM student_leaves WHERE id = ? AND school_id = ? AND teacher_id = ?");
$check->bind_param("iii", $id, $school_id, $teacher_id);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Leave request not found or unauthorized access."
    ]);
    exit;
}
$check->close();

// --- ✅ Update status ---
$stmt = $conn->prepare("UPDATE student_leaves SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Leave status updated successfully.",
        "id"      => $id,
        "status"  => $status
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Database error while updating leave status."
    ]);
}

$stmt->close();
$conn->close();
?>