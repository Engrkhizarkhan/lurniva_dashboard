<?php
session_start();
require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    exit;
}

$facultyId = $_SESSION['admin_id'];
$schoolId  = $_SESSION['campus_id'];

$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 
                        WHERE user_id = ? AND user_type='faculty' AND school_id=?");
$stmt->bind_param("ii", $facultyId, $schoolId);
$stmt->execute();
$stmt->close();

echo "success";