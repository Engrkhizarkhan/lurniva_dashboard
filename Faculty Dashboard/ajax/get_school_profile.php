<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

// ✅ Check login session
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$faculty_id = $_SESSION['admin_id'];

// ✅ Fetch faculty profile
$stmt = $conn->prepare("SELECT id, campus_id, full_name, cnic, qualification, subjects, email, phone, address, joining_date, employment_type, schedule_preference, photo, status, rating 
                        FROM faculty WHERE id = ?");
$stmt->bind_param('i', $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Faculty not found']);
    exit;
}

$data = $result->fetch_assoc();

echo json_encode(['status' => 'success', 'data' => $data]);
