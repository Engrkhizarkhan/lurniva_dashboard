<?php
session_start();
header('Content-Type: application/json');

require '../sass/db_config.php';  // your DB connection file

if (!isset($_SESSION['app_admin_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized'
    ]);
    exit;
}

$admin_id = $_SESSION['app_admin_id'];

// Prepare and execute query to get user settings
$sql = "SELECT * FROM school_settings WHERE person = 'app_admin' AND person_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $settings = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'settings' => $settings
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No settings found'
    ]);
}