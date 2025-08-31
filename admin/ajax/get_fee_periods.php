<?php
session_start();
require_once '../sass/db_config.php';
$school_id = $_SESSION['admin_id'];
$sql = "SELECT id, period_name FROM fee_periods WHERE school_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
  echo "<option value='{$row['id']}'>{$row['period_name']}</option>";
}