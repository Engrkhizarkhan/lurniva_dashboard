<?php
session_start();
require_once '../sass/db_config.php';

// Session values
$teacher_id = $_SESSION['admin_id'] ?? 0; 
$school_id  = $_SESSION['campus_id'] ?? 0; 
$action     = $_POST['action'] ?? '';

if (!$teacher_id || !$school_id) {
    exit("<div class='alert alert-danger'>Session expired. Please log in again.</div>");
}

// Helper function to calculate total days
function calculateTotalDays($start_date, $end_date) {
    if (!empty($start_date) && !empty($end_date)) {
        $start = new DateTime($start_date);
        $end   = new DateTime($end_date);
        $interval = $start->diff($end);
        return $interval->days + 1; // include start date
    }
    return 0;
}

if ($action === "insert") {
    $leave_type = trim($_POST['leave_type'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date   = $_POST['end_date'] ?? '';
    $reason     = trim($_POST['reason'] ?? '');

    $total_days = calculateTotalDays($start_date, $end_date);

    if ($leave_type && $start_date && $end_date && $reason) {
        $stmt = $conn->prepare("
            INSERT INTO faculty_leaves 
                (school_id, faculty_id, leave_type, start_date, end_date, total_days, reason, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->bind_param("iisssis", $school_id, $teacher_id, $leave_type, $start_date, $end_date, $total_days, $reason);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Leave request submitted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Database error: {$stmt->error}</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>All fields are required.</div>";
    }
}

elseif ($action === "getAll") {
    $stmt = $conn->prepare("
        SELECT fl.*, f.full_name AS faculty_name 
        FROM faculty_leaves fl
        LEFT JOIN faculty f ON fl.faculty_id = f.id
        WHERE fl.school_id = ? AND f.id = ?
        ORDER BY fl.created_at DESC
    ");
    $stmt->bind_param("ii", $school_id, $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<table class='table table-bordered'>
                <thead>
                    <tr>
                        <th>Person</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead><tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['faculty_name']}</td>
                    <td>{$row['leave_type']}</td>
                    <td>{$row['start_date']} to {$row['end_date']}</td>
                    <td>{$row['total_days']}</td>
                    <td>{$row['reason']}</td>
                    <td>{$row['status']}</td>
                    <td>
                        <button class='btn btn-sm btn-info editLeave' data-id='{$row['id']}'>Edit</button>
                        <button class='btn btn-sm btn-danger deleteLeave' data-id='{$row['id']}'>Delete</button>
                    </td>
                  </tr>";
        }
        echo "</tbody></table>";
    } else {
        echo "<div class='alert alert-info'>No leave requests found.</div>";
    }
}

elseif ($action === "getOne") {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("SELECT * FROM faculty_leaves WHERE id = ? AND school_id = ? AND status='Pending'");
        $stmt->bind_param("ii", $id, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_assoc());
    } else {
        echo json_encode([]);
    }
}

elseif ($action === "update") {
    $id         = intval($_POST['id'] ?? 0);
    $leave_type = trim($_POST['leave_type'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date   = $_POST['end_date'] ?? '';
    $reason     = trim($_POST['reason'] ?? '');

    $total_days = calculateTotalDays($start_date, $end_date);

    if ($id && $leave_type && $start_date && $end_date && $reason) {
        $stmt = $conn->prepare("
            UPDATE faculty_leaves
            SET faculty_id = ?, leave_type = ?, start_date = ?, end_date = ?, total_days = ?, reason = ?, updated_at = NOW()
            WHERE id = ? AND school_id = ?
        ");
        $stmt->bind_param("isssisii", $teacher_id, $leave_type, $start_date, $end_date, $total_days, $reason, $id, $school_id);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Leave updated successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error updating leave: {$stmt->error}</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>All fields are required for update.</div>";
    }
}

elseif ($action === "delete") {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM faculty_leaves WHERE id = ? AND school_id = ?");
        $stmt->bind_param("ii", $id, $school_id);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Leave deleted successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error deleting leave: {$stmt->error}</div>";
        }
    } else {
        echo "<div class='alert alert-warning'>Invalid leave ID.</div>";
    }
}

else {
    echo "<div class='alert alert-danger'>Invalid action.</div>";
}