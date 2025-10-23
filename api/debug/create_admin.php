<?php
require_once '../../admin/sass/db_config.php';
header('Content-Type: application/json; charset=UTF-8');

// Read POST data (works for both form-data and raw JSON)
$input = $_POST;
if (empty($input)) {
    $json = file_get_contents("php://input");
    $input = json_decode($json, true);
}

$username = trim($input['username'] ?? '');
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');
$full_name = trim($input['full_name'] ?? '');
$phone = trim($input['phone'] ?? '');
$message_email = trim($input['message_email'] ?? '');
$role = trim($input['role'] ?? 'super_admin');
$status = trim($input['status'] ?? 'active');

// ✅ Validate required fields
if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

// ✅ Hash password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// ✅ Insert query
$stmt = $conn->prepare("
    INSERT INTO app_admin 
    (username, email, password, full_name, phone, profile_image, message_email, merchant_id, store_id, secret_key, role, status, created_at, updated_at) 
    VALUES (?, ?, ?, ?, ?, NULL, ?, NULL, NULL, NULL, ?, ?, NOW(), NOW())
");

$stmt->bind_param("sssssss", $username, $email, $hashed_password, $full_name, $phone, $message_email, $role, $status);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Admin created successfully",
        "data" => [
            "id" => $stmt->insert_id,
            "username" => $username,
            "email" => $email,
            "role" => $role,
            "status" => $status
        ]
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $stmt->error
    ]);
}
?>