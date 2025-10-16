<?php
require_once '../admin/sass/db_config.php';

header('Content-Type: application/json');

// ✅ Verify teacher session
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$teacher_id = $_SESSION['admin_id'];
$sender_id = intval($_POST['sender_id'] ?? 0);
$sender_designation = $_POST['sender_designation'] ?? '';

if (!$sender_id || !$sender_designation) {
    echo json_encode(["status" => "error", "message" => "Missing sender info"]);
    exit;
}

$sql = "SELECT 
    m.*,
    COALESCE(
        IF(LOWER(m.sender_designation) = 'student', s.profile_photo, NULL),
        IF(LOWER(m.sender_designation) IN ('faculty', 'teacher'), f.photo, NULL),
        IF(LOWER(m.sender_designation) = 'admin', sch.logo, NULL)
    ) AS sender_image
FROM messages m
LEFT JOIN students s 
    ON LOWER(m.sender_designation) = 'student' AND m.sender_id = s.id
LEFT JOIN faculty f 
    ON LOWER(m.sender_designation) IN ('faculty', 'teacher') AND m.sender_id = f.id
LEFT JOIN schools sch 
    ON LOWER(m.sender_designation) = 'admin' AND m.sender_id = sch.id
WHERE 
(
    (m.sender_id = ? AND m.sender_designation = ? AND m.receiver_id = ? AND m.receiver_designation = 'teacher')
    OR
    (m.receiver_id = ? AND m.receiver_designation = ? AND m.sender_id = ? AND m.sender_designation = 'teacher')
)
ORDER BY m.sent_at ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isiisi", $sender_id, $sender_designation, $teacher_id, $sender_id, $sender_designation, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {

    // ✅ Determine correct media path
    if ($row['sender_designation'] === 'admin') {
        $basePath = '../admin/uploads/';
        $imagePath = $basePath . 'logos/' . $row['sender_image'];
    } elseif (in_array($row['sender_designation'], ['faculty', 'teacher'])) {
        $basePath = '../Faculty Dashboard/uploads/';
        $imagePath = $basePath . 'profile/' . $row['sender_image'];
    } elseif ($row['sender_designation'] === 'student') {
        $basePath = '../student/uploads/';
        $imagePath = $basePath . 'profile/' . $row['sender_image'];
    } else {
        $imagePath = 'assets/img/default-avatar.png';
    }

    if (empty($row['sender_image'])) {
        $imagePath = 'assets/img/default-avatar.png';
    }

    // ✅ Prepare message payload
    $messages[] = [
        "id" => $row['id'],
        "sender_id" => $row['sender_id'],
        "sender_designation" => $row['sender_designation'],
        "receiver_id" => $row['receiver_id'],
        "receiver_designation" => $row['receiver_designation'],
        "message" => $row['message'],
        "attachment" => !empty($row['file_attachment']) ? $basePath . 'chat_files/' . $row['file_attachment'] : null,
        "voice_note" => !empty($row['voice_note']) ? $basePath . 'voice_notes/' . $row['voice_note'] : null,
        "sender_image" => $imagePath,
        "sent_at" => date('d M, h:i A', strtotime($row['sent_at'])),
        "status" => $row['status']
    ];
}

echo json_encode(["status" => "success", "count" => count($messages), "data" => $messages]);
?>