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

// ✅ Accept JSON or FormData
$data = json_decode(file_get_contents("php://input"), true);
$isJson = is_array($data);

$school_id           = intval($data['school_id'] ?? $_POST['school_id'] ?? 0);
$sender_id           = intval($data['sender_id'] ?? $_POST['sender_id'] ?? 0);
$sender_designation  = trim($data['sender_designation'] ?? $_POST['sender_designation'] ?? '');
$receiver_id         = intval($data['receiver_id'] ?? $_POST['receiver_id'] ?? 0);
$receiver_designation= trim($data['receiver_designation'] ?? $_POST['receiver_designation'] ?? '');
$message             = trim($data['message'] ?? $_POST['message'] ?? '');
$sent_at             = date('Y-m-d H:i:s');
$status              = 'unread';

$voice_note_filename = null;
$file_attachment     = null;

// ✅ Validation
if (!$school_id || !$sender_id || !$sender_designation || !$receiver_id || !$receiver_designation) {
    echo json_encode(["status" => "error", "message" => "Missing required parameters"]);
    exit;
}

// ✅ Handle Voice Note Upload
if (!empty($_FILES['voice_note']['name'])) {
    $uploadDir = __DIR__ . '/../Faculty Dashboard/uploads/voice_notes/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = pathinfo($_FILES['voice_note']['name'], PATHINFO_EXTENSION);
    if (!$ext) {
        $mimeToExt = [
            'audio/webm' => 'webm', 'audio/ogg' => 'ogg',
            'audio/mpeg' => 'mp3', 'audio/mp3' => 'mp3',
            'audio/wav' => 'wav'
        ];
        $ext = $mimeToExt[$_FILES['voice_note']['type']] ?? '';
    }

    if (!$ext) {
        echo json_encode(["status" => "error", "message" => "Invalid voice note format"]);
        exit;
    }

    $newName = uniqid('voice_', true) . '.' . $ext;
    if (move_uploaded_file($_FILES['voice_note']['tmp_name'], $uploadDir . $newName)) {
        $voice_note_filename = $newName;
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to upload voice note"]);
        exit;
    }
}

// ✅ Handle File Attachment Upload
if (!empty($_FILES['file_attachment']['name'])) {
    $uploadDir = __DIR__ . '/../Faculty Dashboard/uploads/chat_files/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = pathinfo($_FILES['file_attachment']['name'], PATHINFO_EXTENSION);
    if (!$ext) {
        echo json_encode(["status" => "error", "message" => "Invalid file"]);
        exit;
    }

    $newName = uniqid('file_', true) . '.' . $ext;
    if (move_uploaded_file($_FILES['file_attachment']['tmp_name'], $uploadDir . $newName)) {
        $file_attachment = $newName;
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to upload file"]);
        exit;
    }
}

// ✅ Prevent Empty Messages
if (empty($message) && !$voice_note_filename && !$file_attachment) {
    echo json_encode(["status" => "error", "message" => "Empty message"]);
    exit;
}

// ✅ Insert into Database
$stmt = $conn->prepare("
    INSERT INTO messages 
    (school_id, sender_designation, sender_id, receiver_designation, receiver_id, message, file_attachment, voice_note, sent_at, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt->bind_param(
    "isisssssss",
    $school_id,
    $sender_designation,
    $sender_id,
    $receiver_designation,
    $receiver_id,
    $message,
    $file_attachment,
    $voice_note_filename,
    $sent_at,
    $status
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Message sent successfully",
        "data" => [
            "school_id" => $school_id,
            "sender_id" => $sender_id,
            "sender_designation" => $sender_designation,
            "receiver_id" => $receiver_id,
            "receiver_designation" => $receiver_designation,
            "text" => $message,
            "file_attachment" => $file_attachment ? "../Faculty Dashboard/uploads/chat_files/$file_attachment" : null,
            "voice_note" => $voice_note_filename ? "../Faculty Dashboard/uploads/voice_notes/$voice_note_filename" : null,
            "sent_at" => $sent_at
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error", "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>