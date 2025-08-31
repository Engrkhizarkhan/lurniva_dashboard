<?php
session_start();
require '../sass/db_config.php';

$id = $_POST['id'] ?? 0;
$school_id = $_SESSION['admin_id'] ?? 0;

$conn->query("DELETE FROM fee_periods WHERE id = $id AND school_id = $school_id");

echo json_encode(['status' => 'success', 'message' => 'Fee period deleted']);