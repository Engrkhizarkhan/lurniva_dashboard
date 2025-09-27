<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$school_id    = $_SESSION['admin_id'];
$student_id   = intval($_POST['student_id']);
$fee_period_id= intval($_POST['fee_period_id']);
$amounts      = $_POST['amount'] ?? [];
$due_dates    = $_POST['due_date'] ?? [];

if (empty($student_id) || empty($fee_period_id) || empty($amounts)) {
    echo json_encode(['status'=>'error','message'=>'Missing data']);
    exit;
}

for ($i=0; $i<count($amounts); $i++) {
    $amount = floatval($amounts[$i]);
    $due    = $due_dates[$i];
    if ($amount > 0 && !empty($due)) {
        $stmt = $conn->prepare("INSERT INTO fee_installments (school_id, student_id, fee_period_id, installment_number, amount, due_date) VALUES (?, ?, ?, ?, ?, ?)");
        $inst_no = $i+1;
        $stmt->bind_param("iiiids", $school_id, $student_id, $fee_period_id, $inst_no, $amount, $due);
        $stmt->execute();
    }
}

echo json_encode(['status'=>'success','message'=>'Installments created successfully']);