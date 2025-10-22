<?php
require_once '../admin/sass/db_config.php'; // adjust path if needed

header('Content-Type: application/json; charset=UTF-8');

// ✅ Get student_id from POST/JSON
$data = json_decode(file_get_contents("php://input"), true);
$student_id = intval($data['student_id'] ?? $_POST['student_id'] ?? 0);

if ($student_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing or invalid student_id']);
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
    WHERE m.receiver_designation = 'student' 
      AND m.receiver_id = ? 
      AND m.status = 'unread'
    ORDER BY m.sent_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    // ✅ Build image path based on sender type
    switch ($row['sender_designation']) {
        case 'admin':
            $imagePath = '../admin/uploads/logos/' . ($row['sender_image'] ?: 'default-avatar.png');
            break;
        case 'faculty':
        case 'teacher':
            $imagePath = '../Faculty Dashboard/uploads/profile/' . ($row['sender_image'] ?: 'default-avatar.png');
            break;
        case 'student':
            $imagePath = '../student/uploads/profile/' . ($row['sender_image'] ?: 'default-avatar.png');
            break;
        default:
            $imagePath = 'assets/img/default-avatar.png';
    }

    $messages[] = [
        'id' => (int)$row['id'],
        'sender_id' => (int)$row['sender_id'],
        'sender_designation' => $row['sender_designation'],
        'sender_name' => $row['sender_name'],
        'message' => $row['message'],
        'sent_at' => $row['sent_at'],
        'time_ago' => timeAgo($row['sent_at']),
        'sender_image' => $imagePath
    ];
}

echo json_encode([
    'status' => 'success',
    'count' => count($messages),
    'data' => $messages
]);

$stmt->close();
$conn->close();

// ✅ Helper function
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    if ($diff < 60) return "$diff seconds ago";
    elseif ($diff < 3600) return floor($diff / 60) . " minutes ago";
    elseif ($diff < 86400) return floor($diff / 3600) . " hours ago";
    else return floor($diff / 86400) . " days ago";
}
?>