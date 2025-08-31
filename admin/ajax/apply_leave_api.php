<?php
header("Content-Type: application/json");
require_once '../sass/db_config.php';

// Allow only POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST requests allowed"]);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['faculty_id', 'school_id', 'leave_type', 'start_date', 'end_date', 'reason'];
foreach ($required as $field) {
    if (empty($input[$field])) {
        echo json_encode(["status" => "error", "message" => "$field is required."]);
        exit;
    }
}

// Sanitize inputs
$faculty_id   = intval($input['faculty_id']);
$school_id    = intval($input['school_id']);
$leave_type   = $conn->real_escape_string($input['leave_type']);
$start_date   = $conn->real_escape_string($input['start_date']);
$end_date     = $conn->real_escape_string($input['end_date']);
$reason       = $conn->real_escape_string($input['reason']);

// Insert leave request
$sql = "INSERT INTO faculty_leaves (school_id, faculty_id, leave_type, start_date, end_date, reason, status)
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iissss", $school_id, $faculty_id, $leave_type, $start_date, $end_date, $reason);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Leave applied successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to apply leave."]);
}

$stmt->close();
$conn->close();