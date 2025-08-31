<?php
session_start();
require '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$school_id = (int)($_POST['school_id'] ?? 0);
if ($school_id <= 0) {
    echo json_encode(['status'=>'error','message'=>'Invalid school ID']);
    exit;
}

// Default settings values
$default = [
    'layout' => 1,
    'sidebar_color' => 2,
    'color_theme' => 'white',
    'mini_sidebar' => 0,
    'sticky_header' => 0
];

// Check if row exists
$stmt = $conn->prepare("SELECT school_id FROM school_settings WHERE school_id = ?");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Insert default row
    $insert_stmt = $conn->prepare("INSERT INTO school_settings (school_id, layout, sidebar_color, color_theme, mini_sidebar, sticky_header) VALUES (?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("iissii", $school_id, $default['layout'], $default['sidebar_color'], $default['color_theme'], $default['mini_sidebar'], $default['sticky_header']);
    $insert_stmt->execute();
} else {
    // Update to defaults
    $update_stmt = $conn->prepare("UPDATE school_settings SET layout=?, sidebar_color=?, color_theme=?, mini_sidebar=?, sticky_header=? WHERE school_id=?");
    $update_stmt->bind_param("issiii", $default['layout'], $default['sidebar_color'], $default['color_theme'], $default['mini_sidebar'], $default['sticky_header'], $school_id);
    $update_stmt->execute();
}

echo json_encode(['status'=>'success']);