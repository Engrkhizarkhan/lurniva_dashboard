<?php
session_start();
require_once '../admin/sass/db_config.php'; // ✅ Adjust the path if needed

header('Content-Type: application/json; charset=UTF-8');

// 🧩 Allow both session & direct JSON call (for Flutter/Postman)
$data = json_decode(file_get_contents("php://input"), true);

$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? $_POST['student_id'] ?? 0);

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or missing student_id']);
    exit;
}

// ✅ Collect fields safely (prefer JSON > POST)
$full_name     = trim($data['full_name'] ?? $_POST['full_name'] ?? '');
$parent_name   = trim($data['parent_name'] ?? $_POST['parent_name'] ?? '');
$gender        = trim($data['gender'] ?? $_POST['gender'] ?? '');
$dob           = trim($data['dob'] ?? $_POST['dob'] ?? '');
$cnic_formb    = trim($data['cnic_formb'] ?? $_POST['cnic_formb'] ?? '');
$class_grade   = trim($data['class_grade'] ?? $_POST['class_grade'] ?? '');
$section       = trim($data['section'] ?? $_POST['section'] ?? '');
$roll_number   = trim($data['roll_number'] ?? $_POST['roll_number'] ?? '');
$address       = trim($data['address'] ?? $_POST['address'] ?? '');
$email         = trim($data['email'] ?? $_POST['email'] ?? '');
$parent_cnic  = trim($data['parent_cnic'] ?? $_POST['parent_cnic'] ?? '');
$phone         = trim($data['phone'] ?? $_POST['phone'] ?? '');

if (empty($full_name) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Full name and email are required']);
    exit;
}

// ✅ Update student record
$stmt = $conn->prepare("
    UPDATE students 
    SET 
        parent_name=?, 
        full_name=?, 
        gender=?, 
        dob=?, 
        cnic_formb=?, 
        class_grade=?, 
        section=?, 
        roll_number=?, 
        address=?, 
        email=?, 
        parent_cnic=?, 
        phone=?,
        updated_at = NOW()
    WHERE id=?
");

$stmt->bind_param(
    "ssssssssssssi",
    $parent_name,
    $full_name,
    $gender,
    $dob,
    $cnic_formb,
    $class_grade,
    $section,
    $roll_number,
    $address,
    $email,
    $parent_cnic,
    $phone,
    $student_id
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile', 'error' => $stmt->error]);
}