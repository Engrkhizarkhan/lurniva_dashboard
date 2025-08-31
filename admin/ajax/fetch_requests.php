<?php
session_start();
header('Content-Type: text/html');

require '../sass/db_config.php';

$admin_id = $_SESSION['admin_id'];

$sql = "SELECT * FROM meeting_requests WHERE school_id = '$admin_id'";
$res = $conn->query($sql);

if ($res->num_rows > 0) {
  while ($row = $res->fetch_assoc()) {
    echo "<div style='border:1px solid #ccc; margin:10px; padding:10px;'>
      <strong>Title:</strong> {$row['title']} <br>
      <strong>Agenda:</strong> {$row['agenda']} <br>
      <strong>Status:</strong> {$row['status']} <br>";
    if ($row['status'] == 'pending') {
      echo "<button class='accept-btn' data-id='{$row['id']}'>Accept</button>
      <button class='reject-btn' data-id='{$row['id']}'>Reject</button>";
    } else {
      echo "<em>Already {$row['status']}</em>";
    }
    echo "</div>";
  }
} else {
  echo "No meeting requests found.";
}
?>