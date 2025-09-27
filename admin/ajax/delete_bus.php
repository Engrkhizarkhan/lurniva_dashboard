<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); exit;
}

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['status'=>'error','message'=>'Invalid bus id']); exit;
}

// Option: You may check for related assignments (drivers/student_transport) here before deleting.

if (mysqli_query($conn, "DELETE FROM buses WHERE id=$id")) {
    echo json_encode(['status'=>'success','message'=>'Bus deleted successfully']);
} else {
    echo json_encode(['status'=>'error','message'=>mysqli_error($conn)]);
}
