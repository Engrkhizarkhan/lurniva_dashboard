<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json'); // force JSON output

$school_id = intval($_SESSION['admin_id'] ?? 0);
$id        = intval($_POST['id'] ?? 0);
$name      = trim($_POST['period_name'] ?? '');
$type      = trim($_POST['period_type'] ?? '');
$start     = $_POST['start_date'] ?? '';
$end       = $_POST['end_date'] ?? '';
$status    = $_POST['status'] ?? 'active'; // default

// ✅ Validate
if (empty($school_id) || empty($name) || empty($type) || empty($start) || empty($end)) {
    echo json_encode(['status' => 'danger', 'message' => $school_id.$name.$start.$end.$type]);
    exit;
}

if ($id > 0) {
    // ✅ Update
    $stmt = $conn->prepare("UPDATE fee_periods 
        SET period_name=?, period_type=?, start_date=?, end_date=?, status=? 
        WHERE id=? AND school_id=?");
    if (!$stmt) {
        echo json_encode(['status' => 'danger', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("sssssii", $name, $type, $start, $end, $status, $id, $school_id);

} else {
    // ✅ Insert
    $stmt = $conn->prepare("INSERT INTO fee_periods 
        (school_id, period_name, period_type, start_date, end_date, status) 
        VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['status' => 'danger', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("isssss", $school_id, $name, $type, $start, $end, $status);
}

// ✅ Execute
if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Fee period saved successfully']);
} else {
    echo json_encode([
        'status' => 'danger',
        'message' => 'Execute error: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();