<?php
session_start();
require_once '../admin/sass/db_config.php'; // ✅ Use correct DB path for API
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Allow both session & POST/JSON-based access
$data = json_decode(file_get_contents("php://input"), true);

$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? 0);
$school_id  = intval($_SESSION['school_id'] ?? $data['school_id'] ?? 0);

if (!$student_id || !$school_id) {
    echo json_encode(["status" => "error", "message" => "Session or data missing."]);
    exit;
}

// ✅ Fetch all meetings for this student
$sql = "
SELECT id, title, meeting_agenda, meeting_date, meeting_time, meeting_person, 
       person_id_one, meeting_person2, person_id_two, status, created_at
FROM meeting_announcements
WHERE school_id = ?
  AND (person_id_one = ? OR person_id_two = ?)
ORDER BY meeting_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $school_id, $student_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();

$meetings = [];
while ($row = $result->fetch_assoc()) {
    $meetings[] = [
        "id"              => (int)$row['id'],
        "title"           => $row['title'],
        "agenda"          => $row['meeting_agenda'],
        "date"            => $row['meeting_date'],
        "time"            => $row['meeting_time'],
        "with_person1"    => $row['meeting_person'],
        "person_id_one"   => (int)$row['person_id_one'],
        "with_person2"    => $row['meeting_person2'],
        "person_id_two"   => (int)$row['person_id_two'],
        "status"          => $row['status'],
        "created_at"      => $row['created_at']
    ];
}

if (empty($meetings)) {
    echo json_encode([
        "status"  => "success",
        "message" => "No meetings found",
        "data"    => []
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "count"  => count($meetings),
        "data"   => $meetings
    ]);
}

$stmt->close();
$conn->close();
?>