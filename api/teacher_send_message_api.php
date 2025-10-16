<?php
require_once '../admin/sass/db_config.php';

header('Content-Type: application/json');

// ✅ Session Check
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['campus_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$school_id = $_SESSION['campus_id'];
$sender_id = $_SESSION['admin_id'];
$sender_designation = 'teacher';

// ✅ Inputs
$receiver_id = intval($_POST['receiver_id'] ?? 0);
$receiver_designation = $_POST['receiver_designation'] ?? '';
$message = trim($_POST['message'] ?? '');
$sent_at = date('Y-m-d H:i:s');
$status = 'unread';

$voice_note_filename = null;
$file_attachment = null;

// ✅ Validation
if (!$receiver_id || empty($receiver_designation)) {
    echo json_encode(["status" => "error", "message" => "Missing receiver info"]);
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
        echo json_encode(["status" => "error", "message" => "Invalid voice note file"]);
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

// ✅ Insert into DB
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
?>