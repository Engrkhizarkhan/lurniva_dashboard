<?php
require_once '../admin/sass/db_config.php'; // adjust path if needed
header('Content-Type: application/json');

// --- ✅ Allow CORS for app integration ---
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- ✅ Parse JSON input (for app / JS use) ---
$data = json_decode(file_get_contents("php://input"), true);

// --- ✅ Session fallback (for web version) ---
session_start();
$student_id = $_SESSION['student_id'] ?? ($data['student_id'] ?? 0);
$school_id  = $_SESSION['school_id'] ?? ($data['school_id'] ?? 0);
$action     = $data['action'] ?? ($_POST['action'] ?? '');

// --- ✅ Validate session or input ---
if (!$student_id || !$school_id) {
    echo json_encode(["status" => "error", "message" => "Missing session or credentials."]);
    exit;
}

// --- ✅ Utility: sanitize text ---
function clean($v) { return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8'); }

switch ($action) {

    // -----------------------
    // ✅ INSERT LEAVE REQUEST
    // -----------------------
    case 'insert':
        $teacher_id = (int)($data['teacher_id'] ?? 0);
        $leave_type = clean($data['leave_type'] ?? '');
        $start_date = $data['start_date'] ?? '';
        $end_date   = $data['end_date'] ?? '';
        $reason     = clean($data['reason'] ?? '');

        if (!$teacher_id || !$leave_type || !$start_date || !$end_date || !$reason) {
            echo json_encode(["status" => "error", "message" => "All fields are required."]);
            exit;
        }

        $stmt = $conn->prepare("
            INSERT INTO student_leaves 
            (student_id, school_id, teacher_id, leave_type, start_date, end_date, reason, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', NOW())
        ");
        $stmt->bind_param('iiissss', $student_id, $school_id, $teacher_id, $leave_type, $start_date, $end_date, $reason);
        $ok = $stmt->execute();

        echo json_encode(["status" => $ok ? "success" : "error", "message" => $ok ? "Leave submitted." : $conn->error]);
        break;

    // -----------------------
    // ✅ GET ALL LEAVES
    // -----------------------
    case 'getAll':
        $stmt = $conn->prepare("
            SELECT sl.*, f.full_name AS teacher_name
            FROM student_leaves sl
            LEFT JOIN faculty f ON sl.teacher_id = f.id
            WHERE sl.school_id = ? AND sl.student_id = ?
            ORDER BY sl.created_at DESC
        ");
        $stmt->bind_param('ii', $school_id, $student_id);
        $stmt->execute();
        $res = $stmt->get_result();

        $leaves = [];
        while ($r = $res->fetch_assoc()) {
            $leaves[] = [
                "id"          => (int)$r['id'],
                "teacher_name"=> clean($r['teacher_name']),
                "leave_type"  => clean($r['leave_type']),
                "start_date"  => $r['start_date'],
                "end_date"    => $r['end_date'],
                "reason"      => clean($r['reason']),
                "status"      => ucfirst($r['status']),
                "created_at"  => $r['created_at']
            ];
        }

        echo json_encode(["status" => "success", "count" => count($leaves), "data" => $leaves]);
        break;

    // -----------------------
    // ✅ GET ONE LEAVE
    // -----------------------
    case 'getOne':
        $id = (int)($data['id'] ?? 0);
        if (!$id) { echo json_encode([]); exit; }

        $stmt = $conn->prepare("SELECT * FROM student_leaves WHERE id=? AND school_id=? AND student_id=?");
        $stmt->bind_param('iii', $id, $school_id, $student_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row || strtolower($row['status']) !== 'pending') {
            echo json_encode([]);
        } else {
            echo json_encode(["status" => "success", "data" => $row]);
        }
        break;

    // -----------------------
    // ✅ UPDATE LEAVE
    // -----------------------
    case 'update':
        $id         = (int)($data['id'] ?? 0);
        $teacher_id = (int)($data['teacher_id'] ?? 0);
        $leave_type = clean($data['leave_type'] ?? '');
        $start_date = $data['start_date'] ?? '';
        $end_date   = $data['end_date'] ?? '';
        $reason     = clean($data['reason'] ?? '');

        if (!$id || !$teacher_id || !$leave_type || !$start_date || !$end_date || !$reason) {
            echo json_encode(["status" => "error", "message" => "All fields are required."]);
            exit;
        }

        $check = $conn->prepare("SELECT status FROM student_leaves WHERE id=? AND school_id=? AND student_id=?");
        $check->bind_param('iii', $id, $school_id, $student_id);
        $check->execute();
        $st = strtolower($check->get_result()->fetch_assoc()['status'] ?? '');
        if ($st !== 'pending') {
            echo json_encode(["status" => "error", "message" => "Only Pending requests can be updated."]);
            exit;
        }

        $stmt = $conn->prepare("
            UPDATE student_leaves
            SET teacher_id=?, leave_type=?, start_date=?, end_date=?, reason=?, updated_at=NOW()
            WHERE id=? AND school_id=? AND student_id=?
        ");
        $stmt->bind_param('isssssii', $teacher_id, $leave_type, $start_date, $end_date, $reason, $id, $school_id, $student_id);
        $ok = $stmt->execute();

        echo json_encode(["status" => $ok ? "success" : "error", "message" => $ok ? "Leave updated." : $conn->error]);
        break;

    // -----------------------
    // ✅ DELETE LEAVE
    // -----------------------
    case 'delete':
        $id = (int)($data['id'] ?? 0);
        if (!$id) { echo json_encode(["status" => "error", "message" => "Invalid ID."]); exit; }

        $stmt = $conn->prepare("
            DELETE FROM student_leaves 
            WHERE id=? AND school_id=? AND student_id=? AND status='Pending'
        ");
        $stmt->bind_param('iii', $id, $school_id, $student_id);
        $stmt->execute();

        echo json_encode([
            "status" => $stmt->affected_rows ? "success" : "error",
            "message" => $stmt->affected_rows ? "Leave deleted." : "Cannot delete (only Pending)."
        ]);
        break;

    // -----------------------
    // ❌ INVALID ACTION
    // -----------------------
    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
}