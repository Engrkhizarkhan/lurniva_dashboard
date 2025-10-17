<?php
require_once '../admin/sass/db_config.php'; // ✅ unified DB path
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

// ✅ Support session or posted data
$data = json_decode(file_get_contents("php://input"), true);

$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? 0);
$school_id  = intval($_SESSION['school_id'] ?? $data['school_id'] ?? 0);
$action     = $data['action'] ?? ($_POST['action'] ?? $_GET['action'] ?? '');

if (!$student_id || !$school_id) {
    echo json_encode(["status" => "error", "message" => "Session missing or invalid"]);
    exit;
}

switch ($action) {

    // ✅ INSERT Meeting
    case 'insert':
        $with_meeting = $data['with_meeting'] ?? '';
        $id_meeter    = intval($data['id_meeter'] ?? 0);
        $title        = trim($data['title'] ?? '');
        $agenda       = trim($data['agenda'] ?? '');

        if (!$with_meeting || !$id_meeter || !$title || !$agenda) {
            echo json_encode(["status" => "error", "message" => "All fields are required"]);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO meeting_requests 
            (school_id, requested_by, requester_id, with_meeting, id_meeter, title, agenda, created_at)
            VALUES (?, 'parent', ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iissss", $school_id, $student_id, $with_meeting, $id_meeter, $title, $agenda);

        echo $stmt->execute()
            ? json_encode(["status" => "success", "message" => "Meeting request added successfully"])
            : json_encode(["status" => "error", "message" => $stmt->error]);
        break;

    // ✅ UPDATE Meeting
    case 'update':
        $id           = intval($data['id'] ?? 0);
        $with_meeting = $data['with_meeting'] ?? '';
        $id_meeter    = intval($data['id_meeter'] ?? 0);
        $title        = trim($data['title'] ?? '');
        $agenda       = trim($data['agenda'] ?? '');

        $stmt = $conn->prepare("UPDATE meeting_requests 
                                SET with_meeting=?, id_meeter=?, title=?, agenda=? 
                                WHERE id=? AND school_id=?");
        $stmt->bind_param("sissii", $with_meeting, $id_meeter, $title, $agenda, $id, $school_id);

        echo $stmt->execute()
            ? json_encode(["status" => "success", "message" => "Meeting updated successfully"])
            : json_encode(["status" => "error", "message" => $stmt->error]);
        break;

    // ✅ DELETE Meeting
    case 'delete':
        $id = intval($data['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM meeting_requests WHERE id=? AND school_id=?");
        $stmt->bind_param("ii", $id, $school_id);

        echo $stmt->execute()
            ? json_encode(["status" => "success", "message" => "Meeting deleted successfully"])
            : json_encode(["status" => "error", "message" => $stmt->error]);
        break;

    // ✅ FETCH Meetings
    case 'fetch':
        $result = $conn->query("SELECT id, with_meeting, id_meeter, title, status, agenda
                                FROM meeting_requests 
                                WHERE school_id='$school_id' 
                                  AND requested_by='parent' 
                                  AND requester_id=$student_id 
                                ORDER BY id DESC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    // ✅ GET Single Meeting
    case 'get':
        $id = intval($data['id'] ?? 0);
        $stmt = $conn->prepare("SELECT * FROM meeting_requests WHERE id=? AND school_id=?");
        $stmt->bind_param("ii", $id, $school_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        echo json_encode(["status" => "success", "data" => $res]);
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
}

$conn->close();
?>