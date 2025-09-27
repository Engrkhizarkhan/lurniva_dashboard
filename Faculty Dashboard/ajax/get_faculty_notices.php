<?php
session_start();
require_once '../sass/db_config.php';

if (!isset($_SESSION['admin_id']) && !isset($_SESSION['faculty_id'])) {
    echo "<div class='alert alert-danger'>Unauthorized</div>";
    exit;
}

$school_id = $_SESSION['campus_id'] ;

$stmt = $conn->prepare("
    SELECT id, title, notice_date, expiry_date, issued_by, purpose, notice_type, audience, file_path, created_at
    FROM digital_notices
    WHERE school_id = ? 
    AND (audience = 'Faculty' OR audience = 'Everyone')
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "<div class='alert alert-info'>No notices available.</div>";
    exit;
}

while($row = $result->fetch_assoc()){
    $fileLink = $row['file_path'] ? "<a href='../uploads/notices/{$row['file_path']}' target='_blank'>View Attachment</a>" : "";
    echo "
    <div class='notice-card'>
        <div class='notice-title'>{$row['title']}</div>
        <div class='notice-meta'>
            <b>Date:</b> {$row['notice_date']} | 
            <b>Valid Till:</b> {$row['expiry_date']} | 
            <b>Issued By:</b> {$row['issued_by']} | 
            <b>Type:</b> {$row['notice_type']}
        </div>
        <div class='notice-purpose'>{$row['purpose']}</div>
        <div class='mt-2'>$fileLink</div>
    </div>
    ";
}