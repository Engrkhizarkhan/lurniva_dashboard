<?php
require '../sass/db_config.php';
session_start();

$school_id  = $_SESSION['campus_id'] ?? 0;
$student_id = (int)($_POST['id'] ?? 0);  // ✅ POST instead of GET

$response = ["status" => "error", "message" => "Student not found"]; // ✅ use "status"

if ($student_id && $school_id) {
    // 1. Student info
    $stmt = $conn->prepare("SELECT * FROM students WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $student_id, $school_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if ($student) {
        // 2. Class info
        $stmt = $conn->prepare("SELECT * FROM class_timetable_meta WHERE class_name=? AND section=? AND school_id=?");
        $stmt->bind_param("ssi", $student['class_grade'], $student['section'], $school_id);
        $stmt->execute();
        $class = $stmt->get_result()->fetch_assoc();

        $teacher_name = '';
        if ($class) {
            $stmt = $conn->prepare("SELECT f.full_name 
                                    FROM class_timetable_details d 
                                    JOIN faculty f ON d.teacher_id = f.id 
                                    WHERE d.timing_meta_id = ? LIMIT 1");
            $stmt->bind_param("i", $class['id']);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $teacher_name = $row['full_name'] ?? '';
        }

        // 3. Subjects
        $subjects = [];
        if (!empty($class['id'])) {
            $stmt = $conn->prepare("SELECT d.id, d.period_name, f.full_name AS teacher_name, f.rating
FROM class_timetable_details d
JOIN faculty f ON d.teacher_id = f.id
WHERE d.timing_meta_id = ?
  AND d.period_type <> 'Break';
");
            $stmt->bind_param("i", $class['id']);
            $stmt->execute();
            $subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // 4. Performance
        $performance_sql = "
            SELECT ta.subject,
                   ROUND((SUM(sr.marks_obtained)/SUM(ta.total_marks))*100,2) AS percentage
            FROM student_results sr
            JOIN teacher_assignments ta ON sr.assignment_id = ta.id
            WHERE sr.student_id=$student_id AND sr.school_id=$school_id
            GROUP BY ta.subject
        ";
        $result = mysqli_query($conn, $performance_sql);
        $performance = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $performance[] = [
                "subject" => $row['subject'],
                "marks"   => (float)$row['percentage'] // ✅ return as "marks"
            ];
        }

        // Response JSON
        $response = [
            "status"     => "success",   // ✅ matches AJAX check
            "student"    => $student,
            "class"      => $class,
            "teacher"    => $teacher_name,
            "subjects"   => $subjects,
            "performance"=> $performance
        ];
    }
}

header("Content-Type: application/json");
echo json_encode($response);