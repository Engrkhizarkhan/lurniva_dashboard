<?php
session_start();
require_once '../sass/db_config.php'; // your DB connection

$teacher_id = $_SESSION['admin_id']; 
$school_id  = $_SESSION['campus_id']; 
$action = $_POST['action'] ?? '';

if ($action == "insert") {

    $class_id    = $_POST['class_id'] ?? 0;
    $subject     = $_POST['subject'] ?? ''; // subject is text
    $topic       = $_POST['topic'] ?? '';
    $description = $_POST['description'] ?? '';
    $deadline    = $_POST['deadline'] ?? '';
    $parent_accept = $_POST['parent_approval'] ?? 'no';
    $student_option = $_POST['student_option'] ?? 'all';
    $attachment = '';

    // File upload
    if(isset($_FILES['file']) && $_FILES['file']['name'] != ''){
        $file_name = time().'_'.$_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        move_uploaded_file($file_tmp, 'uploads/'.$file_name);
        $attachment = $file_name;
    }

    $stmt = $conn->prepare("INSERT INTO diary_entries 
        (school_id, class_meta_id, subject, teacher_id, topic, description, attachment, deadline, parent_approval_required, student_option) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissssssss", $school_id, $class_id, $subject, $teacher_id, $topic, $description, $attachment, $deadline, $parent_accept, $student_option);

    if($stmt->execute()){
        $diary_id = $stmt->insert_id;

        // Save specific students
        if($student_option == 'specific' && !empty($_POST['students'])){
            $students = json_decode($_POST['students']);
            foreach($students as $sid){
                $stmt2 = $conn->prepare("INSERT INTO diary_students (diary_id, student_id) VALUES (?, ?)");
                $stmt2->bind_param("ii", $diary_id, $sid);
                $stmt2->execute();
            }
        }

        echo "<div class='alert alert-success'>Diary entry submitted successfully.</div>";
    }else{
        echo "<div class='alert alert-danger'>Error: ".$conn->error."</div>";
    }
}

elseif($action == "getAll"){
    $result = $conn->query("SELECT de.*, ctm.class_name, ctm.section, t.full_name AS teacher_name
        FROM diary_entries de
        LEFT JOIN class_timetable_meta ctm ON de.class_meta_id = ctm.id
        LEFT JOIN faculty t ON de.teacher_id = t.id
        WHERE de.school_id = $school_id
        ORDER BY de.created_at DESC");

    if($result->num_rows > 0){
        echo "<table class='table table-bordered'>
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Topic</th>
                    <th>Description</th>
                    <th>Deadline</th>
                    <th>Parent Accept</th>
                    <th>Student Option</th>
                    <th>Actions</th>
                </tr>
            </thead><tbody>";
        while($row = $result->fetch_assoc()){
            echo "<tr>
                <td>{$row['class_name']} - {$row['section']}</td>
                <td>{$row['subject']}</td>
                <td>{$row['teacher_name']}</td>
                <td>{$row['topic']}</td>
                <td>{$row['description']}</td>
                <td>{$row['deadline']}</td>
                <td>{$row['parent_approval_required']}</td>
                <td>{$row['student_option']}</td>
                <td>
                    <button class='btn btn-sm btn-info editDiary' data-id='{$row['id']}'>Edit</button>
                    <button class='btn btn-sm btn-danger deleteDiary' data-id='{$row['id']}'>Delete</button>
                </td>
            </tr>";
        }
        echo "</tbody></table>";
    }else{
        echo "<div class='alert alert-info'>No diary entries found.</div>";
    }
}

elseif($action == "getOne"){
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("SELECT * FROM diary_entries WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $entry = $result->fetch_assoc();

    // Get students if specific
    if($entry && $entry['student_option'] == 'specific'){
        $res = $conn->query("SELECT student_id FROM diary_students WHERE diary_id = ".$entry['id']);
        $students = [];
        while($r = $res->fetch_assoc()){
            $students[] = $r['student_id'];
        }
        $entry['students'] = $students;
    }

    echo json_encode($entry);
}

elseif($action == "update"){
    $id          = $_POST['id'] ?? 0;
    $class_id    = $_POST['class_id'] ?? 0;
    $subject     = $_POST['subject'] ?? '';
    $topic       = $_POST['topic'] ?? '';
    $description = $_POST['description'] ?? '';
    $deadline    = $_POST['deadline'] ?? '';
    $parent_accept = $_POST['parent_approval'] ?? 'no';
    $student_option = $_POST['student_option'] ?? 'all';
    $attachment = '';

    // File upload
    if(isset($_FILES['file']) && $_FILES['file']['name'] != ''){
        $file_name = time().'_'.$_FILES['file']['name'];
        $file_tmp = $_FILES['file']['tmp_name'];
        move_uploaded_file($file_tmp, 'uploads/'.$file_name);
        $attachment = $file_name;
    }

    if($attachment){
        $stmt = $conn->prepare("UPDATE diary_entries SET class_meta_id=?, subject=?, topic=?, description=?, deadline=?, parent_approval_required=?, student_option=?, attachment=? WHERE id=? AND school_id=?");
        $stmt->bind_param("issssssssi", $class_id, $subject, $topic, $description, $deadline, $parent_accept, $student_option, $attachment, $id, $school_id);
    }else{
        $stmt = $conn->prepare("UPDATE diary_entries SET class_meta_id=?, subject=?, topic=?, description=?, deadline=?, parent_approval_required=?, student_option=? WHERE id=? AND school_id=?");
        $stmt->bind_param("issssssii", $class_id, $subject, $topic, $description, $deadline, $parent_accept, $student_option, $id, $school_id);
    }

    if($stmt->execute()){
        // Update students
        $conn->query("DELETE FROM diary_students WHERE diary_id = $id");
        if($student_option == 'specific' && !empty($_POST['students'])){
            $students = json_decode($_POST['students']);
            foreach($students as $sid){
                $stmt2 = $conn->prepare("INSERT INTO diary_students (diary_id, student_id) VALUES (?, ?)");
                $stmt2->bind_param("ii", $id, $sid);
                $stmt2->execute();
            }
        }
        echo "<div class='alert alert-success'>Diary entry updated successfully.</div>";
    }else{
        echo "<div class='alert alert-danger'>Error updating diary: ".$conn->error."</div>";
    }
}

elseif($action == "delete"){
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM diary_entries WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $id, $school_id);
    if($stmt->execute()){
        // Delete related students
        $conn->query("DELETE FROM diary_students WHERE diary_id = $id");
        echo "<div class='alert alert-success'>Diary entry deleted successfully.</div>";
    }else{
        echo "<div class='alert alert-danger'>Error deleting diary: ".$conn->error."</div>";
    }
}

else{
    echo "<div class='alert alert-danger'>Invalid action.</div>";
}
?>