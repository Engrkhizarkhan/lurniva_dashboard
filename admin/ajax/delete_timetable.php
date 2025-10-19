<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$admin_id = $_SESSION['admin_id'];
$class_id = $_POST['timing_table_id'] ?? null;

if (!$class_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing class ID']);
    exit;
}

$conn->begin_transaction();

try {
    // 1️⃣ Delete periods for this class
    $stmt = $conn->prepare("DELETE FROM class_timetable_details WHERE timing_meta_id = ?");
    $stmt->bind_param("i", $class_id);
    $stmt->execute();
    $stmt->close();


    // 3️⃣ Delete the class itself
    $stmt = $conn->prepare("DELETE FROM class_timetable_meta WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $class_id, $admin_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Class timetable deleted successfully']);
    } else {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Class not found or unauthorized']);
    }

    $stmt->close();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>