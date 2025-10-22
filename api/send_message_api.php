<?php
require_once '../admin/sass/db_config.php'; // adjust path if needed

header('Content-Type: application/json; charset=UTF-8');

// ✅ Get authentication data from POST instead of session
$school_id = intval($_POST['school_id'] ?? 0);
$sender_id = intval($_POST['student_id'] ?? 0);
$sender_designation = 'student';

// ✅ Validate authentication
if ($school_id <= 0 || $sender_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// ✅ Collect POST data
$receiver_id = intval($_POST['receiver_id'] ?? 0);
$receiver_designation = trim($_POST['receiver_designation'] ?? '');
$message = trim($_POST['message'] ?? '');
$sent_at = date('Y-m-d H:i:s');
$status = 'unread';

$voice_note_filename = null;
$file_attachment = null;

// ✅ Validate minimum input
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
// 💾 Save Message
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
            'receiver_id' => $receiver_id,
            'receiver_designation' => $receiver_designation,
            'text' => $message,
            'file' => $file_attachment,
            'voice' => $voice_note_filename,
            'sent_at' => $sent_at
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
}
?>