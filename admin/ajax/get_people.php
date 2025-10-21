<?php
session_start();
require '../sass/db_config.php';
$admin_id = $_SESSION['admin_id'];

$type = $_POST['type'];
$options = "<option value=''>Select</option>";

if ($type == 'teacher') {
      $res = $conn->query("SELECT id, full_name FROM faculty WHERE campus_id = '$admin_id'");
  while ($row = $res->fetch_assoc()) {
    $options .= "<option value='{$row['id']}'>{$row['full_name']}</option>";
  }
} elseif ($type == 'student') {
     $res = $conn->query("SELECT id, full_name FROM students WHERE school_id = '$admin_id'");
  while ($row = $res->fetch_assoc()) {
    $options .= "<option value='{$row['id']}'>{$row['full_name']}</option>";
  }
}

echo $options;