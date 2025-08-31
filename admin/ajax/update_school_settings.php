<?php
session_start();
require '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$school_id = (int)($_POST['school_id'] ?? 0);
$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

$allowed_fields = ['layout', 'sidebar_color', 'color_theme', 'mini_sidebar', 'sticky_header'];

if ($school_id <= 0 || !in_array($field, $allowed_fields)) {
    echo json_encode(['status'=>'error','message'=>'Invalid request']);
    exit;
}

// Sanitize and validate value
if (in_array($field, ['layout', 'sidebar_color', 'mini_sidebar', 'sticky_header'])) {
    $value = (int)$value;
} else {
    $value = $conn->real_escape_string($value);
}

// Check if row exists
$stmt = $conn->prepare("SELECT school_id FROM school_settings WHERE school_id = ?");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Insert new row
    $insert_stmt = $conn->prepare("INSERT INTO school_settings (school_id, $field) VALUES (?, ?)");
    if (in_array($field, ['layout', 'sidebar_color', 'mini_sidebar', 'sticky_header'])) {
        $insert_stmt->bind_param("ii", $school_id, $value);
    } else {
        $insert_stmt->bind_param("is", $school_id, $value);
    }
    $insert_stmt->execute();
} else {
    // Update existing row
    $update_sql = "UPDATE school_settings SET $field = ? WHERE school_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    if (in_array($field, ['layout', 'sidebar_color', 'mini_sidebar', 'sticky_header'])) {
        $update_stmt->bind_param("ii", $value, $school_id);
    } else {
        $update_stmt->bind_param("si", $value, $school_id);
    }
    $update_stmt->execute();
}

echo json_encode(['status'=>'success']);