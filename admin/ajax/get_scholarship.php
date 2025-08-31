<?php
session_start();
header('Content-Type: application/json');
require_once '../sass/db_config.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT * FROM scholarships WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

echo json_encode($result);