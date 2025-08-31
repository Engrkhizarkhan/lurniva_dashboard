<?php
session_start();
require_once '../sass/db_config.php';

$admin_id = $_SESSION['admin_id'];

$sql = "SELECT * FROM students WHERE school_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<table class='table table-bordered table-hover'>
<tr class='table-dark'>
  <th>Photo</th>
  <th>Name</th>
  <th>Parent</th>
  <th>Class</th>
  <th>Roll</th>
  <th>Status</th>
  <th>QR Code</th>
</tr>";

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
    'parent_email' => $row['parent_email'],
    'phone' => $row['phone'],
    'address' => $row['address'],
    'status' => $row['status']
  ], JSON_UNESCAPED_UNICODE);

  $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . rawurlencode($jsonData) . "&size=150x150";

  echo "<tr>
    <td><img src='../uploads/{$row['profile_photo']}' width='60' height='60' style='object-fit:cover'></td>
    <td>{$row['full_name']}</td>
    <td>{$row['parent_name']}</td>
    <td>{$row['class_grade']} - {$row['section']}</td>
    <td>{$row['roll_number']}</td>
    <td>
      <select class='form-select status-select' data-id='{$row['id']}'>
        <option value='Active' " . ($row['status'] == 'Active' ? 'selected' : '') . ">Active</option>
        <option value='Inactive' " . ($row['status'] == 'Inactive' ? 'selected' : '') . ">Inactive</option>
        <option value='Pending Verification' " . ($row['status'] == 'Pending Verification' ? 'selected' : '') . ">Pending Verification</option>
      </select>
    </td>
    <td><img src='{$qrUrl}' alt='QR Code'></td>
  </tr>";
}
echo "</table>";