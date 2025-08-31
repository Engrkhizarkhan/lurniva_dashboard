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

$stmt = $conn->prepare("SELECT layout, sidebar_color, color_theme, mini_sidebar, sticky_header FROM school_settings WHERE school_id = ?");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Return default values if no settings found
    echo json_encode([
        'status' => 'success',
        'settings' => [
            'layout' => 1,
            'sidebar_color' => 2,
            'color_theme' => 'white',
            'mini_sidebar' => 0,
            'sticky_header' => 0
        ]
    ]);
    exit;
}

$settings = $result->fetch_assoc();

echo json_encode(['status'=>'success', 'settings'=>$settings]);