<?php
// delete_fee_structure.php
session_start();
header('Content-Type: application/json');

require '../sass/db_config.php';

if (isset($_POST['id'])) {
    $fee_structure_id = intval($_POST['id']);

    // First delete related fee types
    $stmt = $conn->prepare("DELETE FROM class_fee_types WHERE fee_structure_id = ?");
    $stmt->bind_param("i", $fee_structure_id);
    $stmt->execute();
    $stmt->close();

    // Then delete fee structure
    $stmt2 = $conn->prepare("DELETE FROM fee_structures WHERE id = ?");
    $stmt2->bind_param("i", $fee_structure_id);
    $stmt2->execute();
    $stmt2->close();

    $conn->close();

    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
}