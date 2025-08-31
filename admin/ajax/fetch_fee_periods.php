<?php
session_start();
require '../sass/db_config.php';

$school_id = $_SESSION['admin_id'] ?? 0;

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $res = $conn->query("SELECT * FROM fee_periods WHERE id = $id AND school_id = $school_id");
    echo json_encode($res->fetch_assoc());
    exit;
}

$res = $conn->query("SELECT * FROM fee_periods WHERE school_id = $school_id ORDER BY id DESC");

echo '<table class="table table-bordered">';
echo '<tr class="table-secondary"><th>#</th><th>Name</th><th>Type</th><th>Start</th><th>End</th><th>Status</th><th>Action</th></tr>';
$i = 1;
while ($r = $res->fetch_assoc()) {
    echo "<tr>
      <td>{$i}</td>
      <td>{$r['period_name']}</td>
      <td>{$r['period_type']}</td>
      <td>{$r['start_date']}</td>
      <td>{$r['end_date']}</td>
      <td>{$r['status']}</td>
      <td>
        <button onclick='editPeriod({$r['id']})' class='btn btn-sm btn-warning'>Edit</button>
        <button onclick='deletePeriod({$r['id']})' class='btn btn-sm btn-danger'>Delete</button>
      </td>
    </tr>";
    $i++;
}
echo '</table>';