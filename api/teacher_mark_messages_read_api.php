<?php
require_once '../admin/sass/db_config.php';


header('Content-Type: application/json');

// ✅ Check teacher login
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$receiver_id = $_SESSION['admin_id'];
$sender_id = intval($_POST['sender_id'] ?? 0);
$sender_designation = $_POST['sender_designation'] ?? '';

if ($sender_id <= 0 || empty($sender_designation)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing sender details']);
    exit;
}

// ✅ Update messages to 'read'
$sql = "UPDATE messages 
        SET status = 'read' 
        WHERE sender_id = ? 
          AND sender_designation = ? 
          AND receiver_id = ? 
          AND receiver_designation = 'teacher' 
          AND status = 'unread'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $sender_id, $sender_designation, $receiver_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Messages marked as read']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>