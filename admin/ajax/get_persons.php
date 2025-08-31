<?php
session_start();
require_once '../sass/db_config.php';


$type = $_POST['person_type'];
$admin_id = $_SESSION['admin_id'];

$options = "<option value=''>Select</option>";

if ($type == "admin") {
  $options .= "<option value='$admin_id'>Admin</option>";
} elseif ($type == "teacher") {
  $stmt = $conn->prepare("SELECT id, full_name FROM faculty WHERE campus_id = ?");
  $stmt->bind_param("i", $admin_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()){
    $options .= "<option value='{$row['id']}'>{$row['full_name']}</option>";
  }
} elseif ($type == "parent") {
  $stmt = $conn->prepare("SELECT id, parent_name FROM students WHERE school_id = ?");
  $stmt->bind_param("i", $admin_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while($row = $result->fetch_assoc()){
    $options .= "<option value='{$row['id']}'>{$row['parent_name']}</option>";
  }
}
echo $options;