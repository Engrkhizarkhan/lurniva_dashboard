<?php
session_start();
require '../admin/sass/db_config.php'; // ✅ Adjust path for API folder

header('Content-Type: application/json; charset=UTF-8');

// 🧩 Allow both session & direct API call (Postman / Flutter)
$data = json_decode(file_get_contents("php://input"), true);
$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? $_POST['student_id'] ?? 0);

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or missing student_id']);
    exit;
}

// ✅ Fetch student profile
$stmt = $conn->prepare("
    SELECT 
        id, 
        school_id, 
        parent_name, 
        full_name, 
        gender, 
        dob, 
        cnic_formb, 
        class_grade, 
        section, 
        roll_number, 
        address, 
        email, 
        parent_cnic, 
        phone, 
        profile_photo, 
        status, 
        subscription_start, 
        subscription_end 
    FROM students 
    WHERE id = ?
    LIMIT 1
");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    exit;
}

$data = $result->fetch_assoc();

// ✅ Normalize image URL if needed
$base_url = "../student/uploads/profile/";
$data['profile_photo_url'] = !empty($data['profile_photo'])
    ? $base_url . $data['profile_photo']
    : $base_url . "default.png";

echo json_encode([
    'status' => 'success',
    'data' => $data
]);