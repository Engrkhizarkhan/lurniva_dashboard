<?php
require_once '../admin/sass/db_config.php';

// --- ✅ Enable CORS ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = $_POST['action'] ?? '';
$school_id = intval($_POST['school_id'] ?? 0);
$teacher_id = intval($_POST['teacher_id'] ?? 0);

if (!$school_id || !$teacher_id) {
    echo json_encode(["status" => "error", "message" => "Missing teacher_id or school_id"]);
    exit;
}

// ============= INSERT =============
if ($action === 'insert') {
    $class_id = intval($_POST['class_id'] ?? 0);
    $subject = $_POST['subject'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $description = $_POST['description'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $parent_accept = $_POST['parent_approval'] ?? 'no';
    $student_option = $_POST['student_option'] ?? 'all';
    $attachment = '';

    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
        $file_name = time() . '_' . basename($_FILES['file']['name']);
        $target = "../uploads/diary/" . $file_name;
        if (!is_dir("../uploads/diary")) mkdir("../uploads/diary", 0777, true);
        move_uploaded_file($_FILES['file']['tmp_name'], $target);
        $attachment = $file_name;
    }

    $stmt = $conn->prepare("INSERT INTO diary_entries 
        (school_id, class_meta_id, subject, teacher_id, topic, description, attachment, deadline, parent_approval_required, student_option, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iissssssss", $school_id, $class_id, $subject, $teacher_id, $topic, $description, $attachment, $deadline, $parent_accept, $student_option);

    if ($stmt->execute()) {
        $diary_id = $stmt->insert_id;

        if ($student_option === 'specific' && !empty($_POST['students'])) {
            $students = json_decode($_POST['students'], true);
            if (is_array($students)) {
                $stmt2 = $conn->prepare("INSERT INTO diary_students (diary_id, student_id) VALUES (?, ?)");
                foreach ($students as $sid) {
                    $stmt2->bind_param("ii", $diary_id, $sid);
                    $stmt2->execute();
                }
            }
        }

        echo json_encode(["status" => "success", "message" => "Diary entry created successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}

// ============= GET ALL =============
elseif ($action === 'getAll') {
    $query = "
        SELECT de.*, ctm.class_name, ctm.section, f.full_name AS teacher_name
        FROM diary_entries de
        LEFT JOIN class_timetable_meta ctm ON de.class_meta_id = ctm.id
        LEFT JOIN faculty f ON de.teacher_id = f.id
        WHERE de.school_id = ?
        ORDER BY de.created_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $entries = [];
    while ($row = $result->fetch_assoc()) {
        $entries[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "count" => count($entries),
        "data" => $entries
    ]);
}

// ============= GET ONE =============
elseif ($action === 'getOne') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM diary_entries WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $id, $school_id);
    $stmt->execute();
    $entry = $stmt->get_result()->fetch_assoc();

    if ($entry && $entry['student_option'] === 'specific') {
        $res = $conn->query("SELECT student_id FROM diary_students WHERE diary_id = $id");
        $entry['students'] = [];
        while ($r = $res->fetch_assoc()) {
            $entry['students'][] = $r['student_id'];
        }
    }

    echo json_encode([
        "status" => "success",
        "data" => $entry
    ]);
}

// ============= UPDATE =============
elseif ($action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $class_id = intval($_POST['class_id'] ?? 0);
    $subject = $_POST['subject'] ?? '';
    $topic = $_POST['topic'] ?? '';
    $description = $_POST['description'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $parent_accept = $_POST['parent_approval'] ?? 'no';
    $student_option = $_POST['student_option'] ?? 'all';
    $attachment = '';

    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
        $file_name = time() . '_' . basename($_FILES['file']['name']);
        $target = "../uploads/diary/" . $file_name;
        move_uploaded_file($_FILES['file']['tmp_name'], $target);
        $attachment = $file_name;
    }

    if ($attachment) {
        $stmt = $conn->prepare("UPDATE diary_entries 
            SET class_meta_id=?, subject=?, topic=?, description=?, deadline=?, parent_approval_required=?, student_option=?, attachment=?, updated_at=NOW()
            WHERE id=? AND school_id=?");
        $stmt->bind_param("issssssssi", $class_id, $subject, $topic, $description, $deadline, $parent_accept, $student_option, $attachment, $id, $school_id);
    } else {
        $stmt = $conn->prepare("UPDATE diary_entries 
            SET class_meta_id=?, subject=?, topic=?, description=?, deadline=?, parent_approval_required=?, student_option=?, updated_at=NOW()
            WHERE id=? AND school_id=?");
        $stmt->bind_param("issssssii", $class_id, $subject, $topic, $description, $deadline, $parent_accept, $student_option, $id, $school_id);
    }

    if ($stmt->execute()) {
        $conn->query("DELETE FROM diary_students WHERE diary_id = $id");
        if ($student_option === 'specific' && !empty($_POST['students'])) {
            $students = json_decode($_POST['students'], true);
            foreach ($students as $sid) {
                $conn->query("INSERT INTO diary_students (diary_id, student_id) VALUES ($id, $sid)");
            }
        }
        echo json_encode(["status" => "success", "message" => "Diary updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}

// ============= DELETE =============
elseif ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $conn->query("DELETE FROM diary_students WHERE diary_id = $id");
    $stmt = $conn->prepare("DELETE FROM diary_entries WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $id, $school_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Diary entry deleted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}

// ============= INVALID ACTION =============
else {
    echo json_encode(["status" => "error", "message" => "Invalid action"]);
}

$conn->close();
?>