<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

// ✅ Check login
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$faculty_id = $_SESSION['admin_id'];

// ✅ Collect input
$full_name          = $_POST['full_name'] ?? '';
$cnic               = $_POST['cnic'] ?? '';
$qualification      = $_POST['qualification'] ?? '';
$subjects           = $_POST['subjects'] ?? '';
$email              = $_POST['email'] ?? '';
$phone              = $_POST['phone'] ?? '';
$address            = $_POST['address'] ?? '';
$joining_date       = $_POST['joining_date'] ?? '';
$employment_type    = $_POST['employment_type'] ?? '';
$schedule_preference= $_POST['schedule_preference'] ?? '';

if (empty($full_name) || empty($email)) {
    echo json_encode(['status' => 'error', 'message' => 'Full name and email are required']);
    exit;
}

// ✅ Update faculty profile
$stmt = $conn->prepare("UPDATE faculty 
    SET full_name=?, cnic=?, qualification=?, subjects=?, email=?, phone=?, address=?, joining_date=?, employment_type=?, schedule_preference=? 
    WHERE id=?");

$stmt->bind_param(
    "ssssssssssi",
    $full_name,
    $cnic,
    $qualification,
    $subjects,
    $email,
    $phone,
    $address,
    $joining_date,
    $employment_type,
    $schedule_preference,
    $faculty_id
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update profile']);
}
