<?php
require '../sass/db_config.php';
session_start();

$school_id  = $_SESSION['school_id'] ?? 0;
$student_id = (int)($_POST['student_id'] ?? 0);  // ✅ POST instead of GET

// If no student selected, stop
if ($student_id === 0) {
    echo json_encode([]);
    exit;
}

// Subject name comes from AJAX
$subject = $_POST['subject'] ?? '';

// If no subject provided, stop
if (empty($subject)) {
    echo json_encode([]);
    exit;
}

// Query: fetch student marks for that subject
$sql = "SELECT ta.due_date AS date, sr.marks_obtained AS value, ta.title AS title
        FROM student_results sr
        JOIN teacher_assignments ta ON sr.assignment_id = ta.id
        WHERE sr.student_id = ? 
          AND ta.subject = ?
          AND ta.school_id = ?
        ORDER BY ta.due_date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("isi", $student_id, $subject, $school_id);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);