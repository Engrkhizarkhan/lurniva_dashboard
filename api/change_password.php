<?php
session_start();
require_once '../admin/sass/db_config.php';

// --- ✅ Headers (for CORS + JSON output) ---
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// ✅ Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Check login session
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$admin_id = intval($_SESSION['admin_id']);

// ✅ Validate input
$current_password = trim($_POST['current_password'] ?? '');
$new_password     = trim($_POST['new_password'] ?? '');

if (empty($current_password) || empty($new_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Both current and new password are required.']);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode(['status' => 'error', 'message' => 'New password must be at least 6 characters long.']);
    exit;
}

// ✅ Fetch current hashed password
$stmt = $conn->prepare("SELECT password FROM faculty WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit;
}

$row = $result->fetch_assoc();
$current_hash = $row['password'];

// ✅ Verify current password
if (!password_verify($current_password, $current_hash)) {
    echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
    exit;
}

// ✅ Hash and update new password
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

$update = $conn->prepare("UPDATE faculty SET password = ?, updated_at = NOW() WHERE id = ?");
$update->bind_param("si", $new_hash, $admin_id);

if ($update->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Password updated successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password.', 'error' => $update->error]);
}

// ✅ Cleanup
$stmt->close();
$update->close();
$conn->close();
?>