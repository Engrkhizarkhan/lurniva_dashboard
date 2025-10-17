<?php
session_start();
require_once '../admin/sass/db_config.php'; // ✅ adjust path if needed
header('Content-Type: application/json; charset=UTF-8');

// Enable strict error reporting (safe for debugging)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Allow JSON or session input
$data = json_decode(file_get_contents("php://input"), true);

$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? $_POST['student_id'] ?? 0);
$current_password = trim($data['current_password'] ?? $_POST['current_password'] ?? '');
$new_password     = trim($data['new_password'] ?? $_POST['new_password'] ?? '');

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or missing student ID']);
    exit;
}

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Both current and new passwords are required']);
    exit;
}

// ✅ Fetch existing password hash
$stmt = $conn->prepare("SELECT password FROM students WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Student not found']);
    exit;
}

$row = $result->fetch_assoc();
$current_hash = $row['password'];

// ✅ Verify current password
if (!password_verify($current_password, $current_hash)) {
    echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
    exit;
}

// ✅ Generate new password hash
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

// ✅ Update new password
$update = $conn->prepare("UPDATE students SET password = ?, updated_at = NOW() WHERE id = ?");
$update->bind_param("si", $new_hash, $student_id);

if ($update->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password']);
}