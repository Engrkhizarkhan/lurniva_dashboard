<?php
require_once '../admin/sass/db_config.php'; // adjust path if needed

// --- ✅ Enable CORS for Flutter/Postman ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Parse JSON input
$data = json_decode(file_get_contents("php://input"), true);

$school_id  = intval($data['school_id'] ?? 0);
$student_id = intval($data['student_id'] ?? 0);
$subject    = trim($data['subject'] ?? '');

if ($school_id <= 0 || $student_id <= 0 || $subject === '') {
    echo json_encode([
        "status"  => "error",
        "message" => "Missing or invalid parameters (school_id, student_id, subject)."
    ]);
    exit;
}

// ✅ Fetch student marks by subject
$sql = "
    SELECT 
        ta.due_date AS date,
        sr.marks_obtained AS value,
        ta.title AS title
    FROM student_results sr
    JOIN teacher_assignments ta ON sr.assignment_id = ta.id
    WHERE sr.student_id =_