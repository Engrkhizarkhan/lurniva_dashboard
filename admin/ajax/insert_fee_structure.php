<?php
session_start();
header('Content-Type: application/json');
require_once '../sass/db_config.php';

try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid input data");
    }

    $school_id = $_SESSION['admin_id'];
    $class_grade = $data['class_grade'];
    $frequency = $data['frequency'];
    $status = $data['status'];
    $total_amount = $data['total_amount'];
    $fee_items = $data['fee_items']; // array of fee_type_id + rate

    if (empty($fee_items)) {
        throw new Exception("No fee items provided");
    }

    // Check if fee structure already exists
    $check = $conn->prepare("SELECT id FROM fee_structures WHERE school_id = ? AND class_grade = ?");
    $check->bind_param("is", $school_id, $class_grade);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        // Exists → UPDATE
        $row = $result->fetch_assoc();
        $fee_structure_id = $row['id'];

        // Update main fee_structures row
        $update = $conn->prepare("UPDATE fee_structures SET amount = ?, frequency = ?, status = ?, created_at = NOW() WHERE id = ?");
        $update->bind_param("dssi", $total_amount, $frequency, $status, $fee_structure_id);
        $update->execute();

        // Delete old class_fee_types
        $conn->query("DELETE FROM class_fee_types WHERE fee_structure_id = $fee_structure_id");

    } else {
        // Not exists → INSERT
        $insert = $conn->prepare("INSERT INTO fee_structures (school_id, class_grade, amount, frequency, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $insert->bind_param("isdss", $school_id, $class_grade, $total_amount, $frequency, $status);
        $insert->execute();

        if ($insert->affected_rows === 0) {
            throw new Exception("Failed to insert fee structure");
        }

        $fee_structure_id = $insert->insert_id;
    }

    // Insert new class_fee_types
    $stmt2 = $conn->prepare("
        INSERT INTO class_fee_types 
        (school_id, class_grade, fee_type_id, rate, fee_structure_id) 
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($fee_items as $fee) {
        $fee_type_id = $fee['fee_type_id'];
        $rate = $fee['rate'];
        $stmt2->bind_param("isidi", $school_id, $class_grade, $fee_type_id, $rate, $fee_structure_id);
        $stmt2->execute();
    }

    echo json_encode([
        "status" => "success",
        "message" => "Class fee plan " . ($result->num_rows > 0 ? "updated" : "created") . " successfully."
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
} finally {
    $conn->close();
}