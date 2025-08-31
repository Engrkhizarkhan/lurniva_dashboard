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
            WHEN m.sender_designation = 'student' THEN s.full_name
            WHEN m.sender_designation IN ('faculty', 'teacher') THEN f.full_name
            WHEN m.sender_designation = 'admin' THEN sch.school_name
            ELSE 'Unknown'
        END AS sender_name,
        CASE 
            WHEN m.sender_designation = 'student' THEN s.profile_photo
            WHEN m.sender_designation IN ('faculty', 'teacher') THEN f.photo
            WHEN m.sender_designation = 'admin' THEN sch.logo
            ELSE 'assets/img/default-avatar.png'
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

$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = '';

while ($row = $result->fetch_assoc()) {
    $senderName = htmlspecialchars($row['sender_name'] ?? 'Unknown');
    $messageText = htmlspecialchars($row['message']);
    $sentAt = $row['sent_at'];
    $timeAgo = timeAgo($sentAt);

    // Decide image path based on sender type
    if ($row['sender_designation'] === 'admin') {
        $imagePath = '../admin/uploads/logos/' . $row['sender_image']; 
    } elseif (in_array($row['sender_designation'], ['faculty', 'teacher'])) {
        $imagePath = 'uploads/profile/' . $row['sender_image']; 
    } elseif ($row['sender_designation'] === 'student') {
        $imagePath = '../student/uploads/profile/' . $row['sender_image']; 
    } else {
        $imagePath = 'assets/img/default-avatar.png'; // fallback
    }

    // If empty in DB, fallback to default
    if (empty($row['sender_image'])) {
        $imagePath = 'assets/img/default-avatar.png';
    }

    $messages .= '
    <a href="#" 
       class="dropdown-item open-chat" 
       data-sender-id="' . $row['sender_id'] . '" 
       data-sender-designation="' . $row['sender_designation'] . '" 
       data-sender-name="' . htmlspecialchars($senderName) . '">
        <span class="dropdown-item-avatar text-white">
            <img alt="image" src="' . htmlspecialchars($imagePath) . '" class="rounded-circle">
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