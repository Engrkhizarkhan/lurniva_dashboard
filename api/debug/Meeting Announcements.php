<?php
require_once '../../admin/sass/db_config.php'; // adjust path if needed

// --- ✅ Enable CORS for Flutter/Postman ---
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Optional: Allow filtering by school_id
$input = json_decode(file_get_contents("php://input"), true);
if (empty($input) && !empty($_POST)) $input = $_POST;

$school_id = intval($input['school_id'] ?? ($_GET['school_id'] ?? 0));

// ✅ Base query
$query = "SELECT id, school_id, title, meeting_agenda, meeting_date, meeting_time, 
                 meeting_person, person_id_one, meeting_person2, person_id_two, 
                 status, created_at
          FROM meeting_announcements";

// ✅ Add WHERE if school_id provided
if ($school_id > 0) {
    $query .= " WHERE school_id = ?";
}

$query .= " ORDER BY meeting_date DESC, meeting_time DESC";

$stmt = $conn->prepare($query);

if ($school_id > 0) {
    $stmt->bind_param("i", $school_id);
}

$stmt->execute();
$result = $stmt->get_result();

$meetings = [];
while ($row = $result->fetch_assoc()) {
    $meetings[] = [
        "id" => (int)$row['id'],
        "school_id" => (int)$row['school_id'],
        "title" => $row['title'],
        "meeting_agenda" => $row['meeting_agenda'],
        "meeting_date" => $row['meeting_date'],
        "meeting_time" => $row['meeting_time'],
        "meeting_person" => $row['meeting_person'],
        "person_id_one" => (int)$row['person_id_one'],
        "meeting_person2" => $row['meeting_person2'],
        "person_id_two" => (int)$row['person_id_two'],
        "status" => $row['status'],
        "created_at" => $row['created_at']
    ];
}

// ✅ Return JSON response
if (!empty($meetings)) {
    echo json_encode([
        "status" => "success",
        "count" => count($meetings),
        "data" => $meetings
    ], JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No meeting announcements found."
    ], JSON_PRETTY_PRINT);
}

$stmt->close();
$conn->close();
?>