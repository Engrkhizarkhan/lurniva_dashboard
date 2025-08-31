<?php
session_start();
header('Content-Type: application/json');
require_once '../sass/db_config.php';

// ✅ Get POST data
$fee_structure_id = $_POST['fee_structure_id'] ?? '';
$fee_type_id = $_POST['fee_type_id'] ?? '';
$rate = $_POST['rate'] ?? '';
$class_grade = $_POST['class_grade'] ?? '';
$total_amount = $_POST['total_amount'] ?? '';

// ✅ Get school ID (from session)
$school_id = $_SESSION['admin_id'] ?? 0;

// ✅ Insert new fee type
$stmt = $conn->prepare("INSERT INTO class_fee_types (fee_structure_id, school_id, class_grade, fee_type_id, rate) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iissi", $fee_structure_id, $school_id, $class_grade, $fee_type_id, $rate);
if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Insert failed: ' . $stmt->error]);
    exit;
}
$stmt->close();

// ✅ Recalculate total amount
$result = $conn->query("SELECT SUM(rate) AS total FROM class_fee_types WHERE fee_structure_id = $fee_structure_id");
$row = $result->fetch_assoc();
$new_total = $row['total'] ?? 0;

// ✅ Update fee_structures table
$stmt2 = $conn->prepare("UPDATE fee_structures SET amount = ? WHERE id = ?");
$stmt2->bind_param("di", $new_total, $fee_structure_id);
$stmt2->execute();
$stmt2->close();

// ✅ Send response once
echo json_encode([
    'status' => 'success',
    'fee_structure_id' => $fee_structure_id,
    'fee_type_id' => $fee_type_id,
    'rate' => $rate,
    'class_grade' => $class_grade,
    'new_total' => $new_total
]);
exit;
?>