<?php
require_once '../../admin/sass/db_config.php';
header('Content-Type: application/json; charset=UTF-8');

// ✅ Static insert query
$sql = "
INSERT INTO `app_admin` 
(`id`, `username`, `email`, `password`, `full_name`, `phone`, `profile_image`, `message_email`, `merchant_id`, `store_id`, `secret_key`, `role`, `status`, `created_at`, `updated_at`, `verification_code`, `code_expires_at`) 
VALUES (
    NULL,
    'Usman',
    'admin@lurniva.com',
    '$2y$10$0S2Wn/mEzR6GE6ZnHcCjrumfvGcuUs5Gc.waxXiIJfynJ6eQkRR56',
    'Usman Jawad',
    '03339299096',
    NULL,
    'usmanwaali@gmail.com',
    NULL,
    NULL,
    NULL,
    'super_admin',
    'active',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP,
    NULL,
    NULL
);
";

// ✅ Execute
if (mysqli_query($conn, $sql)) {
    echo json_encode([
        "status" => "success",
        "message" => "Static admin record inserted successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . mysqli_error($conn)
    ]);
}

$conn->close();
?>