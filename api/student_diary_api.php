<?php
session_start();
require_once '../admin/sass/db_config.php'; // adjust path if needed

header("Content-Type: application/json; charset=UTF-8");

// ✅ Allow both Web (Session) & Mobile (POST/JSON)
$data = json_decode(file_get_contents("php://input"), true);
$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? $_POST['student_id'] ?? 0);
$school_id  = intval($_SESSION['school_id'] ?? $data['school_id'] ?? $_POST['school_id'] ?? 0);

if (!$student_id || !$school_id) {
    echo json_encode(["status" => "error", "message" => "Session missing or invalid parameters."]);
    exit;
}

// ✅ Query all diary entries visible to this student
$sql = "
SELECT 
    de.id AS diary_id,
    de.subject,
    de.topic,
    de.description,
    de.attachment,
    de.deadline,
    de.parent_approval_required,
    de.student_option
FROM diary_entries AS de
LEFT JOIN diary_students AS ds
       ON ds.diary_id = de.id
      AND ds.student_id = ?
WHERE de.school_id = ?
  AND (
        de.student_option = 'all'
        OR (de.student_option = 'specific' AND ds.student_id IS NOT NULL)
      )
ORDER BY de.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $student_id, $school_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode(["status" => "success", "data" => []]);
    exit;
}

$entries = [];
while ($row = $res->fetch_assoc()) {
    $attachmentUrl = "";
    if (!empty($row['attachment'])) {
        // ✅ Use absolute URL for mobile app
        $base = rtrim((isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'], '/');
        $attachmentUrl = "{$base}../Faculty Dashboard/uploads/results/" . rawurlencode($row['attachment']);
    }

    $entries[] = [
        "diary_id"   => (int)$row['diary_id'],
        "subject"    => $row['subject'],
        "topic"      => $row['topic'],
        "description"=> $row['description'],
        "deadline"   => $row['deadline'],
        "attachment" => $attachmentUrl ?: null,
        "approval_required" => ($row['parent_approval_required'] === 'yes')
    ];
}

$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "data" => $entries]);
?>