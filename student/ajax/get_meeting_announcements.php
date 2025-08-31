<?php
session_start();
require_once '../sass/db_config.php'; // DB connection

$teacher_id = $_SESSION['student_id']; 
$school_id  = $_SESSION['school_id'];

// Fetch meetings related to this teacher
$sql = "SELECT id, title, meeting_agenda, meeting_date, meeting_time, meeting_person, 
               person_id_one, meeting_person2, person_id_two, status, created_at
        FROM meeting_announcements
        WHERE school_id = ?
        AND (person_id_one = ? OR person_id_two = ?)
        ORDER BY meeting_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $school_id, $teacher_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

$output = "";
while ($row = $result->fetch_assoc()) {
    $statusClass = "";
    if ($row['status'] === 'rejected') {
        $statusClass = "style='background-color:#f8d7da;'";
    }

    $tooltip = htmlspecialchars($row['status']);

    $output .= "<tr {$statusClass} data-toggle='tooltip' data-placement='top' title='{$tooltip}'>
        <td>{$row['title']}</td>
        <td>{$row['meeting_agenda']}</td>
        <td>{$row['meeting_date']} {$row['meeting_time']}</td>
        <td>{$row['meeting_person']} - {$row['meeting_person2']}</td>
        <td>{$row['status']}</td>
    </tr>";
}

echo $output ?: "<tr><td colspan='5' class='text-center'>No meetings found</td></tr>";