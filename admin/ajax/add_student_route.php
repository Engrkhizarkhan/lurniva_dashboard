<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'danger', 'message' => 'Session expired']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $school_id  = (int)$_SESSION['admin_id'];
    $student_id = $_POST['student_id'] ?? '';
    $route_id   = $_POST['route_id'] ?? '';

    if (empty($student_id) || empty($route_id)) {
        echo json_encode(['status' => 'danger', 'message' => 'All fields are required']);
        exit;
    }

    try {
        // Check duplicate for the same school
        $check = $conn->prepare("SELECT id FROM transport_student_routes WHERE student_id = ? AND school_id = ?");
        $check->execute([$student_id, $school_id]);
        if ($check->fetch()) {
            echo json_encode(['status' => 'danger', 'message' => 'This student is already assigned in this school']);
            exit;
        }

        // Insert assignment with school_id
        $stmt = $conn->prepare("INSERT INTO transport_student_routes (school_id, student_id, route_id, assigned_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$school_id, $student_id, $route_id]);

        echo json_encode(['status' => 'success', 'message' => 'Student assigned successfully']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'danger', 'message' => $e->getMessage()]);
    }
}
