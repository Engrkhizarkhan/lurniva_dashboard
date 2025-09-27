<?php
session_start();
require_once '../sass/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

$school_id   = $_SESSION['admin_id'];
$slip_id     = intval($_POST['slip_id'] ?? 0);
$amount      = floatval($_POST['refund_amount'] ?? 0);
$reason      = trim($_POST['refund_reason'] ?? '');
$refund_date = $_POST['refund_date'] ?? date('Y-m-d');

if (!$slip_id || $amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid refund request']);
    exit;
}

// Check slip exists
$check = $conn->prepare("SELECT id, amount_paid FROM fee_slip_details WHERE id=? AND school_id=?");
$check->bind_param("ii", $slip_id, $school_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Fee slip not found']);
    exit;
}
$slip = $res->fetch_assoc();

// Prevent refund > paid
if ($amount > $slip['amount_paid']) {
    echo json_encode(['status' => 'error', 'message' => 'Refund exceeds paid amount']);
    exit;
}

// Insert refund record
$stmt = $conn->prepare("INSERT INTO fee_refunds (school_id, slip_id, refund_amount, refund_reason, refund_date, created_at)
                        VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("iidss", $school_id, $slip_id, $amount, $reason, $refund_date);

if ($stmt->execute()) {
    // Update slip amount_paid
    $new_paid = $slip['amount_paid'] - $amount;
    $upd = $conn->prepare("UPDATE fee_slip_details SET amount_paid=? WHERE id=? AND school_id=?");
    $upd->bind_param("dii", $new_paid, $slip_id, $school_id);
    $upd->execute();

    echo json_encode(['status' => 'success', 'message' => 'Refund recorded successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to record refund']);
}