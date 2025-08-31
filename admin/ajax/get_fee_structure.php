<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require '../sass/db_config.php';

$id = intval($_GET['id']);
$school_id = intval($_SESSION['admin_id']);

// Fetch main structure
$sql = "SELECT frequency, status, class_grade, amount 
        FROM fee_structures 
        WHERE id = ? AND school_id = ? 
        LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $school_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Fee structure not found']);
    exit;
}

$data = $result->fetch_assoc();

// Fetch fee items
$items_sql = "SELECT cft.id, cft.fee_type_id, ft.fee_name, cft.rate 
              FROM class_fee_types cft 
              JOIN fee_types ft ON ft.id = cft.fee_type_id 
              WHERE cft.fee_structure_id = ?";
$stmt2 = $conn->prepare($items_sql);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$items_result = $stmt2->get_result();

$fee_items = [];
while ($row = $items_result->fetch_assoc()) {
    $fee_items[] = $row;
}

$data['fee_items'] = $fee_items;

echo json_encode($data);