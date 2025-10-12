<?php
require_once '../admin/sass/db_config.php';
// --- ✅ CORS CONFIGURATION ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// --- ✅ Handle preflight request ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- ✅ Read JSON input (or form-data for file upload) ---
$data = json_decode(file_get_contents("php://input"), true);
$action = $_POST['action'] ?? $data['action'] ?? '';
$school_id = intval($_POST['school_id'] ?? $data['school_id'] ?? 0);
$teacher_id = intval($_POST['teacher_id'] ?? $data['teacher_id'] ?? 0);

if (!$action || !$school_id || !$teacher_id) {
    echo json_encode(["status" => "error", "message" => "Missing required fields (action, school_id, teacher_id)."]);
    exit;
}

// --- ✅ INSERT NEW ASSIGNMENT ---
if ($action === "insert") {
    $class_meta_id = intval($_POST['class_id'] ?? $data['class_id'] ?? 0);
    $subject       = trim($_POST['subject_id'] ?? $data['subject_id'] ?? '');
    $type          = trim($_POST['type'] ?? $data['type'] ?? '');
    $title         = trim($_POST['title'] ?? $data['title'] ?? '');
    $description   = trim($_POST['description'] ?? $data['description'] ?? '');
    $due_date      = trim($_POST['due_date'] ?? $data['due_date'] ?? '');
    $total_marks   = floatval($_POST['total_marks'] ?? $data['total_marks'] ?? 0);

    // Handle file upload
    $attachment = null;
    if (!empty($_FILES['attachment']['name'])) {
        $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $fileName = "assignment_" . time() . "." . $ext;
        $uploadPath = "../uploads/assignment/";
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
        move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath . $fileName);
        $attachment = $fileName;
    }

    if ($class_meta_id && $subject && $type && $title && $description && $due_date && $total_marks) {
        $stmt = $conn->prepare("
            INSERT INTO teacher_assignments 
            (school_id, teacher_id, class_meta_id, subject, type, title, description, due_date, total_marks, attachment, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiisssssds", $school_id, $teacher_id, $class_meta_id, $subject, $type, $title, $description, $due_date, $total_marks, $attachment);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Assignment/Test added successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
    }
    exit;
}

// --- ✅ FETCH ALL ASSIGNMENTS ---
if ($action === "getAll") {
    $stmt = $conn->prepare("
        SELECT ta.*, ctm.class_name, ctm.section 
        FROM teacher_assignments ta
        LEFT JOIN class_timetable_meta ctm ON ta.class_meta_id = ctm.id
        WHERE ta.school_id = ? AND ta.teacher_id = ?
        ORDER BY ta.created_at DESC
    ");
    $stmt->bind_param("ii", $school_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = [
            "id" => (int)$row['id'],
            "class" => $row['class_name'] . " - " . $row['section'],
            "subject" => $row['subject'],
            "type" => $row['type'],
            "title" => $row['title'],
            "description" => $row['description'],
            "due_date" => $row['due_date'],
            "total_marks" => $row['total_marks'],
            "attachment_url" => $row['attachment'] 
                ? "https://dashboard.lurniva.com/uploads/assignment/" . $row['attachment'] 
                : null,
            "created_at" => $row['created_at']
        ];
    }

    echo json_encode([
        "status" => "success",
        "count" => count($assignments),
        "data" => $assignments
    ]);
    $stmt->close();
    exit;
}

// --- ✅ FETCH SINGLE ASSIGNMENT ---
if ($action === "getOne") {
    $id = intval($_POST['id'] ?? $data['id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM teacher_assignments WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $row['attachment_url'] = $row['attachment']
            ? "https://dashboard.lurniva.com/uploads/assignment/" . $row['attachment']
            : null;
        echo json_encode(["status" => "success", "data" => $row]);
    } else {
        echo json_encode(["status" => "error", "message" => "Assignment not found."]);
    }
    $stmt->close();
    exit;
}

// --- ✅ UPDATE ASSIGNMENT ---
if ($action === "update") {
    $id            = intval($_POST['id'] ?? $data['id'] ?? 0);
    $class_meta_id = intval($_POST['class_id'] ?? $data['class_id'] ?? 0);
    $subject       = trim($_POST['subject_id'] ?? $data['subject_id'] ?? '');
    $type          = trim($_POST['type'] ?? $data['type'] ?? '');
    $title         = trim($_POST['title'] ?? $data['title'] ?? '');
    $description   = trim($_POST['description'] ?? $data['description'] ?? '');
    $due_date      = trim($_POST['due_date'] ?? $data['due_date'] ?? '');
    $total_marks   = floatval($_POST['total_marks'] ?? $data['total_marks'] ?? 0);

    // Handle attachment
    $attachment = null;
    if (!empty($_FILES['attachment']['name'])) {
        $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $fileName = "assignment_" . time() . "." . $ext;
        $uploadPath = "../uploads/assignment/";
        if (!is_dir($uploadPath)) mkdir($uploadPath, 0777, true);
        move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadPath . $fileName);
        $attachment = $fileName;
    }

    if ($attachment) {
        $stmt = $conn->prepare("
            UPDATE teacher_assignments
            SET class_meta_id=?, subject=?, type=?, title=?, description=?, due_date=?, total_marks=?, attachment=?, updated_at=NOW()
            WHERE id=? AND school_id=?
        ");
        $stmt->bind_param("isssssdsii", $class_meta_id, $subject, $type, $title, $description, $due_date, $total_marks, $attachment, $id, $school_id);
    } else {
        $stmt = $conn->prepare("
            UPDATE teacher_assignments
            SET class_meta_id=?, subject=?, type=?, title=?, description=?, due_date=?, total_marks=?, updated_at=NOW()
            WHERE id=? AND school_id=?
        ");
        $stmt->bind_param("isssssiii", $class_meta_id, $subject, $type, $title, $description, $due_date, $total_marks, $id, $school_id);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Assignment/Test updated successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error updating: " . $stmt->error]);
    }
    $stmt->close();
    exit;
}

// --- ✅ DELETE ASSIGNMENT ---
if ($action === "delete") {
    $id = intval($_POST['id'] ?? $data['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM teacher_assignments WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $id, $school_id);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Assignment/Test deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error deleting record."]);
    }
    $stmt->close();
    exit;
}

// --- 🚫 INVALID ACTION ---
echo json_encode(["status" => "error", "message" => "Invalid action."]);
$conn->close();
?>