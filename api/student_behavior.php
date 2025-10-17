<?php
session_start();
require_once '../admin/sass/db_config.php'; // Adjust path as needed
header('Content-Type: application/json; charset=UTF-8');

// ✅ Allow both web session & JSON/POST input
$data = json_decode(file_get_contents('php://input'), true);
$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? $_POST['student_id'] ?? 0);
$action      = $_POST['action'] ?? $data['action'] ?? '';

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or missing student ID']);
    exit;
}

if ($action === 'getMyReports') {

    $sql = "SELECT 
                b.id,
                b.topic,
                b.description,
                b.deadline,
                b.parent_approval,
                b.attachment,
                b.parent_approved,
                f.full_name AS teacher_name,
                c.class_name
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

        // ✅ Attachment absolute URL (for Flutter)
        $attachment_url = '';
        if (!empty($row['attachment'])) {
            $base = rtrim((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'], '/');
            $attachment_url = "{$base}/Faculty Dashboard/uploads/behavior/" . rawurlencode($row['attachment']);
        }

        $data[] = [
            'id' => (int)$row['id'],
            'topic' => $row['topic'],
            'description' => $row['description'],
            'deadline' => $row['deadline'],
            'parent_approval' => (bool)$row['parent_approval'],
            'attachment' => $attachment_url,
            'parent_approved' => (bool)$row['parent_approved'],
            'teacher_name' => $row['teacher_name'],
            'class_name' => $row['class_name']
        ];
    }

    echo json_encode(['status' => 'success', 'data' => $data]);
    exit;
}


// ✅ Parent Approval Update
if ($action === 'approveReport') {
    $report_id = intval($_POST['report_id'] ?? $data['report_id'] ?? 0);

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
?>