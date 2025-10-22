<?php
require_once '../admin/sass/db_config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// ✅ Handle preflight (for Flutter)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Get teacher_id from POST or JSON
$data = json_decode(file_get_contents("php://input"), true);
$teacher_id = intval($data['teacher_id'] ?? $_POST['teacher_id'] ?? 0);

if ($teacher_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid teacher_id']);
    exit;
}

// ✅ Fetch unread messages with sender info
$stmt = $conn->prepare("
    SELECT 
        m.id,
        m.sender_id,
        m.sender_designation,
        m.message,
        m.sent_at,
        CASE 
            WHEN m.sender_designation = 'student' THEN s.full_name
            WHEN m.sender_designation IN ('faculty', 'teacher') THEN f.full_name
            WHEN m.sender_designation = 'admin' THEN sch.school_name
            ELSE 'Unknown'
        END AS sender_name,
        CASE 
            WHEN m.sender_designation = 'student' THEN s.profile_photo
            WHEN m.sender_designation IN ('faculty', 'teacher') THEN f.photo
            WHEN m.sender_designation = 'admin' THEN sch.logo
            ELSE 'default-avatar.png'
        END AS sender_image
    FROM messages m
    LEFT JOIN students s 
        ON m.sender_designation = 'student' AND m.sender_id = s.id
    LEFT JOIN faculty f 
        ON m.sender_designation IN ('faculty', 'teacher') AND m.sender_id = f.id
    LEFT JOIN schools sch 
        ON m.sender_designation = 'admin' AND m.sender_id = sch.id
    WHERE m.receiver_designation = 'teacher' 
      AND m.receiver_id = ? 
      AND m.status = 'unread'
    ORDER BY m.sent_at DESC
");

$stmt->bind_param('i', $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $sender_name  = $row['sender_name'] ?? 'Unknown';
    $sender_image = $row['sender_image'] ?? 'default-avatar.png';
    
    // Build image path based on sender type
    switch ($row['sender_designation']) {
        case 'admin':
            $image_path = '../admin/uploads/logos/' . $sender_image;
            break;
        case 'teacher':
        case 'faculty':
            $image_path = '../Faculty Dashboard/uploads/profile/' . $sender_image;
            break;
        case 'student':
            $image_path = '../student/uploads/profile/' . $sender_image;
            break;
        default:
            $image_path = 'assets/img/default-avatar.png';
    }

    if (empty($sender_image)) {
        $image_path = 'assets/img/default-avatar.png';
    }

    $messages[] = [
        'id'                 => (int)$row['id'],
        'sender_id'          => (int)$row['sender_id'],
        'sender_designation' => $row['sender_designation'],
        'sender_name'        => $sender_name,
        'sender_image'       => $image_path,
        'message'            => $row['message'],
        'sent_at'            => $row['sent_at'],
        'time_ago'           => timeAgo($row['sent_at'])
    ];
}

echo json_encode([
    'status' => 'success',
    'count'  => count($messages),
    'data'   => $messages
]);

$stmt->close();
$conn->close();

// Helper function
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return "$diff seconds ago";
    elseif ($diff < 3600) return floor($diff / 60) . " minutes ago";
    elseif ($diff < 86400) return floor($diff / 3600) . " hours ago";
    return floor($diff / 86400) . " days ago";
}
?>