<?php
header('Content-Type: application/json');
require_once '../sass/db_config.php';

$action = $_REQUEST['action'] ?? '';

if ($action == "save") {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['exam_name'] ?? '');
    $marks = intval($_POST['total_marks'] ?? 0);

    if ($name == '' || $marks <= 0) {
        echo json_encode(["status" => "error", "message" => "Please fill all fields."]);
        exit;
    }

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE exams SET exam_name = ?, total_marks = ? WHERE id = ?");
        $stmt->bind_param("sii", $name, $marks, $id);
        $ok = $stmt->execute();
        echo json_encode(["status" => $ok ? "success" : "error", "message" => $ok ? "Exam updated." : "Update failed."]);
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO exams (exam_name, total_marks) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $marks);
        $ok = $stmt->execute();
        echo json_encode(["status" => $ok ? "success" : "error", "message" => $ok ? "Exam added successfully." : "Insert failed."]);
    }
    exit;
}

if ($action == "read") {
    $result = $conn->query("SELECT * FROM exams ORDER BY id DESC");
    if ($result->num_rows > 0) {
        $rows = "";
        while ($r = $result->fetch_assoc()) {
            $rows .= "<tr>
                <td>{$r['id']}</td>
                <td>{$r['exam_name']}</td>
                <td>{$r['total_marks']}</td>
                <td>
                    <button class='btn btn-sm btn-info editBtn' data-id='{$r['id']}'>Edit</button>
                    <button class='btn btn-sm btn-danger deleteBtn' data-id='{$r['id']}'>Delete</button>
                </td>
            </tr>";
        }
        echo $rows;
    } else {
        echo "<tr><td colspan='4' class='text-center'>No exams found.</td></tr>";
    }
    exit;
}

if ($action == "get") {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM exams WHERE id = ?");
    $stmt->bind_param("i", $id);
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
    $stmt = $conn->prepare("DELETE FROM exams WHERE id = ?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    echo json_encode(["status" => $ok ? "success" : "error", "message" => $ok ? "Exam deleted." : "Delete failed."]);
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid action."]);
?>