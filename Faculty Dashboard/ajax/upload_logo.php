<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

// ✅ Ensure faculty is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$faculty_id = $_SESSION['admin_id'];

if (!isset($_FILES['faculty_photo'])) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['faculty_photo'];
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
$newFileName = 'faculty_' . $faculty_id . '_' . time() . '.' . $ext;
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

// ✅ Save only filename in DB (safer & consistent)
$stmt = $conn->prepare("UPDATE faculty SET photo = ? WHERE id = ?");
$stmt->bind_param('si', $newFileName, $faculty_id);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'photo_path' => $uploadPath  // absolute path
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}
?>