<?php
session_start();
require_once '../sass/db_config.php';


$admin_id = $_SESSION['admin_id']; // or hardcode like $admin_id = 1;

$sql = "SELECT COUNT(*) AS unread_count 
        FROM messages 
        WHERE receiver_designation = 'teacher' 
          AND receiver_id = ? 
          AND status = 'unread'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo $row['unread_count'];
?>