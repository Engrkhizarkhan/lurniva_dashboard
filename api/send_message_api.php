<?php
require_once '../admin/sass/db_config.php';

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Decode JSON body if sent as raw
$input = json_decode(file_get_contents('php://input'), true);
if (empty($input) && !empty($_POST)) {
    $input = $_POST;
}

// ✅ Get values (works for JSON or form-data)
$school_id = intval($input['school_id'] ?? 0);
$sender_id = intval($input['sender_id'] ?? 0);
$sender_designation = trim($input['sender_designation'] ?? 'student');
$receiver_id = intval($input['receiver_id'] ?? 0);
$receiver_designation = trim($input['receiver_designation'] ?? '');
$message = trim($input['message'] ?? '');
$sent_at = date('Y-m-d H:i:s');
$status = 'unread';

$voice_note_filename = null;
$file_attachment = null;

// ✅ Validation
if ($school_id <= 0 || $sender_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing sender or school ID']);
    exit;
}
if ($receiver_id <= 0 || empty($receiver_designation)) {
    echo json_encode(['status' => 'error', 'message' => 'Receiver details missing']);
    exit;
}
if (empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot send an empty message']);
    exit;
}

// ✅ Insert message
$stmt = $conn->prepare("
    INSERT INTO messages 
    (school_id, sender_designation, sender_id, receiver_designation, receiver_id, message, sent_at, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("isisssss", $school_id, $sender_designation, $sender_id, $receiver_designation, $receiver_id, $message, $sent_at, $status);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Message sent successfully',
        'data' => [
            'school_id' => $school_id,
            'sender_id' => $sender_id,
            'receiver_id' => $receiver_id,
            'text' => $message,
            'sent_at' => $sent_at
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error', 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>