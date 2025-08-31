<?php
session_start();
require '../sass/db_config.php';

$id = $_POST['id'];
$stmt = $conn->prepare("DELETE FROM notes_board WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(["status" => "success", "message" => "Note deleted."]);