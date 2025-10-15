<?php
require_once '../admin/sass/db_config.php'; // adjust path if needed


// --- ✅ Enable CORS for Flutter/Postman ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

$school_id  = intval($data['school_id'] ?? 0);
$student_id = intval($data['student_id'] ?? 0);

$response = ["status" => "error", "message" => "Student not found."];

if ($school_id > 0 && $student_id > 0) {
    // 1️⃣ Get student info
    $stmt = $conn->prepare("SELECT * FROM students WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $student_id, $school_id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();

    if ($student) {
        // 2️⃣ Get class info
        $stmt = $conn->prepare("
            SELECT * FROM class_timetable_meta 
            WHERE class_name=? AND section=? AND school_id=? LIMIT 1
        ");
        $stmt->bind_param("ssi", $student['class_grade'], $student['section'], $school_id);
        $stmt->execute();
        $class = $stmt->get_result()->fetch_assoc();

        // 3️⃣ Get class teacher
        $teacher_name = '';
        if ($class) {
            $stmt = $conn->prepare("
                SELECT f.full_name 
                FROM class_timetable_details d 
                JOIN faculty f ON d.teacher_id = f.id 
                WHERE d.timing_meta_id = ? LIMIT 1
            ");
            $stmt->bind_param("i", $class['id']);
            $stmt->execute();
            $tRow = $stmt->get_result()->fetch_assoc();
            $teacher_name = $tRow['full_name'] ?? '';
        }

        // 4️⃣ Get subjects taught in this class
        $subjects = [];
        if (!empty($class['id'])) {
            $stmt = $conn->prepare("
                SELECT d.id, d.period_name AS subject_name, 
                       f.full_name AS teacher_name, f.rating
                FROM class_timetable_details d
                JOIN faculty f ON d.teacher_id = f.id
                WHERE d.timing_meta_id = ? AND d.period_type <> 'Break'
            ");
            $stmt->bind_param("i", $class['id']);
            $stmt->execute();
            $subjects = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // 5️⃣ Get performance by subject (average marks %)
        $performance_sql = "
            SELECT ta.subject,
                   ROUND((SUM(sr.marks_obtained)/SUM(ta.total_marks))*100,2) AS percentage
            FROM student_results sr
            JOIN teacher_assignments ta ON sr.assignment_id = ta.id
            WHERE sr.student_id=$student_id AND sr.school_id=$school_id
            GROUP BY ta.subject
        ";
        $performance = [];
        $res_perf = $conn->query($performance_sql);
        while ($row = $res_perf->fetch_assoc()) {
            $performance[] = [
                "subject" => $row['subject'],
                "marks"   => (float)$row['percentage']
            ];
        }

        // ✅ Build success response
        $response = [
            "status"      => "success",
            "student"     => $student,
            "class"       => $class,
            "class_teacher" => $teacher_name,
            "subjects"    => $subjects,
            "performance" => $performance
        ];
    }
}

// ✅ Output JSON
echo json_encode($response, JSON_PRETTY_PRINT);
?>