<?php
session_start();
require_once '../sass/db_config.php'; // DB connection

$teacher_id = $_SESSION['student_id']; 
$school_id  = $_SESSION['school_id']; 
$action     = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'insert') {
    $with_meeting = $_POST['with_meeting'];
    $id_meeter    = $_POST['id_meeter'];
    $title        = $_POST['title'];
    $agenda       = $_POST['agenda'];

    $stmt = $conn->prepare("INSERT INTO meeting_requests (school_id, requested_by, requester_id, with_meeting, id_meeter, title, agenda, created_at) VALUES (?, 'parent', ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iissss", $school_id, $teacher_id, $with_meeting, $id_meeter, $title, $agenda);

    echo $stmt->execute() ? "Meeting request added successfully." : "Error: " . $stmt->error;
    $stmt->close();
}

// Update meeting
elseif ($action === 'update') {
    $id           = $_POST['id'];
    $with_meeting = $_POST['with_meeting'];
    $id_meeter    = $_POST['id_meeter'];
    $title        = $_POST['title'];
    $agenda       = $_POST['agenda'];

    $stmt = $conn->prepare("UPDATE meeting_requests SET with_meeting=?, id_meeter=?, title=?, agenda=? WHERE id=? AND school_id=?");
    $stmt->bind_param("sissii", $with_meeting, $id_meeter, $title, $agenda, $id, $school_id);

    echo $stmt->execute() ? "Meeting updated successfully." : "Error: " . $stmt->error;
    $stmt->close();
}

// Delete meeting
elseif ($action === 'delete') {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM meeting_requests WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $id, $school_id);

    echo $stmt->execute() ? "Meeting deleted successfully." : "Error: " . $stmt->error;
    $stmt->close();
}

// Fetch all meetings
elseif ($action === 'fetch') {
    $result = $conn->query("SELECT id, with_meeting, id_meeter, title,status, agenda FROM meeting_requests WHERE school_id='$school_id' AND requested_by='parent' AND requester_id=$teacher_id AND status!='approved' ORDER BY id DESC");
while ($row = $result->fetch_assoc()) {
    $with = ucfirst($row['with_meeting']);
    $tooltip = htmlspecialchars($row['status']); // escape for safety
    $rowClass = ($row['status'] === 'rejected') ? "style='background-color: #f8d7da;'" : "";

    echo "<tr {$rowClass} data-toggle='tooltip' data-placement='top' title='{$tooltip}'>
        <td>{$row['title']}</td>
        <td>{$row['agenda']}</td>
        <td>{$with}</td>
        <td>
            <button class='btn btn-sm btn-info edit-meeting' data-id='{$row['id']}'>Edit</button>
            <button class='btn btn-sm btn-danger delete-meeting' data-id='{$row['id']}'>Delete</button>
        </td>
    </tr>";
}


}

// Get single meeting
elseif ($action === 'get') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM meeting_requests WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
    $stmt->close();
}

$conn->close();
?>