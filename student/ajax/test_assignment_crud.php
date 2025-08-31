<?php
session_start();
require_once '../sass/db_config.php';

// Session values
$teacher_id = $_SESSION['admin_id']; // Logged-in teacher
$school_id  = $_SESSION['campus_id']; // School from session
$action = $_POST['action'] ?? '';

if ($action == "insert") {
    $class_meta_id = $_POST['class_id'] ?? 0;
    $subject       = $_POST['subject_id'] ?? '';
    $type          = $_POST['type'] ?? '';
    $title         = $_POST['title'] ?? '';
    $description   = $_POST['description'] ?? '';
    $due_date      = $_POST['due_date'] ?? '';
    $total_marks   = $_POST['total_marks'] ?? 0;
    
    // Handle attachment
    $attachment = null;
    if(isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0){
        $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $newName = "uploads/assignment/assignment_".time().".".$ext;
        move_uploaded_file($_FILES['attachment']['tmp_name'], "../".$newName);
        $attachment = "assignment_".time().".".$ext;
    }

    if($class_meta_id && $subject && $type && $title && $description && $due_date && $total_marks){
        $stmt = $conn->prepare("
            INSERT INTO teacher_assignments 
            (school_id, teacher_id, class_meta_id, subject, type, title, description, due_date, total_marks, attachment, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->bind_param("iiisssssds", $school_id, $teacher_id, $class_meta_id, $subject, $type, $title, $description, $due_date, $total_marks, $attachment);

        if($stmt->execute()){
            echo "<div class='alert alert-success'>Assignment/Test added successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: ".$conn->error."</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>All fields are required.</div>";
    }
}

elseif ($action == "getAll") {
    $result = $conn->query("
        SELECT ta.*, ctm.class_name, ctm.section
        FROM teacher_assignments ta
        LEFT JOIN class_timetable_meta ctm ON ta.class_meta_id = ctm.id
        WHERE ta.school_id = '$school_id' AND ta.teacher_id = '$teacher_id'
        ORDER BY ta.created_at DESC
    ");

    if($result->num_rows > 0){
        echo "<table class='table table-bordered'>
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Due Date</th>
                        <th>Total Marks</th>
                        <th>Attachment</th>
                        <th>Actions</th>
                    </tr>
                </thead><tbody>";
        while($row = $result->fetch_assoc()){
            $attachmentLink = $row['attachment'] ? "<a href='uploads/assignment/".$row['attachment']."' target='_blank'>View</a>" : "-";
            echo "<tr>
                    <td>{$row['class_name']} - {$row['section']}</td>
                    <td>{$row['subject']}</td>
                    <td>{$row['type']}</td>
                    <td>{$row['title']}</td>
                    <td>{$row['due_date']}</td>
                    <td>{$row['total_marks']}</td>
                    <td>{$attachmentLink}</td>
                    <td>
                        <button class='btn btn-sm btn-info editAssignment' data-id='{$row['id']}'>Edit</button>
                        <button class='btn btn-sm btn-danger deleteAssignment' data-id='{$row['id']}'>Delete</button>
                    </td>
                  </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<div class='alert alert-info'>No assignments/tests found.</div>";
    }
}

elseif ($action == "getOne") {
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("SELECT * FROM teacher_assignments WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_assoc());
}

elseif ($action == "update") {
    $id            = $_POST['id'] ?? 0;
    $class_meta_id = $_POST['class_id'] ?? 0;
    $subject       = $_POST['subject_id'] ?? '';
    $type          = $_POST['type'] ?? '';
    $title         = $_POST['title'] ?? '';
    $description   = $_POST['description'] ?? '';
    $due_date      = $_POST['due_date'] ?? '';
    $total_marks   = $_POST['total_marks'] ?? 0;

    // Handle new attachment if uploaded
    $attachment = null;
    if(isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0){
        $ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $newName = "uploads/assignment/assignment_".time().".".$ext;
        move_uploaded_file($_FILES['attachment']['tmp_name'], "../".$newName);
        $attachment = "assignment_".time().".".$ext;
    }

    if($class_meta_id && $subject && $type && $title && $description && $due_date && $total_marks){
        if($attachment){
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

        if($stmt->execute()){
            echo "<div class='alert alert-success'>Assignment/Test updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: ".$conn->error."</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>All fields are required.</div>";
    }
}

elseif ($action == "delete") {
    $id = $_POST['id'] ?? 0;
    $stmt = $conn->prepare("DELETE FROM teacher_assignments WHERE id=? AND school_id=?");
    $stmt->bind_param("ii", $id, $school_id);
    if($stmt->execute()){
        echo "<div class='alert alert-success'>Assignment/Test deleted successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting assignment/test.</div>";
    }
}

else{
    echo "<div class='alert alert-danger'>Invalid action.</div>";
}
?>