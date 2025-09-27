<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

// ✅ Check login session
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$student_id = $_SESSION['student_id'];

// ✅ Fetch student profile
$stmt = $conn->prepare("SELECT id, school_id, parent_name, full_name, gender, dob, cnic_formb, class_grade, section, roll_number, address, email, parent_email, phone, profile_photo, status, subscription_start, subscription_end 
                        FROM students 
                        WHERE id = ?");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    exit;
}

$data = $result->fetch_assoc();

echo json_encode(['status' => 'success', 'data' => $data]);
