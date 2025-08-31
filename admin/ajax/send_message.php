<?php
session_start();
require_once '../sass/db_config.php';

// Configuration
$school_id = $_SESSION['admin_id']; // Set dynamically if needed
$sender_id = $_SESSION['admin_id'];
$sender_designation = 'admin';

$receiver_id = $_POST['receiver_id'] ?? null;
$receiver_designation = $_POST['receiver_designation'] ?? null;
$message = isset($_POST['message']) ? trim($_POST['message']) : null;
$sent_at = date('Y-m-d H:i:s');
$status = 'unread';

$voice_note_filename = null;
$file_attachment = null;

//////////////////////////////////
// Voice Note Upload
//////////////////////////////////
if (isset($_FILES['voice_note']) && $_FILES['voice_note']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/voice_notes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = basename($_FILES['voice_note']['name']);
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);

    // Fallback for extension from MIME type
    if (!$ext) {
        $mime = $_FILES['voice_note']['type'];
        $mimeToExt = [
            'audio/webm' => 'webm',
            'audio/ogg' => 'ogg',
            'audio/mp3' => 'mp3',
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
        ];
        $ext = $mimeToExt[$mime] ?? null;
    }

    if (!$ext) {
        echo 'Invalid voice note file.';
        exit;
    }

    $newName = uniqid('voice_', true) . '.' . $ext;
    $targetPath = $uploadDir . $newName;

    if (move_uploaded_file($_FILES['voice_note']['tmp_name'], $targetPath)) {
        $voice_note_filename = $newName;
    } else {
        echo 'Failed to upload voice note.';
        exit;
    }
}

//////////////////////////////////
// File Attachment Upload
//////////////////////////////////
if (isset($_FILES['file_attachment']) && $_FILES['file_attachment']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/chat_files/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $originalName = basename($_FILES['file_attachment']['name']);
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);

    if (!$ext) {
        echo 'Invalid file attachment.';
        exit;
    }

    $newName = uniqid('file_', true) . '.' . $ext;
    $targetPath = $uploadDir . $newName;

    if (move_uploaded_file($_FILES['file_attachment']['tmp_name'], $targetPath)) {
        $file_attachment = $newName;
    } else {
        echo 'Failed to upload file.';
        exit;
    }
}

//////////////////////////////////
// Validation
//////////////////////////////////
if (empty($message) && !$voice_note_filename && !$file_attachment) {
    echo 'Empty message.';
    exit;
}

//////////////////////////////////
// Insert into messages table
//////////////////////////////////
$stmt = $conn->prepare("INSERT INTO messages 
    (school_id, sender_designation, sender_id, receiver_designation, receiver_id, message, file_attachment, voice_note, sent_at, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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
    echo 'success';
} else {
    echo 'Database error: ' . $stmt->error;
}
?>