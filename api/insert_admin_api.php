<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
require_once '../admin/sass/db_config.php'; // adjust path if needed

try {
    // Fixed test data
    $username = 'usman';
    $email = 'admin@lurniva.com';
    $password = '$2y$10$vu1OqO/ZJC/YKARHx0LlRueL/JojWg3AguqTPbOyyXmJl5kcjELeO'; // already hashed
    $full_name = 'Usman Jawad';
    $phone = null;
    $profile_image = null;
    $message_email = 'shayans1215225@gmail.com';
    $merchant_id = null;
    $store_id = null;
    $secret_key = null;
    $role = 'super_admin';
    $status = 'active';
    $verification_code = rand(100000, 999999);
    $code_expires_at = date("Y-m-d H:i:s", strtotime("+10 minutes"));

    $stmt = $conn->prepare("
        INSERT INTO app_admin 
        (username, email, password, full_name, phone, profile_image, message_email, merchant_id, store_id, secret_key, role, status, created_at, updated_at, verification_code, code_expires_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?)
    ");
    $stmt->bind_param(
        "ssssssssssssss",
        $username,
        $email,
        $password,
        $full_name,
        $phone,
        $profile_image,
        $message_email,
        $merchant_id,
        $store_id,
        $secret_key,
        $role,
        $status,
        $verification_code,
        $code_expires_at
    );

    if ($stmt->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Admin inserted successfully",
            "admin_id" => $conn->insert_id,
            "verification_code" => $verification_code
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Insert failed: " . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Exception: " . $e->getMessage()
    ]);
}
?>