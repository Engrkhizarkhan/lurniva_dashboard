<?php
session_start();
require_once '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo "Session expired!";
    exit;
}

$admin_id = $_SESSION['admin_id'];
$school_id = $_SESSION['campus_id'] ?? 0;

// Get search filters from AJAX request
$filterType = $_GET['filter_type'] ?? '';
$filterValue = trim($_GET['filter_value'] ?? '');

// 1️⃣ Get all classes assigned to this teacher
$sql_classes = "
    SELECT DISTINCT ctm.id AS class_id, ctm.class_name, ctm.section, ctm.total_periods, ctm.id
    FROM class_timetable_meta ctm
    INNER JOIN class_timetable_details ctd 
        ON ctd.timing_meta_id = ctm.id
    WHERE ctd.teacher_id = ? AND ctm.school_id = ?
";
$stmt_classes = $conn->prepare($sql_classes);
$stmt_classes->bind_param("ii", $admin_id, $school_id);
$stmt_classes->execute();
$res_classes = $stmt_classes->get_result();

while ($class = $res_classes->fetch_assoc()) {
    $class_id = $class['class_id'];
    $class_name = $class['class_name'];
    $section = $class['section'];
    $total_periods = $class['total_periods'];
    $timing_table_id = $class['id'];

    echo "<div class='card mb-4 border-primary'>
            <div class='card-header bg-primary text-white'>
                <h5 class='mb-0'>{$class_name} - {$section}</h5>
                <small>Total Periods: {$total_periods}</small>
            </div>
            <div class='card-body'>
                <h6 class='text-muted'>Periods</h6>
                <ul class='list-group mb-3'>";

    // 2️⃣ Get periods for this class
    $sql_periods = "
        SELECT period_number, period_name, start_time, end_time
        FROM class_timetable_details
        WHERE timing_meta_id = ? AND teacher_id = ? 
        ORDER BY period_number ASC
    ";
    $stmt_periods = $conn->prepare($sql_periods);
    $stmt_periods->bind_param("ii", $timing_table_id, $admin_id);
    $stmt_periods->execute();
    $res_periods = $stmt_periods->get_result();

    while ($p = $res_periods->fetch_assoc()) {
        echo "<li class='list-group-item'>
                <strong>{$p['period_number']}. {$p['period_name']}</strong>
                <span class='text-muted'> ({$p['start_time']} - {$p['end_time']})</span>
              </li>";
    }

    echo "</ul>";

    // 3️⃣ Show students in this class (with search filter if provided)
    echo "<h6 class='text-muted'>Students</h6>
          <table class='table table-bordered table-striped table-hover'>
          <thead class='table-dark text-white'>
          <tr>
              <th>Photo</th>
              <th>Name</th>
              <th>Parent</th>
              <th>Class</th>
              <th>Roll No</th>
              <th>QR Code</th>
             
              <th>View Profile</th>
          </tr>
          </thead><tbody>";

    // Build student query with optional filtering
    $sql_students = "SELECT * FROM students WHERE class_grade = ? AND section = ?";
    $params = [$class_name, $section];
    $types = "ss";

    if (!empty($filterType) && !empty($filterValue)) {
        $sql_students .= " AND {$filterType} LIKE ?";
        $params[] = "%{$filterValue}%";
        $types .= "s";
    }

    $stmt_students = $conn->prepare($sql_students);
    $stmt_students->bind_param($types, ...$params);
    $stmt_students->execute();
    $res_students = $stmt_students->get_result();

    if ($res_students->num_rows > 0) {
        while ($row = $res_students->fetch_assoc()) {
            $jsonData = json_encode([
                'id' => $row['id'],
                'full_name' => $row['full_name'],
                'parent_name' => $row['parent_name'],
                'gender' => $row['gender'],
                'dob' => $row['dob'],
                'cnic_formb' => $row['cnic_formb'],
                'class' => $row['class_grade'],
                'section' => $row['section'],
                'roll_number' => $row['roll_number'],
                'email' => $row['email'],
                'parent_email' => $row['parent_email'],
                'phone' => $row['phone'],
                'address' => $row['address'],
                'status' => $row['status']
            ], JSON_UNESCAPED_UNICODE);

            $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . rawurlencode($jsonData) . "&size=150x150";

            echo "<tr>
                <td><img src='../student/uploads/profile/{$row['profile_photo']}' width='60' height='60' style='object-fit:cover'></td>
                <td>{$row['full_name']}</td>
                <td>{$row['parent_name']}</td>
                <td>{$row['class_grade']} - {$row['section']}</td>
                <td>{$row['roll_number']}</td>
                <td class='text-center'>
                    <a href='{$qrUrl}' target='_blank'>
        <img src='{$qrUrl}' width='100' style='cursor:pointer'>
    </a><br>
                    <small><strong>{$row['full_name']}</strong><br>{$row['class_grade']} - {$row['section']}<br>Roll: {$row['roll_number']}</small>
                </td>
                <td>
                    <form action='view_profile.php' method='POST'>
                        <input type='hidden' name='id' value='{$row['id']}'>
                        <button type='submit' class='btn btn-sm btn-info'>View</button>
                    </form>
                </td>
            </tr>";
        }
    } else {
        echo "<tr><td colspan='8' class='text-center text-muted'>No students found</td></tr>";
    }

    echo "</tbody></table></div></div>";
}
?>