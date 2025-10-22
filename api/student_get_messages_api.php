<?php
require_once '../admin/sass/db_config.php';


header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Accept JSON or normal POST
$data = json_decode(file_get_contents("php://input"), true);
$receiver_id = intval($data['receiver_id'] ?? $_POST['receiver_id'] ?? 0);
$sender_id = intval($data['sender_id'] ?? $_POST['sender_id'] ?? 0);
$sender_designation = trim($data['sender_designation'] ?? $_POST['sender_designation'] ?? '');

if (!$receiver_id || !$sender_id || !$sender_designation) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

// ✅ Fetch chat messages between student & sender
$sql = "
SELECT 
    m.*, 
    COALESCE(
        IF(LOWER(m.sender_designation) = 'student', s.profile_photo, NULL),
        IF(LOWER(m.sender_designation) IN ('teacher','faculty'), f.photo, NULL),
        IF(LOWER(m.sender_designation) = 'admin', sch.logo, NULL)
    ) AS sender_image
FROM messages m
LEFT JOIN students s 
    ON LOWER(m.sender_designation) = 'student' AND m.sender_id = s.id
LEFT JOIN faculty f 
    ON LOWER(m.sender_designation) IN ('teacher','faculty') AND m.sender_id = f.id
LEFT JOIN schools sch 
    ON LOWER(m.sender_designation) = 'admin' AND m.sender_id = sch.id
WHERE 
(
    (m.sender_id = ? AND m.sender_designation = ? AND m.receiver_id = ? AND m.receiver_designation = 'student')
    OR
    (m.receiver_id = ? AND m.receiver_designation = ? AND m.sender_id = ? AND m.sender_designation = 'student')
)
ORDER BY m.sent_at ASC;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isissi", $sender_id, $sender_designation, $receiver_id, $sender_id, $sender_designation, $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    // ✅ Determine file paths
    switch (strtolower($row['sender_designation'])) {
        case 'admin':
            $base = '../admin/uploads/';
            $imagePath = $base . 'logos/' . ($row['sender_image'] ?: 'default-avatar.png');
            break;
        case 'teacher':
        case 'faculty':
            $base = '../Faculty Dashboard/uploads/';
            $imagePath = $base . 'profile/' . ($row['sender_image'] ?: 'default-avatar.png');
            break;
        case 'student':
            $base = '../student/uploads/';
            $imagePath = $base . 'profile/' . ($row['sender_image'] ?: 'default-avatar.png');
            break;
        default:
            $imagePath = 'assets/img/default-avatar.png';
    }

    $messages[] = [
        'id' => (int)$row['id'],
        'sender_id' => (int)$row['sender_id'],
        'sender_designation' => $row['sender_designation'],
        'receiver_id' => (int)$row['receiver_id'],
        'receiver_designation' => $row['receiver_designation'],
        'message' => $row['message'],
        'file_attachment' => !empty($row['file_attachment']) ? $base . 'chat_files/' . $row['file_attachment'] : null,
        'voice_note' => !empty($row['voice_note']) ? $base . 'voice_notes/' . $row['voice_note'] : null,
        'sender_image' => $imagePath,
        'sent_at' => $row['sent_at'],
        'time_ago' => timeAgo($row['sent_at'])
    ];
}

echo json_encode([
    'status' => 'success',
    'count' => count($messages),
    'data' => $messages
]);

$stmt->close();
$conn->close();


// ✅ Helper: Time Ago
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return "$diff seconds ago";
    elseif ($diff < 3600) return floor($diff / 60) . " minutes ago";
    elseif ($diff < 86400) return floor($diff / 3600) . " hours ago";
    return floor($diff / 86400) . " days ago";
}
?>