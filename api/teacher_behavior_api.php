<?php
require_once '../admin/sass/db_config.php'; // adjust path if needed

// --- ✅ Enable CORS for Flutter/Postman ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Parse input (both JSON or FormData)
$input = json_decode(file_get_contents("php://input"), true);
if (empty($input) && !empty($_POST)) $input = $_POST;

$action = $input['action'] ?? '';
$school_id = intval($input['school_id'] ?? 0);
$teacher_id = intval($input['teacher_id'] ?? 0);

// 🛡 Validate base session info
if (!$school_id || !$teacher_id) {
    echo json_encode(["status" => "error", "message" => "Missing school_id or teacher_id"]);
    exit;
}

// --- ACTION: INSERT ---
if ($action === 'insert') {
    $class_id     = intval($input['class_id'] ?? 0);
    $topic        = trim($input['topic'] ?? '');
    $description  = trim($input['description'] ?? '');
    $deadline     = $input['deadline'] ?? '';
    $parent_req   = $input['parent_approval'] ?? 'no';
    $students     = $input['students'] ?? [];

    if (!is_array($students)) $students = [];

    // ✅ Attachment (FormData upload)
    $attachment = null;
    if (!empty($_FILES['file']['name'])) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $newFile = "behavior_" . time() . "." . $ext;
        $uploadDir = "../uploads/behavior/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $uploadPath = $uploadDir . $newFile;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
            $attachment = $newFile;
        }
    }

    if ($class_id > 0 && $topic && $description && $deadline && !empty($students)) {
        $stmt = $conn->prepare("
            INSERT INTO student_behavior 
            (school_id, class_id, teacher_id, student_id, topic, description, attachment, deadline, parent_approval) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        foreach ($students as $sid) {
            $stmt->bind_param("iiiisssss", $school_id, $class_id, $teacher_id, $sid, $topic, $description, $attachment, $deadline, $parent_req);
            $stmt->execute();
        }

        echo json_encode(["status" => "success", "message" => "Behavior record(s) added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    }
    exit;
}

// --- ACTION: GET ALL ---
if ($action === 'getAll') {
    $query = "
        SELECT b.*, s.full_name AS student_name, s.roll_number, c.class_name, c.section, f.full_name AS teacher_name
        FROM student_behavior b
        JOIN students s ON b.student_id = s.id
        JOIN class_timetable_meta c ON b.class_id = c.id
        JOIN faculty f ON b.teacher_id = f.id
        WHERE b.school_id = ?
        ORDER BY b.created_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $records = [];
    while ($row = $res->fetch_assoc()) {
        $row['attachment_url'] = $row['attachment'] 
            ? "https://dashboard.lurniva.com/uploads/behavior/" . $row['attachment'] 
            : null;
        $records[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "count"  => count($records),
        "data"   => $records
    ]);
    exit;
}

// --- ACTION: GET ONE ---
if ($action === 'getOne') {
    $id = intval($input['id'] ?? 0);
    if (!$id) {
        echo json_encode(["status" => "error", "message" => "Missing id"]);
        exit;
    }
    $stmt = $conn->prepare("SELECT * FROM student_behavior WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $id, $school_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        $res['attachment_url'] = $res['attachment']
            ? "https://dashboard.lurniva.com/uploads/behavior/" . $res['attachment']
            : null;
        echo json_encode(["status" => "success", "data" => $res]);
    } else {
        echo json_encode(["status" => "error", "message" => "Record not found"]);
    }
    exit;
}

// --- ACTION: UPDATE ---
if ($action === 'update') {
    $id           = intval($input['id'] ?? 0);
    $class_id     = intval($input['class_id'] ?? 0);
    $topic        = trim($input['topic'] ?? '');
    $description  = trim($input['description'] ?? '');
    $deadline     = $input['deadline'] ?? '';
    $parent_req   = $input['parent_approval'] ?? 'no';
    $attachment   = $input['old_attachment'] ?? null;

    if (!empty($_FILES['file']['name'])) {
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $newFile = "behavior_" . time() . "." . $ext;
        $uploadDir = "../uploads/behavior/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $uploadPath = $uploadDir . $newFile;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
            $attachment = $newFile;
        }
    }

    $stmt = $conn->prepare("
        UPDATE student_behavior 
        SET class_id=?, topic=?, description=?, attachment=?, deadline=?, parent_approval=? 
        WHERE id=? AND school_id=?
    ");
    $stmt->bind_param("isssssii", $class_id, $topic, $description, $attachment, $deadline, $parent_req, $id, $school_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Behavior updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed"]);
    }
    exit;
}

// --- ACTION: DELETE ---
if ($action === 'delete') {
    $id = intval($input['id'] ?? 0);
    if (!$id) {
        echo json_encode(["status" => "error", "message" => "Missing id"]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM student_behavior WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $id, $school_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Behavior deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Delete failed"]);
    }
    exit;
}

// --- Default case ---
echo json_encode(["status" => "error", "message" => "Invalid action"]);
exit;
?>