<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$school_id = (int)$_SESSION['admin_id'];

$sql = "SELECT id, full_name, qualification, email 
        FROM faculty 
        WHERE campus_id = $school_id 
        ORDER BY full_name ASC";

$q = mysqli_query($conn, $sql);

$faculty = [];
while ($row = mysqli_fetch_assoc($q)) {
    $faculty[] = [
        'id'   => $row['id'],
        'text' => $row['full_name'] . " (" . $row['qualification'] . " | " . $row['email'] . ")"
    ];
}

echo json_encode(['status' => 'success', 'data' => $faculty]);