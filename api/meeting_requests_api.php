<?php
require_once '../admin/sass/db_config.php';

// --- CORS CONFIGURATION ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // 🔥 In production, restrict to your domain
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- Read JSON or POST input ---
$input = json_decode(file_get_contents("php://input"), true);
if (empty($input) && !empty($_POST)) $input = $_POST;

// --- Get teacher_id and school_id from POST ---
$teacher_id = isset($input['teacher_id']) ? intval($input['teacher_id']) : 0;
$school_id  = isset($input['school_id']) ? intval($input['school_id']) : 0;
$action     = $input['action'] ?? '';

if (!$teacher_id || !$school_id) {
    echo json_encode(["status" => "error", "message" => "Unauthorized - teacher_id and school_id required"]);
    exit;
}

$response = ["status" => "error", "message" => "Invalid action"];

// ---------------------- INSERT ----------------------
if ($action === 'insert') {
    $with_meeting = $input['with_meeting'] ?? '';
    $id_meeter    = $input['id_meeter'] ?? '';
    $title        = $input['title'] ?? '';
    $agenda       = $input['agenda'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO meeting_requests 
        (school_id, requested_by, requester_id, with_meeting, id_meeter, title, agenda, created_at) 
        VALUES (?, 'teacher', ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("iissss", $school_id, $teacher_id, $with_meeting, $id_meeter, $title, $agenda);

    if ($stmt->execute()) {
        $response = ["status" => "success", "message" => "Meeting request added successfully"];
    } else {
        $response = ["status" => "error", "message" => $stmt->error];
    }
    $stmt->close();
}

// ---------------------- UPDATE ----------------------
elseif ($action === 'update') {
    $id           = intval($input['id']);
    $with_meeting = $input['with_meeting'] ?? '';
    $id_meeter    = $input['id_meeter'] ?? '';
    $title        = $input['title'] ?? '';
    $agenda       = $input['agenda'] ?? '';

    $stmt = $conn->prepare("
        UPDATE meeting_requests 
        SET with_meeting=?, id_meeter=?, title=?, agenda=? 
        WHERE id=? AND school_id=?
    ");
    $stmt->bind_param("sissii", $with_meeting, $id_meeter, $title, $agenda, $id, $school_id);

    if ($stmt->execute()) {
        $response = ["status" => "success", "message" => "Meeting updated successfully"];
    } else {
        $response = ["status" => "error", "message" => $stmt->error];
    }
    $stmt->close();
}

// ---------------------- DELETE ----------------------
elseif ($action === 'delete') {
    $id = intval($input['id']);
    $stmt = $conn->prepare("DELETE FROM meeting_requests WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $id, $school_id);

    if ($stmt->execute()) {
        $response = ["status" => "success", "message" => "Meeting deleted successfully"];
    } else {
        $response = ["status" => "error", "message" => $stmt->error];
    }
    $stmt->close();
}

// ---------------------- FETCH ALL ----------------------
elseif ($action === 'fetch') {
    $stmt = $conn->prepare("
        SELECT id, with_meeting, id_meeter, title, status, agenda 
        FROM meeting_requests 
        WHERE school_id=? AND status!='approved' 
        ORDER BY id DESC
    ");
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $meetings = [];
    while ($row = $result->fetch_assoc()) {
        $meetings[] = $row;
    }

    $response = [
        "status" => "success",
        "count"  => count($meetings),
        "data"   => $meetings
    ];
    $stmt->close();
}

// ---------------------- GET SINGLE ----------------------
elseif ($action === 'get') {
    $id = intval($input['id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM meeting_requests WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $meeting = $result->fetch_assoc();

    if ($meeting) {
        $response = ["status" => "success", "data" => $meeting];
    } else {
        $response = ["status" => "error", "message" => "Meeting not found"];
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>