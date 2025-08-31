<?php
session_start();
require_once '../sass/db_config.php';

$admin_id = $_SESSION['admin_id'] ?? null;

if (!isset($_SESSION['admin_id'])) {
    exit; // prevent unauthorized access
}

$admin_id = $_SESSION['admin_id'];

$stmt = $conn->prepare("
    SELECT 
    m.*,
    CASE 
        WHEN m.sender_designation IN ('student') THEN s.full_name
        WHEN m.sender_designation IN ('faculty', 'teacher') THEN f.full_name
        WHEN m.sender_designation IN ('school') THEN sch.school_name
        ELSE 'Unknown'
    END AS sender_name
FROM messages m
LEFT JOIN students s ON m.sender_designation = 'student' AND m.sender_id = s.id
LEFT JOIN faculty f ON m.sender_designation IN ('faculty', 'teacher') AND m.sender_id = f.id
LEFT JOIN schools sch ON m.sender_designation = 'school' AND m.sender_id = sch.id
WHERE m.receiver_designation = 'admin' AND m.receiver_id = ? AND m.status='unread'
ORDER BY m.sent_at DESC

");


$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = '';

while ($row = $result->fetch_assoc()) {
    $senderName = htmlspecialchars($row['sender_name'] ?? 'Unknown');
    $messageText = htmlspecialchars($row['message']);
    $sentAt = $row['sent_at'];
    $timeAgo = timeAgo($sentAt);
$messages .= '
<a href="#" 
   class="dropdown-item open-chat" 
   data-sender-id="' . $row['sender_id'] . '" 
   data-sender-designation="' . $row['sender_designation'] . '" 
   data-sender-name="' . htmlspecialchars($senderName) . '">
    <span class="dropdown-item-avatar text-white">
        <img alt="image" src="assets/img/users/user-1.png" class="rounded-circle">
    </span>
    <span class="dropdown-item-desc">
        <span class="message-user">' . htmlspecialchars($senderName) . '</span>
        <span class="time messege-text">' . htmlspecialchars($messageText) . '</span>
        <span class="time">' . htmlspecialchars($timeAgo) . '</span>
    </span>
</a>';

}

echo $messages;

// Helper function
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return "$diff seconds ago";
    elseif ($diff < 3600) return floor($diff / 60) . " minutes ago";
    elseif ($diff < 86400) return floor($diff / 3600) . " hours ago";
    else return floor($diff / 86400) . " days ago";
}
?>