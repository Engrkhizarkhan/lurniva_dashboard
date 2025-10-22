<?php
require_once '../sass/db_config.php';

// Collect input safely
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
if (!$request_id || !$title || !$agenda || !$meeting_date || !$meeting_time || !$meeting_person || !$meeting_person2) {
    echo "Missing required fields.";
    exit;
}

// Validate ENUM values
$valid_roles = ['admin', 'teacher', 'parent'];
if (!in_array($meeting_person, $valid_roles) || !in_array($meeting_person2, $valid_roles)) {
    echo "Invalid meeting person role(s). Allowed: admin, teacher, parent.";
    exit;
}

// Get school_id from meeting_requests
$stmt = $conn->prepare("SELECT school_id FROM meeting_requests WHERE id = ?");
$stmt->bind_param("i", $request_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "Invalid meeting request ID.";
    exit;
}

$school_id = $res->fetch_assoc()['school_id'];
$stmt->close();

// Prepare INSERT
$stmtInsert = $conn->prepare("
    INSERT INTO meeting_announcements 
    (school_id, title, meeting_agenda, meeting_date, meeting_time, meeting_person, person_id_one, meeting_person2, person_id_two, status, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled', NOW())
");

// 9 parameters → "issssisis"
$stmtInsert->bind_param(
    "issssisis",
    $school_id,
    $title,
    $agenda,
    $meeting_date,
    $meeting_time,
    $meeting_person,
    $person_one,
    $meeting_person2,
    $person_two
);

// Execute insert
if ($stmtInsert->execute()) {
    // Update meeting_requests
    $stmtUpdate = $conn->prepare("UPDATE meeting_requests SET status='approved' WHERE id=?");
    $stmtUpdate->bind_param("i", $request_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

    echo "Meeting Scheduled Successfully!";
} else {
    echo "Database Error: " . $stmtInsert->error;
}

// Cleanup
$stmtInsert->close();
$conn->close();
?>