<?php
session_start();
require_once 'sass/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);

    $stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if ($student) {
        // ✅ Store child data in session
        $_SESSION['student_id']     = $student['id'];
        $_SESSION['student_name']   = $student['full_name'];
        $_SESSION['school_id']      = $student['school_id'];
        $_SESSION['student_photo']  = $student['profile_photo'];
        $_SESSION['class_grade']    = $student['class_grade'];
        $_SESSION['section']        = $student['section'];

        header("Location: ../student/index.php");
        exit;
    } else {
        echo "❌ Invalid student.";
    }
} else {
    header("Location: parent.php");
    exit;
}
