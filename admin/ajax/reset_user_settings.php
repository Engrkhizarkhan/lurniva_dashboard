<?php
session_start();
require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized"
    ]);
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Set your default values here:
$default_layout = 1;
$default_sidebar_color = 1;
$default_color_theme = 'white';
$default_mini_sidebar = 0;
$default_sticky_header = 0;

// Prepare update query to reset settings
$sql = "UPDATE school_settings SET 
            layout = ?, 
            sidebar_color = ?, 
            color_theme = ?, 
            mini_sidebar = ?, 
            sticky_header = ?,
            updated_at = NOW()
        WHERE person = 'admin' AND person_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iisiii",
    $default_layout,
    $default_sidebar_color,
    $default_color_theme,
    $default_mini_sidebar,
    $default_sticky_header,
    $admin_id
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Settings reset to default."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to reset settings."
    ]);
}

$stmt->close();
$conn->close();