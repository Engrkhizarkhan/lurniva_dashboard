<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
  exit;
}

require '../sass/db_config.php';

$fee_type_id = intval($_POST['fee_type_id']);
$fee_structure_id = intval($_POST['fee_structure_id']);
$rate = floatval($_POST['rate']);
$school_id = intval($_SESSION['admin_id']);

// Delete the class_fee_types row
$del = $conn->prepare("DELETE FROM class_fee_types WHERE id = ? AND fee_structure_id = ? LIMIT 1");
$del->bind_param("ii", $fee_type_id, $fee_structure_id);
$del->execute();

// Update the fee_structures amount
$update = $conn->prepare("UPDATE fee_structures SET amount = amount - ? WHERE id = ? AND school_id = ?");
$update->bind_param("dii", $rate, $fee_structure_id, $school_id);
$update->execute();

// Get new amount
$getAmount = $conn->prepare("SELECT amount FROM fee_structures WHERE id = ? AND school_id = ?");
$getAmount->bind_param("ii", $fee_structure_id, $school_id);
$getAmount->execute();
$resAmount = $getAmount->get_result()->fetch_assoc();
$newAmount = $resAmount['amount'];

// Get updated fee items
$items_sql = "SELECT cft.id, cft.fee_type_id, ft.fee_name, cft.rate 
              FROM class_fee_types cft
              JOIN fee_types ft ON ft.id = cft.fee_type_id
              WHERE cft.fee_structure_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $fee_structure_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$fee_items = [];
while ($row = $items_result->fetch_assoc()) {
  $fee_items[] = $row;
}

echo json_encode([
  'status' => 'success',
  'new_amount' => $newAmount,
  'fee_items' => $fee_items
]);