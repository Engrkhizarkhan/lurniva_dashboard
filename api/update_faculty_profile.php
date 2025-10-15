<?php
session_start();
require_once '../admin/sass/db_config.php';

// --- ✅ Enable CORS (for Flutter / Postman) ---
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight (CORS OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Check login
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access — please log in again.'
    ]);
    exit;
}

$faculty_id = intval($_SESSION['admin_id']);

// ✅ Collect input safely
$full_name           = trim($_POST['full_name'] ?? '');
$cnic                = trim($_POST['cnic'] ?? '');
$qualification       = trim($_POST['qualification'] ?? '');
$subjects            = trim($_POST['subjects'] ?? '');
$email               = trim($_POST['email'] ?? '');
$phone               = trim($_POST['phone'] ?? '');
$address             = trim($_POST['address'] ?? '');
$joining_date        = trim($_POST['joining_date'] ?? '');
$employment_type     = trim($_POST['employment_type'] ?? '');
$schedule_preference = trim($_POST['schedule_preference'] ?? '');

// ✅ Basic validation
if (empty($full_name) || empty($email)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Full name and email are required.'
    ]);
    exit;
}

// ✅ Update query
$stmt = $conn->prepare("
    UPDATE faculty 
    SET 
        full_name = ?, 
        cnic = ?, 
        qualification = ?, 
        subjects = ?, 
        email = ?, 
        phone = ?, 
        address = ?, 
        joining_date = ?, 
        employment_type = ?, 
        schedule_preference = ?,
        updated_at = NOW()
    WHERE id = ?
");

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

// ✅ Execute and respond
if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Profile updated successfully.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database update failed.',
        'error' => $stmt->error
    ]);
}

// Cleanup
$stmt->close();
$conn->close();
?>