<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); exit;
}

$id = (int)($_POST['id'] ?? 0);
$sql = "DELETE FROM books WHERE id=$id";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['status'=>'success','message'=>'Book deleted successfully']);
} else {
    echo json_encode(['status'=>'error','message'=>mysqli_error($conn)]);
}
