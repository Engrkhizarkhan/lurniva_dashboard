<?php
require_once '../sass/db_config.php';

$request_id = $_POST['request_id'];
$title = $_POST['title'];
$agenda = $_POST['agenda'];
$meeting_date = $_POST['meeting_date'];
$meeting_time = $_POST['meeting_time'];
$person_one = $_POST['person_one'];
$person_two = $_POST['person_two'];

// Insert into announcements
$sql = "INSERT INTO meeting_announcements 
        (school_id, title, meeting_agenda, meeting_date, meeting_time, person_id_one, person_id_two, status, created_at)
        SELECT school_id, '$title', '$agenda', '$meeting_date', '$meeting_time', '$person_one', '$person_two', 'scheduled', NOW()
        FROM meeting_requests WHERE id = '$request_id'";

if(mysqli_query($conn, $sql)) {
    mysqli_query($conn, "UPDATE meeting_requests SET status='approved' WHERE id='$request_id'");
    echo "Meeting Scheduled Successfully!";
} else {
    echo "Error: " . mysqli_error($conn);
}