<?php
require_once '../admin/sass/db_config.php';

// --- CORS CONFIGURATION (for Flutter/Postman) ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

$action     = $data['action'] ?? '';
$teacher_id = intval($data['teacher_id'] ?? 0);
$school_id  = intval($data['school_id'] ?? 0);

if (!$teacher_id || !$school_id) {
    echo json_encode(["status" => "error", "message" => "Missing school_id or teacher_id"]);
    exit;
}

// ✅ Helper: calculate total days
function calculateTotalDays($start_date, $end_date) {
    if (!empty($start_date) && !empty($end_date)) {
        $start = new DateTime($start_date);
        $end   = new DateTime($end_date);
        return $start->diff($end)->days + 1;
    }
    return 0;
}

/* ───────────────────────────────
   1️⃣ INSERT LEAVE REQUEST
─────────────────────────────── */
if ($action === "insert") {
    $leave_type = trim($data['leave_type'] ?? '');
    $start_date = $data['start_date'] ?? '';
    $end_date   = $data['end_date'] ?? '';
    $reason     = trim($data['reason'] ?? '');

    if (!$leave_type || !$start_date || !$end_date || !$reason) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    $total_days = calculateTotalDays($start_date, $end_date);

    $stmt = $conn->prepare("
        INSERT INTO faculty_leaves 
        (school_id, faculty_id, leave_type, start_date, end_date, total_days, reason, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param("iisssis", $school_id, $teacher_id, $leave_type, $start_date, $end_date, $total_days, $reason);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Leave request submitted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: {$stmt->error}"]);
    }
    $stmt->close();
}

/* ───────────────────────────────
   2️⃣ GET ALL LEAVES
─────────────────────────────── */
elseif ($action === "getAll") {
    $stmt = $conn->prepare("
        SELECT fl.*, f.full_name AS faculty_name 
        FROM faculty_leaves fl
        LEFT JOIN faculty f ON fl.faculty_id = f.id
        WHERE fl.school_id = ? AND f.id = ?
        ORDER BY fl.created_at DESC
    ");
    $stmt->bind_param("ii", $school_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $leaves = [];
    while ($row = $result->fetch_assoc()) {
        $leaves[] = [
            "id"          => (int)$row['id'],
            "faculty_name"=> $row['faculty_name'],
            "leave_type"  => $row['leave_type'],
            "start_date"  => $row['start_date'],
            "end_date"    => $row['end_date'],
            "total_days"  => (int)$row['total_days'],
            "reason"      => $row['reason'],
            "status"      => $row['status'],
            "created_at"  => $row['created_at']
        ];
    }

    echo json_encode([
        "status" => "success",
        "count"  => count($leaves),
        "data"   => $leaves
    ]);
    $stmt->close();
}

/* ───────────────────────────────
   3️⃣ GET SINGLE LEAVE
─────────────────────────────── */
elseif ($action === "getOne") {
    $id = intval($data['id'] ?? 0);
    if (!$id) {
        echo json_encode(["status" => "error", "message" => "Invalid leave ID."]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM faculty_leaves WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leave = $result->fetch_assoc();

    echo json_encode([
        "status" => $leave ? "success" : "error",
        "data"   => $leave ?: []
    ]);
    $stmt->close();
}

/* ───────────────────────────────
   4️⃣ UPDATE LEAVE
─────────────────────────────── */
elseif ($action === "update") {
    $id         = intval($data['id'] ?? 0);
    $leave_type = trim($data['leave_type'] ?? '');
    $start_date = $data['start_date'] ?? '';
    $end_date   = $data['end_date'] ?? '';
    $reason     = trim($data['reason'] ?? '');

    if (!$id || !$leave_type || !$start_date || !$end_date || !$reason) {
        echo json_encode(["status" => "error", "message" => "All fields are required for update."]);
        exit;
    }

    $total_days = calculateTotalDays($start_date, $end_date);

    $stmt = $conn->prepare("
        UPDATE faculty_leaves
        SET faculty_id=?, leave_type=?, start_date=?, end_date=?, total_days=?, reason=?, updated_at=NOW()
        WHERE id=? AND school_id=?
    ");
    $stmt->bind_param("isssisii", $teacher_id, $leave_type, $start_date, $end_date, $total_days, $reason, $id, $school_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Leave updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating leave: {$stmt->error}"]);
    }
    $stmt->close();
}

/* ───────────────────────────────
   5️⃣ DELETE LEAVE
─────────────────────────────── */
elseif ($action === "delete") {
    $id = intval($data['id'] ?? 0);
    if (!$id) {
        echo json_encode(["status" => "error", "message" => "Invalid leave ID."]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM faculty_leaves WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $id, $school_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Leave deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting leave: {$stmt->error}"]);
    }
    $stmt->close();
}

/* ───────────────────────────────
   ❌ INVALID ACTION
─────────────────────────────── */
else {
    echo json_encode(["status" => "error", "message" => "Invalid action."]);
}

$conn->close();
?>