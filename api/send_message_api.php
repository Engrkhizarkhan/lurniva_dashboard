<?php
require_once '../admin/sass/db_config.php'; // Adjust path if needed

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// ✅ Handle preflight (Flutter/Web)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Get POST data
$school_id = intval($_POST['school_id'] ?? 0);
$sender_id = intval($_POST['sender_id'] ?? 0);
$sender_designation = trim($_POST['sender_designation'] ?? 'student'); // default student
$receiver_id = intval($_POST['receiver_id'] ?? 0);
$receiver_designation = trim($_POST['receiver_designation'] ?? '');
$message = trim($_POST['message'] ?? '');
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
if (empty($message) && empty($_FILES['voice_note']) && empty($_FILES['file_attachment'])) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot send an empty message']);
    exit;
}

// -----------------------------
// 🎙 Upload Voice Note
// -----------------------------
if (isset($_FILES['voice_note']) && $_FILES['voice_note']['error'] === UPLOAD_ERR_OK) {
    $allowedMimes = [
        'audio/webm' => 'webm',
        'audio/ogg'  => 'ogg',
        'audio/mp3'  => 'mp3',
        'audio/mpeg' => 'mp3',
        'audio/wav'  => 'wav'
    ];

    $mime = $_FILES['voice_note']['type'];
    $ext = $allowedMimes[$mime] ?? pathinfo($_FILES['voice_note']['name'], PATHINFO_EXTENSION);

    if (!$ext) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid voice note format']);
        exit;
    }

    $uploadDir = '../student/uploads/voice_notes/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

    $newName = uniqid('voice_', true) . '.' . $ext;
    $targetPath = $uploadDir . $newName;

    if (!move_uploaded_file($_FILES['voice_note']['tmp_name'], $targetPath)) {
        echo json_encode(['status' => 'error', 'message' => 'Voice upload failed']);
        exit;
    }

    $voice_note_filename = $newName;
}

// -----------------------------
// 📎 Upload File Attachment
// -----------------------------
if (isset($_FILES['file_attachment']) && $_FILES['file_attachment']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = '../student/uploads/chat_files/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

    $originalName = basename($_FILES['file_attachment']['name']);
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);

    if (!$ext) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid attachment']);
        exit;
    }

    $newName = uniqid('file_', true) . '.' . $ext;
    $targetPath = $uploadDir . $newName;

    if (!move_uploaded_file($_FILES['file_attachment']['tmp_name'], $targetPath)) {
        echo json_encode(['status' => 'error', 'message' => 'File upload failed']);
        exit;
    }

    $file_attachment = $newName;
}

// -----------------------------
// 💾 Save Message to DB
// -----------------------------
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
        'status' => 'success',
        'message' => 'Message sent successfully',
        'data' => [
            'school_id' => $school_id,
            'sender_id' => $sender_id,
            'sender_designation' => $sender_designation,
            'receiver_id' => $receiver_id,
            'receiver_designation' => $receiver_designation,
            'text' => $message,
            'file_attachment' => $file_attachment,
            'voice_note' => $voice_note_filename,
            'sent_at' => $sent_at
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error', 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>