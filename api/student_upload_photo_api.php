<?php
session_start();
require_once '../admin/sass/db_config.php'; // ✅ Adjust path if needed

header('Content-Type: application/json; charset=UTF-8');

// ✅ Check if student is logged in
$student_id = intval($_SESSION['student_id'] ?? 0);

if ($student_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or invalid session']);
    exit;
}

// ✅ Ensure file is uploaded
if (!isset($_FILES['student_photo']) || $_FILES['student_photo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No photo uploaded or upload error']);
    exit;
}

$file = $_FILES['student_photo'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

// ✅ Validate type
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, GIF allowed']);
    exit;
}

// ✅ Validate size (2MB limit)
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'File too large. Max 2MB']);
    exit;
}

// ✅ Create unique filename
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$newFileName = 'student_' . $student_id . '_' . time() . '.' . $ext;

// ✅ Ensure upload directory exists
$uploadDir = __DIR__ . '/../uploads/profile/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$uploadPath = $uploadDir . $newFileName;

// ✅ Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
    exit;
}

// ✅ Update database
$stmt = $conn->prepare("UPDATE students SET profile_photo = ? WHERE id = ?");
$stmt->bind_param('si', $newFileName, $student_id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Profile photo updated successfully',
        'photo_path' => 'uploads/profile/' . $newFileName
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}