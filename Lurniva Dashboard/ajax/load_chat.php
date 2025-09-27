<?php
session_start();
require_once '../sass/db_config.php';

$receiver_id = $_SESSION['admin_id'];
$sender_id = $_POST['sender_id'];
$sender_designation = $_POST['sender_designation'];

$sql = "SELECT 
    m.*, 
    COALESCE(
        IF(LOWER(m.sender_designation) = 'student', s.profile_photo, NULL),
        IF(LOWER(m.sender_designation) = 'teacher', f.photo, NULL),
        IF(LOWER(m.sender_designation) = 'admin', sch.logo, NULL)
    ) AS sender_image
FROM messages m
LEFT JOIN students s 
    ON LOWER(m.sender_designation) = 'student' AND m.sender_id = s.id
LEFT JOIN faculty f 
    ON LOWER(m.sender_designation) = 'teacher' AND m.sender_id = f.id
LEFT JOIN schools sch 
    ON LOWER(m.sender_designation) = 'admin' AND m.sender_id = sch.id
WHERE 
(
    (m.sender_id = $sender_id AND m.sender_designation = '$sender_designation' AND m.receiver_id = $receiver_id AND m.receiver_designation = 'admin') 
    OR 
    (m.receiver_id = $sender_id AND m.receiver_designation = '$sender_designation' AND m.sender_id = $receiver_id AND m.sender_designation = 'admin')
)
ORDER BY m.sent_at ASC;";

// Execute and check for errors
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query Error: " . mysqli_error($conn));
}

while ($row = mysqli_fetch_assoc($result)) {
    $isAdmin = strtolower($row['sender_designation']) === 'admin';
  // Decide image path based on sender type
    if ($row['sender_designation'] === 'teacher') {
        $path = '../Faculty Dashboard/uploads/'; 
        $imagePath = $path.'profile/'. $row['sender_image']; 
    } elseif ($row['sender_designation'] === 'admin') {
        $path = 'uploads/'; 
        $imagePath = $path.'logos/'. $row['sender_image']; 
    } elseif ($row['sender_designation'] === 'student') {
        $path = '../student/uploads/';
        $imagePath = $path.'profile/'. $row['sender_image'];
    } else {
        $imagePath = 'assets/img/default-avatar.png'; // fallback
    }

    // If empty in DB, fallback to default
    if (empty($row['sender_image'])) {
        $imagePath = 'assets/img/default-avatar.png';
    }
    // Styles
    $wrapperStyle = $isAdmin ? 'justify-content: flex-end;' : 'justify-content: flex-start;';
    $messageBg = $isAdmin ? '#007bff' : '#e0e0e0';
    $textColor = $isAdmin ? 'white' : 'black';

    echo '<div style="display: flex; ' . $wrapperStyle . ' margin-bottom: 10px;">
            <div style="display: flex; align-items: flex-end; ' . ($isAdmin ? 'flex-direction: row-reverse;' : '') . '">
                <img src="'. $imagePath . '" alt="profile" style="width: 32px; height: 32px; border-radius: 50%; margin: 0 8px;">
                <div>
                    <div style="
                        background:' . $messageBg . ';
                        color:' . $textColor . ';
                        padding: 10px 14px;
                        border-radius: 10px;
                        max-width: 260px;
                        word-wrap: break-word;
                    ">';

    // Text message
    if (!empty($row['message'])) {
        echo '<div>' . nl2br(htmlspecialchars($row['message'])) . '</div>';
    }

    // File
    if (!empty($row['file_attachment'])) {
        $fileUrl = $path.'chat_files/' . $row['file_attachment'];
        echo '<div style="margin-top: 5px;">
                <a href="' . $fileUrl . '" target="_blank" download style="color:' . $textColor . '; text-decoration: underline;">
                    📎 Download Attachment
                </a>
              </div>';
    }

    // Voice
    if (!empty($row['voice_note'])) {
        $voiceUrl = $path.'voice_notes/' . $row['voice_note'];
        echo '<div style="margin-top: 5px;">
                <audio controls style="width: 100%;">
                    <source src="' . $voiceUrl . '" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
              </div>';
    }

    // Timestamp
    echo '      </div>
                <div style="font-size: 11px; color: #666; margin-top: 4px; text-align: ' . ($isAdmin ? 'right' : 'left') . ';">' . 
                    date('d M, h:i A', strtotime($row['sent_at'])) . 
         '</div>
            </div>
        </div>
    </div>';
}
?>