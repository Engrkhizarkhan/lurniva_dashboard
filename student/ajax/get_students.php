<?php
session_start();
header('Content-Type: application/json');

require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$school_id = $_SESSION['admin_id'];

$sql = "SELECT id, full_name, class_grade, roll_number FROM students WHERE school_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = [
        'id' => $row['id'],
        'name' => $row['full_name'],
        'class' => $row['class_grade'],
        'roll' => $row['roll_number']
    ];
}

echo json_encode(['status' => 'success', 'data' => $students]);
$stmt->close();
$conn->close();