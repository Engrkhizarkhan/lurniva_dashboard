<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

if (!isset($_FILES['school_logo'])) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['school_logo'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

// Validate file type
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid image type. Allowed: JPG, PNG, GIF']);
    exit;
}

// Validate file size (max 2MB)
if ($file['size'] > 2 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'File too large. Max 2MB allowed']);
    exit;
}

// Generate unique file name
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = 'logo_' . $admin_id . '_' . time() . '.' . $ext;
$uploadDir = '../uploads/logos/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$uploadPath = $uploadDir . $newFileName;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
    exit;
}

// Update DB with new logo path (relative to your web root, adjust accordingly)
$logoPathForDB = '../uploads/logos/' . $newFileName;
$stmt = $conn->prepare("UPDATE schools SET logo = ? WHERE id = ?");
$stmt->bind_param('si', $newFileName, $admin_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'logo_path' => $logoPathForDB]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}