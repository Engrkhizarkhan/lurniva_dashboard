<?php
session_start();
require '../sass/db_config.php';
header('Content-Type: application/json');

// ✅ Check student login
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$student_id = $_SESSION['student_id'];
$action = $_POST['action'] ?? '';

if ($action === 'getMyReports') {
    $sql = "SELECT b.id, b.topic, b.description, b.deadline, b.parent_approval, 
                   b.attachment, b.parent_approved, 
                   f.full_name AS teacher_name, c.class_name
            FROM student_behavior b
            JOIN class_timetable_meta c ON b.class_id = c.id
            JOIN faculty f ON f.id = b.teacher_id
            WHERE b.student_id = ? 
              AND b.created_at >= (NOW() - INTERVAL 30 DAY)
            ORDER BY b.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}

// ✅ Parent approval update
if ($action === 'approveReport') {
    $report_id = intval($_POST['report_id'] ?? 0);

    if ($report_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid report ID']);
        exit;
    }

    $sql = "UPDATE student_behavior 
            SET parent_approved = 1 
            WHERE id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $report_id, $student_id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Report approved successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to approve report']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
