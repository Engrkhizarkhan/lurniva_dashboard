<?php
session_start();
require_once '../sass/db_config.php';

// Session values
$teacher_id = $_SESSION['admin_id']; // Teacher logged in
$school_id  = $_SESSION['campus_id']; // School from session

$sql = "SELECT sl.*, s.full_name, s.class_grade, s.section, s.roll_number
FROM student_leaves sl
JOIN students s ON sl.student_id = s.id
WHERE sl.school_id = ? AND sl.teacher_id = ?
ORDER BY 
    CASE 
        WHEN sl.status = 'Pending' THEN 1 
        ELSE 2 
    END,
    sl.start_date";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $school_id, $teacher_id);
$stmt->execute();
$result = $stmt->get_result();

echo '
<div class="row">
  <div class="col-12">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Student Leave Requests</h4>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover text-center align-middle">
            <thead class="thead-dark">
              <tr>
                <th>Student</th>
                <th>Class</th>
                <th>Roll No</th>
                <th>Leave Type</th>
                <th>From</th>
                <th>To</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>';

if ($result->num_rows > 0) {
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
            <td>{$row['class_grade']} - {$row['section']}</td>
            <td>{$row['roll_number']}</td>
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
} else {
    echo "<tr><td colspan='9' class='text-center text-muted'>No pending leave requests</td></tr>";
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