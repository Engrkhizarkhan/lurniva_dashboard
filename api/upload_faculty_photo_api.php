<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json; charset=UTF-8');

// ✅ Ensure faculty is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$faculty_id = intval($_SESSION['admin_id']);

// ✅ Check if file uploaded
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

// ✅ Generate unique filename
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = 'faculty_' . $faculty_id . '_' . time() . '.' . strtolower($ext);
$uploadDir = '../Faculty Dashboard/uploads/profile/';
$adminuploadDir = '../admin/uploads/profile/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$uploadPath = $uploadDir . $newFileName;
$uploadadmin = $adminuploadDir . $newFileName;

// ✅ Move file
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file']);
    exit;
}
if (!move_uploaded_file($file['tmp_name'], $uploadadmin)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file']);
    exit;
}

// ✅ Update database (faculty table, not student)
$stmt = $conn->prepare("UPDATE faculty SET photo = ? WHERE id = ?");
$stmt->bind_param('si', $newFileName, $faculty_id);

if ($stmt->execute()) {
    // ✅ Return relative or public path
    $publicUrl = '../Faculty Dashboard/uploads/profile/' . $newFileName;

    echo json_encode([
        'status' => 'success',
        'message' => 'Profile photo updated successfully',
        'photo_url' => $publicUrl
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}

$stmt->close();
$conn->close();
?>