<?php
header('Content-Type: application/json');
require_once '../../admin/sass/db_config.php'; // Your database connection

// Check database connection
if (!$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Fetch all faculty records
$sql = "SELECT 
            id, campus_id, full_name, cnic, qualification, subjects, email, password, phone, address, 
            joining_date, employment_type, schedule_preference, photo, created_at, status, rating, 
            verification_code, is_verified, code_expires_at, verification_attempts, subscription_start, subscription_end
        FROM faculty";

$result = $conn->query($sql);

if ($result) {
    $facultyList = [];

    while ($row = $result->fetch_assoc()) {
        $facultyList[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $facultyList
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch faculty records'
    ]);
}

$conn->close();
?>