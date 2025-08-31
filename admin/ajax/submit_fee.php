<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$school_id      = $_SESSION['admin_id'];
$student_id     = intval($_POST['student_id']);
$fee_period_id  = intval($_POST['fee_period_id']);
$amount_paid    = floatval($_POST['amount_paid']);
$payment_method = $_POST['payment_method'];
$payment_date   = date('Y-m-d');

// ✅ First, check if a fee slip exists for this student + period
$slipQuery = $conn->prepare("SELECT * FROM fee_slip_details WHERE school_id=? AND student_id=? AND fee_period_id=?");
$slipQuery->bind_param("iii", $school_id, $student_id, $fee_period_id);
$slipQuery->execute();
$slipRes = $slipQuery->get_result();

if ($slipRes->num_rows == 0) {
    // 🆕 Create new slip if not exists
    // 1. Get student class
    $studentQuery = $conn->query("SELECT class_grade FROM students WHERE id = $student_id AND school_id = $school_id");
    if (!$studentQuery || $studentQuery->num_rows == 0) {
        echo json_encode(['status' => 'error', 'message' => 'Student not found']);
        exit;
    }
    $class = $studentQuery->fetch_assoc()['class_grade'];

    // 2. Calculate total fee
    $total_amount = 0;
    $feeMap = [];
    $classFeeQuery = $conn->query("SELECT * FROM class_fee_types WHERE school_id = $school_id AND class_grade = '$class'");
    while ($cf = $classFeeQuery->fetch_assoc()) {
        $feeMap[$cf['fee_type_id']] = $cf['rate'];
        $total_amount += $cf['rate'];
    }

    // 3. Override with student-specific fees
    $studentFeeQuery = $conn->query("SELECT * FROM student_fee_plans WHERE school_id = $school_id AND student_id = $student_id");
    while ($sf = $studentFeeQuery->fetch_assoc()) {
        $fid = $sf['fee_component'];
        $rate = $sf['base_amount'];
        $total_amount -= $feeMap[$fid] ?? 0; // remove class fee
        $total_amount += $rate;              // add student-specific fee
    }

    // 4. Scholarships
    $scholarship_amount = 0;
    $scholarshipQuery = $conn->query("SELECT * FROM scholarships WHERE school_id = $school_id AND student_id = $student_id AND status = 'approved'");
    while ($sch = $scholarshipQuery->fetch_assoc()) {
        if ($sch['type'] === 'percentage') {
            $scholarship_amount += ($total_amount * $sch['amount']) / 100;
        } else {
            $scholarship_amount += $sch['amount'];
        }
    }

    $net_payable = $total_amount - $scholarship_amount;

    // 5. Insert slip
    $stmt = $conn->prepare("INSERT INTO fee_slip_details 
        (school_id, student_id, fee_period_id, total_amount, scholarship_amount, net_payable, amount_paid, balance_due, payment_status, payment_date, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 0, ?, 'UNPAID', ?, NOW())");

    $balance_due = $net_payable; // nothing paid yet
    $stmt->bind_param("iiidddds", $school_id, $student_id, $fee_period_id, $total_amount, $scholarship_amount, $net_payable, $balance_due, $payment_date);

    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create slip.']);
        exit;
    }

    $slip_id = $stmt->insert_id;
} else {
    // ✅ Existing slip
    $slip = $slipRes->fetch_assoc();
    $slip_id = $slip['id'];
    $net_payable = $slip['net_payable'];
}

// ✅ Record the payment in fee_payments
$stmt = $conn->prepare("INSERT INTO fee_payments 
    (school_id, fee_slip_id, student_id, amount, payment_method, payment_date, status)
    VALUES (?, ?, ?, ?, ?, ?, 'CLEARED')");
$stmt->bind_param("iiidss", $school_id, $slip_id, $student_id, $amount_paid, $payment_method, $payment_date);

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to record payment.']);
    exit;
}

// ✅ Recalculate totals
$res = $conn->query("SELECT SUM(amount) as paid FROM fee_payments WHERE fee_slip_id = $slip_id AND status='CLEARED'");
$paid = $res->fetch_assoc()['paid'] ?? 0;
$balance_due = max(0, $net_payable - $paid);

// Determine status
if ($paid == 0) {
    $status = 'UNPAID';
} elseif ($paid < $net_payable) {
    $status = 'PARTIALLY_PAID';
} else {
    $status = 'PAID';
}

// ✅ Update slip
$update = $conn->prepare("UPDATE fee_slip_details SET amount_paid=?, balance_due=?, payment_status=? WHERE id=?");
$update->bind_param("ddsi", $paid, $balance_due, $status, $slip_id);
$update->execute();

echo json_encode([
    'status' => 'success',
    'message' => "Payment of Rs. $amount_paid recorded. Total Paid: Rs. $paid, Balance Due: Rs. $balance_due",
    'paid' => $paid,
    'balance_due' => $balance_due,
    'slip_status' => $status
]);