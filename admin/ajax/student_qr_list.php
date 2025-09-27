<?php
session_start();
require_once '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo "Session expired!";
    exit;
}

$admin_id = $_SESSION['admin_id'];

$filterType = $_GET['filter_type'] ?? '';
$filterValue = $_GET['filter_value'] ?? '';
$filterSql = '';
$params = [];
$types = '';

if (!empty($filterType) && !empty($filterValue)) {
    if (in_array($filterType, ['full_name', 'class_grade', 'roll_number'])) {
        $filterSql = " AND $filterType LIKE ?";
        $params[] = '%' . $filterValue . '%';
        $types .= 's';
    }
}

$sql = "SELECT * FROM students WHERE school_id = ?" . $filterSql;
$stmt = $conn->prepare($sql);

$params = array_merge([$admin_id], $params);
$types = 'i' . $types;

$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

echo "<table class='table table-bordered table-striped table-hover'>
<thead class='table-dark text-white'>
<tr>
  <th>Photo</th>
  <th>Name</th>
  <th>Parent</th>
  <th>Class</th>
  <th>Roll No</th>
  <th>QR Code</th>
  <th>Status</th>
  <th>View Profile</th>
  <th>Migration</th>
  <th>Promote</th>
</tr>
</thead><tbody>";

while ($row = $result->fetch_assoc()) {
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
        'parent_email' => $row['parent_cnic'],
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
            <img src='{$qrUrl}' width='100'><br>
            <small><strong>{$row['full_name']}</strong><br>{$row['class_grade']} - {$row['section']}<br>Roll: {$row['roll_number']}</small>
        </td>
        <td>
            <select class='form-select status-select' id='status-select' data-id='{$row['id']}'>
                <option value='Approved' " . ($row['status'] == 'Approved' ? 'selected' : '') . ">Approved</option>
                <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending Verification</option>
            </select>
        </td>
        <td>
            <form action='view_profile.php' method='POST'>
                <input type='hidden' name='id' value='{$row['id']}'>
                <button type='submit' class='btn btn-sm btn-info'>View</button>
            </form>
        </td>
        <td>
            <button class='btn btn-sm btn-warning migrate-btn' data-id='{$row['id']}'>Migrate</button>
        </td>
        <td>
            <button class='btn btn-sm btn-success promote-btn' data-id='{$row['id']}'>Promote</button>
        </td>
    </tr>";
}

echo "</tbody></table>";