<?php

session_start();
require_once '../sass/db_config.php';

$admin_id = $_SESSION['admin_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action == 'insert') {
        $stmt = $conn->prepare("INSERT INTO faculty (campus_id, full_name, cnic, qualification, subjects, email, password, phone, address, joining_date, employment_type, schedule_preference, photo, created_at, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')");

        $photoName = '';
        if (!empty($_FILES['photo']['name'])) {
            $photoName = time() . '_' . $_FILES['photo']['name'];
            move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/profile/' . $photoName);
            move_uploaded_file($_FILES['photo']['tmp_name'], '../../Faculty Dashboard/uploads/profile/' . $photoName);
        }

        $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt->bind_param(
    "sssssssssssss",
    $admin_id,
    $_POST['full_name'],
    $_POST['cnic'],
    $_POST['qualification'],
    $_POST['subjects'],
    $_POST['email'],
    $passwordHash, // ✅ variable instead of direct function
    $_POST['phone'],
    $_POST['address'],
    $_POST['joining_date'],
    $_POST['employment_type'],
    $_POST['schedule_preference'],
    $photoName
);


        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error: " . $stmt->error;
        }
    }

    if ($action == 'getAll') {
       $res = $conn->query("SELECT * FROM faculty WHERE campus_id = $admin_id ORDER BY id DESC");

        $output = "<table class='table table-bordered'><thead><tr>
            <th>#</th><th>Name</th><th>Email</th><th>Phone</th><th>Type</th><th>Schedule</th><th>Action</th>
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
                    <td>
                        <button class='btn btn-sm btn-warning editBtn' data-id='{$row['id']}'>Edit</button>
                        <button class='btn btn-sm btn-danger deleteBtn' data-id='{$row['id']}'>Delete</button>
                    </td>
                </tr>";
                $i++;
            }
        } else {
            $output .= "<tr><td colspan='7' class='text-center'>No faculty found.</td></tr>";
        }
        $output .= "</tbody></table>";
        echo $output;
    }

    if ($action == 'getOne') {
        $id = $_POST['id'];
        $res = $conn->query("SELECT * FROM faculty WHERE id = $id");
        echo json_encode($res->fetch_assoc());
    }

    if ($action == 'update') {
        $photoName = $_POST['existing_photo'];
        if (!empty($_FILES['photo']['name'])) {
            $photoName = time() . '_' . $_FILES['photo']['name'];
            move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/profile/' . $photoName);
        }

        $stmt = $conn->prepare("UPDATE faculty SET campus_id=?, full_name=?, cnic=?, qualification=?, subjects=?, email=?, phone=?, address=?, joining_date=?, employment_type=?, schedule_preference=?, photo=? WHERE id=?");

        $stmt->bind_param(
            "ssssssssssssi",
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
            $_POST['id']
        );

        if ($stmt->execute()) {
            echo "Updated successfully.";
        } else {
            echo "Update failed.";
        }
    }

    if ($action == 'delete') {
        $id = $_POST['id'];
        $conn->query("DELETE FROM faculty WHERE id = $id");
        echo "Deleted successfully.";
    }
}
?>