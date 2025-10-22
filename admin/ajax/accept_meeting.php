<?php
require_once '../sass/db_config.php';

// Collect input
$request_id      = intval($_POST['request_id'] ?? 0);
$title           = trim($_POST['title'] ?? '');
$agenda          = trim($_POST['agenda'] ?? '');
$meeting_date    = $_POST['meeting_date'] ?? '';
$meeting_time    = $_POST['meeting_time'] ?? '';
$person_one      = intval($_POST['person_one'] ?? 0);
$person_two      = intval($_POST['person_two'] ?? 0);
$meeting_person  = strtolower(trim($_POST['meeting_person'] ?? ''));
$meeting_person2 = strtolower(trim($_POST['meeting_person2'] ?? ''));

// Validation
if (
    !$request_id || !$title || !$agenda ||
    !$meeting_date || !$meeting_time ||
    !$meeting_person || !$meeting_person2
) {
    echo "Missing required fields.";
    exit;
}

// Get school_id from meeting_requests
$getSchool = mysqli_query($conn, "SELECT school_id FROM meeting_requests WHERE id = '$request_id'");
if (mysqli_num_rows($getSchool) == 0) {
    echo "Invalid meeting request ID.";
    exit;
}

$row = mysqli_fetch_assoc($getSchool);
$school_id = $row['school_id'];

// Insert into meeting_announcements
$sql = mysqli_query($conn, "
    INSERT INTO meeting_announcements 
    (school_id, title, meeting_agenda, meeting_date, meeting_time, meeting_person, person_id_one, meeting_person2, person_id_two, status, created_at)
    VALUES (
        '$school_id',
        '$title',
        '$agenda',
        '$meeting_date',
        '$meeting_time',
        '$meeting_person',
        '$person_one',
        '$meeting_person2',
        '$person_two',
        'scheduled',
        NOW()
    )
");

if ($sql) {
    // Update request status
    mysqli_query($conn, "UPDATE meeting_requests SET status='approved' WHERE id='$request_id'");
    echo "✅ Meeting Scheduled Successfully!";
} else {
    echo "❌ Database Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>