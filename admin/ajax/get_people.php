<?php
require '../sass/db_config.php';

$type = $_POST['type'];
$options = "<option value=''>Select</option>";

if ($type == 'teacher') {
  $res = $conn->query("SELECT id, full_name FROM faculty");
  while ($row = $res->fetch_assoc()) {
    $options .= "<option value='{$row['id']}'>{$row['full_name']}</option>";
  }
} elseif ($type == 'student') {
  $res = $conn->query("SELECT id, full_name FROM students");
  while ($row = $res->fetch_assoc()) {
    $options .= "<option value='{$row['id']}'>{$row['full_name']}</option>";
  }
}

echo $options;