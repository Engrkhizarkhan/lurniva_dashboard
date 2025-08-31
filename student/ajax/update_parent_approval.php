<?php
session_start();
require_once '../sass/db_config.php';

$student_id = $_SESSION['student_id'] ?? 0;
$diary_id   = intval($_POST['diary_id'] ?? 0);

if (!$student_id || !$diary_id) {
    echo "Invalid request.";
    exit;
}

// First, check if a record exists for this student and diary
$sqlCheck = "SELECT id FROM diary_students WHERE diary_id = ? AND student_id = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("ii", $diary_id, $student_id);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();

if ($resCheck->num_rows > 0) {
    // Update existing
    $sqlUpdate = "UPDATE diary_students SET approve_parent = 'approved' WHERE diary_id = ? AND student_id = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ii", $diary_id, $student_id);
    $stmtUpdate->execute();

    if ($stmtUpdate->affected_rows > 0) {
        echo "success";
    } else {
        echo "no_change"; // Record exists but already approved
    }
    $stmtUpdate->close();
} else {
    // Insert new
    $sqlInsert = "INSERT INTO diary_students (diary_id, student_id, approve_parent) VALUES (?, ?, 'approved')";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("ii", $diary_id, $student_id);
    if ($stmtInsert->execute()) {
        echo "success";
    } else {
        echo "failed";
    }
    $stmtInsert->close();
}

$stmtCheck->close();
$conn->close();
?>