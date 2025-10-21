<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo "<tr><td colspan='5' class='text-center text-danger'>Unauthorized access</td></tr>";
    exit;
}

$admin_id = $_SESSION['admin_id']; // School ID for this admin
$action = $_REQUEST['action'] ?? 'read'; // ✅ Default to 'read' when not provided

if ($action == "save") {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['exam_name'] ?? '');
    $marks = intval($_POST['total_marks'] ?? 0);

    if ($name == '' || $marks <= 0) {
        echo json_encode(["status" => "error", "message" => "Please fill all fields."]);
        exit;
    }

    if ($id) {
        // ✅ Update existing exam
        $stmt = $conn->prepare("UPDATE exams SET exam_name = ?, total_marks = ? WHERE id = ? AND school_id = ?");
        $stmt->bind_param("siii", $name, $marks, $id, $admin_id);
        $ok = $stmt->execute();
        echo json_encode(["status" => $ok ? "success" : "error", "message" => $ok ? "Exam updated successfully." : "Update failed."]);
    } else {
        // ✅ Insert new exam
        $stmt = $conn->prepare("INSERT INTO exams (school_id, exam_name, total_marks, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("isi", $admin_id, $name, $marks);
        $ok = $stmt->execute();
        echo json_encode(["status" => $ok ? "success" : "error", "message" => $ok ? "Exam added successfully." : "Insert failed."]);
    }
    exit;
}

if ($action == "get") {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM exams WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $id, $admin_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if ($res) {
        echo json_encode(["status" => "success", "data" => $res]);
    } else {
        echo json_encode(["status" => "error", "message" => "Exam not found."]);
    }
    exit;
}

if ($action == "delete") {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM exams WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $id, $admin_id);
    $ok = $stmt->execute();
    echo json_encode(["status" => $ok ? "success" : "error", "message" => $ok ? "Exam deleted successfully." : "Delete failed."]);
    exit;
}

// ✅ Default action: READ
$stmt = $conn->prepare("SELECT id, exam_name, total_marks, created_at FROM exams WHERE school_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($r = $result->fetch_assoc()) {
        echo "<tr>
            <td>{$r['id']}</td>
            <td>{$r['exam_name']}</td>
            <td>{$r['total_marks']}</td>
            <td>{$r['created_at']}</td>
            <td>
                <button class='btn btn-sm btn-info editBtn' data-id='{$r['id']}'>Edit</button>
                <button class='btn btn-sm btn-danger deleteBtn' data-id='{$r['id']}'>Delete</button>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center'>No exams found.</td></tr>";
}
?>