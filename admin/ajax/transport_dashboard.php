<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']);
    exit;
}

$school_id = (int)($_SESSION['admin_id'] ?? 0);

try {
    // Count buses for this school
    $busCount = $conn->query("SELECT COUNT(*) FROM buses WHERE school_id=$school_id")->fetch_row()[0];

    // Count drivers for this school
    $driverCount = $conn->query("SELECT COUNT(*) FROM drivers WHERE school_id=$school_id")->fetch_row()[0];

    // Count assigned students for this school
    $assignedCount = $conn->query("
        SELECT COUNT(*) 
        FROM transport_student_routes sr
        JOIN students s ON sr.student_id = s.id
        WHERE s.school_id=$school_id
    ")->fetch_row()[0];

    echo json_encode([
        'status' => 'success',
        'buses' => $busCount,
        'drivers' => $driverCount,
        'assigned_students' => $assignedCount
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
