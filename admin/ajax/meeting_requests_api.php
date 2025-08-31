<?php
header('Content-Type: application/json');
require_once '../sass/db_config.php'; // adjust path if needed

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');


// Read JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
  echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
  exit();
}

$action = isset($data['action']) ? $data['action'] : '';

switch ($action) {
  case 'insert':
    $school_id = $data['school_id'];
    $requested_by = $data['requested_by'];
    $requester_id = $data['requester_id'];
    $with_meeting = $data['with_meeting'];
    $id_meeter = $data['id_meeter'];
    $title = $data['title'];
    $agenda = $data['agenda'];
    $status = $data['status'];

    $sql = "INSERT INTO meeting_requests (school_id, requested_by, requester_id, with_meeting, id_meeter, title, agenda, status)
            VALUES ('$school_id', '$requested_by', '$requester_id', '$with_meeting', '$id_meeter', '$title', '$agenda', '$status')";
    if ($conn->query($sql) === TRUE) {
      echo json_encode(["status" => "success", "message" => "Meeting request created"]);
    } else {
      echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
    break;

  case 'update':
    $id = $data['id'];
    $requested_by = $data['requested_by'];
    $requester_id = $data['requester_id'];
    $with_meeting = $data['with_meeting'];
    $id_meeter = $data['id_meeter'];
    $title = $data['title'];
    $agenda = $data['agenda'];
    $status = $data['status'];

    $sql = "UPDATE meeting_requests SET requested_by='$requested_by', requester_id='$requester_id',
            with_meeting='$with_meeting', id_meeter='$id_meeter', title='$title',
            agenda='$agenda', status='$status' WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
      echo json_encode(["status" => "success", "message" => "Meeting request updated"]);
    } else {
      echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
    break;

  case 'delete':
    $id = $data['id'];
    $sql = "DELETE FROM meeting_requests WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
      echo json_encode(["status" => "success", "message" => "Meeting request deleted"]);
    } else {
      echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
    break;

  case 'getAll':
    $school_id = $data['school_id'];
    $sql = "SELECT * FROM meeting_requests WHERE school_id='$school_id'";
    $result = $conn->query($sql);
    $meetings = [];
    while ($row = $result->fetch_assoc()) {
      $meetings[] = $row;
    }
    echo json_encode($meetings);
    break;

  case 'get':
    $id = $data['id'];
    $sql = "SELECT * FROM meeting_requests WHERE id='$id'";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
      echo json_encode($row);
    } else {
      echo json_encode(["status" => "error", "message" => "Meeting request not found"]);
    }
    break;

  default:
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
}

$conn->close();