<?php
session_start();
require_once '../sass/db_config.php';

$admin_id = $_SESSION['admin_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // =========================
    // INSERT FACULTY
    // =========================
    if ($action == 'insert') {
        $stmt = $conn->prepare("INSERT INTO faculty 
            (campus_id, full_name, cnic, qualification, subjects, email, password, phone, address, joining_date, employment_type, schedule_preference, photo, created_at, status, subscription_start, subscription_end) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Approved', ?, ?)");

        $photoName = '';
        if (!empty($_FILES['photo']['name'])) {
            $photoName = time() . '_' . $_FILES['photo']['name'];
            move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/profile/' . $photoName);
            move_uploaded_file($_FILES['photo']['tmp_name'], '../../Faculty Dashboard/uploads/profile/' . $photoName);
        }

        $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // Subscription dates
        $subscription_start = $_POST['subscription_start'] ?? date('Y-m-d');
        $subscription_end   = $_POST['subscription_end'] ?? date('Y-m-d', strtotime('+1 month'));

        $stmt->bind_param(
            "sssssssssssssss",
            $admin_id,
            $_POST['full_name'],
            $_POST['cnic'],
            $_POST['qualification'],
            $_POST['subjects'],
            $_POST['email'],
            $passwordHash,
            $_POST['phone'],
            $_POST['address'],
            $_POST['joining_date'],
            $_POST['employment_type'],
            $_POST['schedule_preference'],
            $photoName,
            $subscription_start,
            $subscription_end
        );

        if ($stmt->execute()) {
            $faculty_id = $conn->insert_id; // ✅ Get the new faculty ID

            // ✅ Insert default settings for the faculty
            $sql_settings = "INSERT INTO school_settings 
                (person, person_id, layout, sidebar_color, color_theme, mini_sidebar, sticky_header, created_at, updated_at,
                attendance_enabled, behavior_enabled, chat_enabled, dairy_enabled, exam_enabled, fee_enabled, library_enabled,
                meeting_enabled, notice_board_enabled, assign_task_enabled, tests_assignments_enabled, timetable_enabled, transport_enabled)
                VALUES ('faculty', ?, '1', '1', 'white', '0', '0', NOW(), NOW(), 
                        '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1')";
            
            $stmt2 = $conn->prepare($sql_settings);
            $stmt2->bind_param("i", $faculty_id);
            $stmt2->execute();

            echo "success";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    // =========================
    // UPDATE FACULTY
    // =========================
    if ($action == 'update') {
        $photoName = $_POST['existing_photo'];
        if (!empty($_FILES['photo']['name'])) {
            $photoName = time() . '_' . $_FILES['photo']['name'];
            move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/profile/' . $photoName);
        }

        $stmt = $conn->prepare("UPDATE faculty SET 
            campus_id=?, full_name=?, cnic=?, qualification=?, subjects=?, email=?, phone=?, address=?, joining_date=?, employment_type=?, schedule_preference=?, photo=?, subscription_start=?, subscription_end=?, status='Approved'
            WHERE id=?");

        $subscription_start = $_POST['subscription_start'] ?? date('Y-m-d');
        $subscription_end   = $_POST['subscription_end'] ?? date('Y-m-d', strtotime('+1 month'));

        $stmt->bind_param(
            "ssssssssssssssi",
            $admin_id,
            $_POST['full_name'],
            $_POST['cnic'],
            $_POST['qualification'],
            $_POST['subjects'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['address'],
            $_POST['joining_date'],
            $_POST['employment_type'],
            $_POST['schedule_preference'],
            $photoName,
            $subscription_start,
            $subscription_end,
            $_POST['id']
        );

        if ($stmt->execute()) {
            echo "Updated successfully.";
        } else {
            echo "Update failed.";
        }
    }

    // =========================
    // GET ALL FACULTY
    // =========================
    if ($action == 'getAll') {
        $res = $conn->query("SELECT * FROM faculty WHERE campus_id = $admin_id ORDER BY id DESC");

        $output = "<table class='table table-bordered'><thead><tr>
            <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Type</th><th>Schedule</th><th>Subscription Start</th><th>Subscription End</th><th>Action</th>
        </tr></thead><tbody>";

        if ($res->num_rows > 0) {
            $i = 1;
            while ($row = $res->fetch_assoc()) {
                $output .= "<tr>
                    <td>{$i}</td>
                    <td>{$row['full_name']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['phone']}</td>
                    <td>{$row['employment_type']}</td>
                    <td>{$row['schedule_preference']}</td>
                    <td>{$row['subscription_start']}</td>
                    <td>{$row['subscription_end']}</td>
                    <td>
                        <button class='btn btn-sm btn-warning editBtn' data-id='{$row['id']}'>Edit</button>
                        <button class='btn btn-sm btn-danger deleteBtn' data-id='{$row['id']}'>Delete</button>
                    </td>
                </tr>";
                $i++;
            }
        } else {
            $output .= "<tr><td colspan='9' class='text-center'>No faculty found.</td></tr>";
        }
        $output .= "</tbody></table>";
        echo $output;
    }

    // =========================
    // GET ONE FACULTY
    // =========================
    if ($action == 'getOne') {
        $id = $_POST['id'];
        $res = $conn->query("SELECT * FROM faculty WHERE id = $id");
        echo json_encode($res->fetch_assoc());
    }

    // =========================
    // DELETE FACULTY
    // =========================
    if ($action == 'delete') {
        $id = $_POST['id'];
        $conn->query("DELETE FROM faculty WHERE id = $id");
        $conn->query("DELETE FROM school_settings WHERE person='faculty' AND person_id = $id");
        echo "Deleted successfully.";
    }
}
?>