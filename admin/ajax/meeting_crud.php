<?php
session_start();
require_once '../sass/db_config.php';

$action = $_POST['action'];

if ($action == "insert") {
$stmt = $conn->prepare("
    INSERT INTO meeting_announcements 
    (school_id, title, meeting_agenda, meeting_date, meeting_time, meeting_person, person_id_one, meeting_person2, person_id_two)
    VALUES (?,?,?,?,?,?,?,?,?)
");

$stmt->bind_param(
    "issssssss",
    $_POST['school_id'],
    $_POST['title'],
    $_POST['meeting_agenda'],
    $_POST['meeting_date'],
    $_POST['meeting_time'],
    $_POST['meeting_person'],
    $_POST['person_id_one'],
    $_POST['meeting_person2'],
    $_POST['person_id_two']
);

  $stmt->execute();
  echo "Meeting Added!";
}

if ($action == "getAll") {
  $admin_id = $_SESSION['admin_id'];
  $result = $conn->query("SELECT * FROM meeting_announcements WHERE school_id = $admin_id");

  if ($result->num_rows > 0) {
    echo "<table class='table table-bordered'>
      <thead>
        <tr>
          <th>Title</th>
          <th>Agenda</th>
          <th>Date</th>
          <th>Time</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>";
    while($row = $result->fetch_assoc()){
      echo "<tr>
        <td>{$row['title']}</td>
        <td>{$row['meeting_agenda']}</td>
        <td>{$row['meeting_date']}</td>
        <td>{$row['meeting_time']}</td>
        <td>{$row['status']}</td>
        <td>
          <button class='btn btn-sm btn-warning editMeeting' data-id='{$row['id']}'>Edit</button>
          <button class='btn btn-sm btn-danger deleteMeeting' data-id='{$row['id']}'>Delete</button>
        </td>
      </tr>";
    }
    echo "</tbody></table>";
  } else {
    echo "<p>No meetings found.</p>";
  }
}


if ($action == "getOne") {
  $id = $_POST['id'];
  $result = $conn->query("SELECT * FROM meeting_announcements WHERE id = $id");
  echo json_encode($result->fetch_assoc());
}

if ($action == "update") {
$stmt = $conn->prepare("
    UPDATE meeting_announcements 
    SET 
        title = ?, 
        meeting_agenda = ?, 
        meeting_date = ?, 
        meeting_time = ?, 
        meeting_person = ?, 
        person_id_one = ?, 
        meeting_person2 = ?, 
        person_id_two = ? 
    WHERE id = ?
");

$stmt->bind_param(
    "ssssssssi",
    $_POST['title'],
    $_POST['meeting_agenda'],
    $_POST['meeting_date'],
    $_POST['meeting_time'],
    $_POST['meeting_person'],
    $_POST['person_id_one'],
    $_POST['meeting_person2'],
    $_POST['person_id_two'],
    $_POST['id']
);

  $stmt->execute();
  echo "Meeting Updated!";
}

if ($action == "delete") {
  $id = $_POST['id'];
  $conn->query("DELETE FROM meeting_announcements WHERE id = $id");
  echo "Meeting Deleted!";
}