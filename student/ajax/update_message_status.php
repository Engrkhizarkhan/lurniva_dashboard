<?php
session_start();
require_once '../sass/db_config.php';

$receiver_id = $_SESSION['student_id'];
$sender_id = $_POST['sender_id'];
$sender_designation = $_POST['sender_designation'];

$sql = "UPDATE messages 
        SET status = 'read' 
        WHERE sender_id = ? 
        AND sender_designation = ? 
        AND receiver_id = ? 
        AND receiver_designation = 'student' 
        AND status = 'unread'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $sender_id, $sender_designation, $receiver_id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}
?>