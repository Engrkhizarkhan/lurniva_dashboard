<?php
session_start();
require_once '../sass/db_config.php';

$admin_id = $_SESSION['admin_id'];

$sql = "SELECT fl.*, f.full_name 
FROM faculty_leaves fl 
JOIN faculty f ON fl.faculty_id = f.id 
WHERE fl.school_id = ?
ORDER BY 
    CASE 
        WHEN fl.status = 'Pending' THEN 1 
        ELSE 2 
    END,
    fl.start_date;
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
echo '
<div class="row">
  <div class="col-12">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Leave History</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover text-center align-middle">
            <thead class="thead-dark">
              <tr>
                <th>Teacher</th>
                <th>Leave Type</th>
                <th>From</th>
                <th>To</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>';
while ($row = $result->fetch_assoc()) {
    // Set status badge color dynamically
    $status = ucfirst($row['status']);
    $badgeClass = 'secondary';
    if ($status === 'Approved') $badgeClass = 'success';
    elseif ($status === 'Rejected') $badgeClass = 'danger';
    elseif ($status === 'Pending') $badgeClass = 'warning';

    echo "
      <tr>
        <td>{$row['full_name']}</td>
        <td>{$row['leave_type']}</td>
        <td>{$row['start_date']}</td>
        <td>{$row['end_date']}</td>
        <td>{$row['reason']}</td>
        <td><span class='badge badge-{$badgeClass}'>{$status}</span></td>
        <td>
          <button class='btn btn-sm btn-success action-btn' data-id='{$row['id']}' data-status='Approved'>Approve</button>
          <button class='btn btn-sm btn-danger action-btn' data-id='{$row['id']}' data-status='Rejected'>Reject</button>
        </td>
      </tr>";
}
echo '
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>';

?>