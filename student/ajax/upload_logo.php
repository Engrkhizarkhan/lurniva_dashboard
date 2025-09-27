<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

// ✅ Ensure student is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$student_id = $_SESSION['student_id'];

// ✅ Check file upload
if (!isset($_FILES['student_photo'])) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['student_photo'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

// ✅ Validate file type
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid image type. Allowed: JPG, PNG, GIF']);
    exit;
}

// ✅ Validate file size (max 2MB)
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'File too large. Max 2MB allowed']);
    exit;
}

// ✅ Generate unique file name
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = 'student_' . $student_id . '_' . time() . '.' . $ext;
$uploadDir = '../uploads/profile/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$uploadPath = $uploadDir . $newFileName;

// ✅ Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
    exit;
}

// ✅ Save only filename in DB
$stmt = $conn->prepare("UPDATE students SET profile_photo = ? WHERE id = ?");
$stmt->bind_param('si', $newFileName, $student_id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'photo_path' => 'uploads/profile/' . $newFileName  // relative path for browser
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}
