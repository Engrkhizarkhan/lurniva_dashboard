<?php
session_start();
require_once "../sass/db_config.php";

$action = $_POST['action'] ?? '';

if ($action == 'insert') {
    $class_id     = intval($_POST['class_id'] ?? 0);
    $teacher_id   = $_SESSION['admin_id'] ?? 0; // teacher logged in
    $topic        = trim($_POST['topic'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $deadline     = $_POST['deadline'] ?? '';
    $parent_req   = $_POST['parent_approval'] ?? 'no';
    $students     = isset($_POST['students']) ? json_decode($_POST['students'], true) : [];

    // ✅ Handle attachment
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

    if ($class_id > 0 && $teacher_id > 0 && !empty($topic) && !empty($description) && !empty($deadline) && !empty($students)) {
        $stmt = $conn->prepare("INSERT INTO student_behavior 
            (class_id, teacher_id, student_id, topic, description, attachment, deadline, parent_approval) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        foreach ($students as $sid) {
            $stmt->bind_param("iiisssss", $class_id, $teacher_id, $sid, $topic, $description, $attachment, $deadline, $parent_req);
            $stmt->execute();
        }
        echo "Behavior record(s) added successfully!";
    } else {
        echo "Missing required fields!";
    }
    exit;
}

if ($action == 'getAll') {
    $result = $conn->query("SELECT b.*, s.full_name AS student_name, s.roll_number, c.class_name, f.full_name AS teacher_name
        FROM student_behavior b
        JOIN students s ON b.student_id = s.id
        JOIN class_timetable_meta c ON b.class_id = c.id
        JOIN faculty f ON b.teacher_id = f.id
        ORDER BY b.created_at DESC");

    if ($result->num_rows > 0) {
        echo "<table class='table table-bordered'>
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Teacher</th>
                        <th>Student</th>
                        <th>Topic</th>
                        <th>Description</th>
                        <th>Deadline</th>
                        <th>Parent Approval</th>
                        <th>Attachment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['class_name']}</td>
                    <td>{$row['teacher_name']}</td>
                    <td>{$row['student_name']} ({$row['roll_number']})</td>
                    <td>{$row['topic']}</td>
                    <td>{$row['description']}</td>
                    <td>{$row['deadline']}</td>
                    <td>{$row['parent_approval']}</td>
                    <td>" . ($row['attachment'] ? "<a href='../uploads/behavior/{$row['attachment']}' target='_blank'>View</a>" : "N/A") . "</td>
                    <td>
                        <button class='btn btn-sm btn-warning editBehavior' data-id='{$row['id']}'>Edit</button>
                        <button class='btn btn-sm btn-danger deleteBehavior' data-id='{$row['id']}'>Delete</button>
                    </td>
                </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No behavior records found.</p>";
    }
    exit;
}

if ($action == 'getOne') {
    $id = intval($_POST['id'] ?? 0);
    $res = $conn->query("SELECT * FROM student_behavior WHERE id=$id LIMIT 1");
    echo json_encode($res->fetch_assoc());
    exit;
}

if ($action == 'update') {
    $id           = intval($_POST['id'] ?? 0);
    $class_id     = intval($_POST['class_id'] ?? 0);
    $topic        = trim($_POST['topic'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $deadline     = $_POST['deadline'] ?? '';
    $parent_req   = $_POST['parent_approval'] ?? 'no';

    // ✅ Handle attachment update
    $attachment = $_POST['old_attachment'] ?? null;
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

    $stmt = $conn->prepare("UPDATE student_behavior SET class_id=?, topic=?, description=?, attachment=?, deadline=?, parent_approval=? WHERE id=?");
    $stmt->bind_param("isssssi", $class_id, $topic, $description, $attachment, $deadline, $parent_req, $id);

    if ($stmt->execute()) {
        echo "Behavior updated successfully!";
    } else {
        echo "Update failed!";
    }
    exit;
}

if ($action == 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $conn->query("DELETE FROM student_behavior WHERE id=$id");
    echo "Behavior deleted successfully!";
    exit;
}
