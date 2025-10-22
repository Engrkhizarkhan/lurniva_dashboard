<?php
require_once '../admin/sass/db_config.php';


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// ✅ Handle preflight (for Flutter)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Read POST/JSON data
$data = json_decode(file_get_contents("php://input"), true);

$receiver_id = intval($data['receiver_id'] ?? $_POST['receiver_id'] ?? 0); // teacher/admin id
$sender_id = intval($data['sender_id'] ?? $_POST['sender_id'] ?? 0);
$sender_designation = trim($data['sender_designation'] ?? $_POST['sender_designation'] ?? '');

if ($receiver_id <= 0 || $sender_id <= 0 || empty($sender_designation)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

// ✅ Fetch chat messages
$sql = "
    SELECT 
        m.*, 
        COALESCE(
            IF(LOWER(m.sender_designation) = 'student', s.profile_photo, NULL),
            IF(LOWER(m.sender_designation) IN ('teacher', 'faculty'), f.photo, NULL),
            IF(LOWER(m.sender_designation) = 'admin', sch.logo, NULL)
        ) AS sender_image
    FROM messages m
    LEFT JOIN students s 
        ON LOWER(m.sender_designation) = 'student' AND m.sender_id = s.id
    LEFT JOIN faculty f 
        ON LOWER(m.sender_designation) IN ('teacher', 'faculty') AND m.sender_id = f.id
    LEFT JOIN schools sch 
        ON LOWER(m.sender_designation) = 'admin' AND m.sender_id = sch.id
    WHERE 
        (
            (m.sender_id = ? AND m.sender_designation = ? AND m.receiver_id = ? AND m.receiver_designation = 'teacher')
            OR 
            (m.receiver_id = ? AND m.receiver_designation = ? AND m.sender_id = ? AND m.sender_designation = 'teacher')
        )
    ORDER BY m.sent_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isisis", $sender_id, $sender_designation, $receiver_id, $sender_id, $sender_designation, $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];

while ($row = $result->fetch_assoc()) {
    // Determine image path
    switch (strtolower($row['sender_designation'])) {
        case 'admin':
            $image_path = '../admin/uploads/logos/' . ($row['sender_image'] ?? 'default-avatar.png');
            break;
        case 'teacher':
        case 'faculty':
            $image_path = '../Faculty Dashboard/uploads/profile/' . ($row['sender_image'] ?? 'default-avatar.png');
            break;
        case 'student':
            $image_path = '../student/uploads/profile/' . ($row['sender_image'] ?? 'default-avatar.png');
            break;
        default:
            $image_path = 'assets/img/default-avatar.png';
    }

    // File / voice paths
    $file_attachment = !empty($row['file_attachment']) 
        ? '../uploads/chat_files/' . $row['file_attachment']
        : null;

    $voice_note = !empty($row['voice_note']) 
        ? '../uploads/voice_notes/' . $row['voice_note']
        : null;

    $messages[] = [
        'id'                => (int)$row['id'],
        'sender_id'         => (int)$row['sender_id'],
        'sender_designation'=> $row['sender_designation'],
        'receiver_id'       => (int)$row['receiver_id'],
        'receiver_designation'=> $row['receiver_designation'],
        'message'           => $row['message'],
        'file_attachment'   => $file_attachment,
        'voice_note'        => $voice_note,
        'sender_image'      => $image_path,
        'sent_at'           => $row['sent_at'],
        'time_ago'          => timeAgo($row['sent_at'])
    ];
}

echo json_encode([
    'status' => 'success',
    'count'  => count($messages),
    'data'   => $messages
]);

$stmt->close();
$conn->close();

// ✅ Helper: Time ago format
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) return "$diff seconds ago";
    elseif ($diff < 3600) return floor($diff / 60) . " minutes ago";
    elseif ($diff < 86400) return floor($diff / 3600) . " hours ago";
    return floor($diff / 86400) . " days ago";
}
?>