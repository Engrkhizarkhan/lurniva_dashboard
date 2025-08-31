<?php
session_start();
header('Content-Type: text/html');

require '../sass/db_config.php';

$id = $_POST['id'];
$action = $_POST['action'];

$req = $conn->query("SELECT * FROM meeting_requests WHERE id = '$id'")->fetch_assoc();

if ($action == 'accept') {
  $meeting_date = $_POST['meeting_date'];
  $meeting_time = $_POST['meeting_time'];

  $stmt = $conn->prepare("INSERT INTO meeting_announcements 
    (school_id, title, meeting_agenda, meeting_date, meeting_time, meeting_person, person_id_one, meeting_person2, person_id_two, status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')");
  $stmt->bind_param("sssssssss",
    $req['school_id'],
    $req['title'],
    $req['agenda'],
    $meeting_date,
    $meeting_time,
    $req['requested_by'],
    $req['requester_id'],
    $req['with_meeting'],
    $req['id_meeter']
  );
  $stmt->execute();

  $conn->query("UPDATE meeting_requests SET status = 'accepted' WHERE id = '$id'");

  echo "Accepted and scheduled!";
} else {
  $conn->query("UPDATE meeting_requests SET status = 'rejected' WHERE id = '$id'");
  echo "Rejected!";
}
?>