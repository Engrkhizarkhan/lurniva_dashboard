<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

// ✅ Check login (student must be logged in)
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$student_id = $_SESSION['student_id'];

// ✅ Collect input
$full_name     = $_POST['full_name'] ?? '';
$parent_name   = $_POST['parent_name'] ?? '';
$gender        = $_POST['gender'] ?? '';
$dob           = $_POST['dob'] ?? '';
$cnic_formb    = $_POST['cnic_formb'] ?? '';
$class_grade   = $_POST['class_grade'] ?? '';
$section       = $_POST['section'] ?? '';
$roll_number   = $_POST['roll_number'] ?? '';
$address       = $_POST['address'] ?? '';
$email         = $_POST['email'] ?? '';
$parent_email  = $_POST['parent_email'] ?? '';
$phone         = $_POST['phone'] ?? '';

if (empty($full_name) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Full name and email are required']);
    exit;
}

// ✅ Update student profile
$stmt = $conn->prepare("
    UPDATE students 
    SET parent_name=?, full_name=?, gender=?, dob=?, cnic_formb=?, 
        class_grade=?, section=?, roll_number=?, address=?, email=?, 
        parent_cnic=?, phone=? 
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
    $parent_email,
    $phone,
    $student_id
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Student profile updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update student profile']);
}