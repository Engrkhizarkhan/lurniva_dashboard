<?php
require_once '../admin/sass/db_config.php';

// ✅ Allow Flutter/Postman CORS
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Decode incoming JSON body
$data = json_decode(file_get_contents("php://input"), true);

$school_id     = intval($data['school_id'] ?? 0);
$assignment_id = intval($data['assignment_id'] ?? 0);
$results       = $data['results'] ?? []; // array of students

if (!$school_id || !$assignment_id || empty($results)) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required parameters (school_id, assignment_id, or results)"
    ]);
    exit;
}

$upload_dir = "../uploads/results/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$success = 0;
$errors = [];

foreach ($results as $res) {
    $student_id = intval($res['student_id'] ?? 0);
    $marks      = floatval($res['marks_obtained'] ?? 0);
    $remarks    = trim($res['remarks'] ?? '');
    $attachment = '';

    // ✅ Handle base64 attachment if provided
    if (!empty($res['attachment_base64'])) {
        $decoded = base64_decode($res['attachment_base64']);
        if ($decoded !== false) {
            $filename = "result_" . time() . "_{$student_id}.jpg";
            $file_path = $upload_dir . $filename;
            if (file_put_contents($file_path, $decoded)) {
                $attachment = $filename;
            }
        }
    }

    if ($student_id && $marks >= 0) {
        $stmt = $conn->prepare("
            INSERT INTO student_results 
                (school_id, assignment_id, student_id, marks_obtained, remarks, attachment, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiiiss", $school_id, $assignment_id, $student_id, $marks, $remarks, $attachment);

        if ($stmt->execute()) {
            $success++;
        } else {
            $errors[] = [
                "student_id" => $student_id,
                "error" => $stmt->error
            ];
        }
    } else {
        $errors[] = [
            "student_id" => $student_id,
            "error" => "Missing or invalid student_id/marks"
        ];
    }
}

if ($success > 0) {
    echo json_encode([
        "status" => "success",
        "message" => "$success results submitted successfully.",
        "failed" => $errors
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No results saved.",
        "details" => $errors
    ]);
}
?>