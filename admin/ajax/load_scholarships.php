<?php
session_start();
require_once '../sass/db_config.php';

$school_id = $_SESSION['admin_id'];

$sql = "SELECT s.*, st.full_name FROM scholarships s
  JOIN students st ON st.id = s.student_id
  WHERE s.school_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

echo '<table class="table table-bordered">
<tr>
  <th>ID</th>
  <th>Student</th>
  <th>Type</th>
  <th>Amount</th>
  <th>Reason</th>
  <th>Status</th>
  <th>Actions</th>
</tr>';

while ($row = $result->fetch_assoc()) {
  echo '<tr>
    <td>'.$row['id'].'</td>
    <td>'.htmlspecialchars($row['full_name']).'</td>
    <td>'.$row['type'].'</td>
    <td>'.$row['amount'].'</td>
    <td>'.$row['reason'].'</td>
    <td>'.$row['status'].'</td>
    <td>
      <button type="button" class="btn btn-primary edit-btn" data-id="'.$row['id'].'" data-toggle="modal"
                      data-target=".bd-example-modal-lg">Edit</button>
      <button class="btn btn-sm btn-danger delete-btn" data-id="'.$row['id'].'">Delete</button>
    </td>
  </tr>';
}

echo '</table>';