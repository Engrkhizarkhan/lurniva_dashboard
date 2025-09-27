<?php
session_start();
require_once '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo "<tr><td colspan='9'>Session expired</td></tr>";
    exit;
}

$school_id  = $_SESSION['admin_id'];
$period_id  = $_POST['period_id'] ?? '';
$student_id = $_POST['student_id'] ?? '';

$where = "WHERE fsd.school_id = $school_id";
if (!empty($period_id))  $where .= " AND fsd.fee_period_id = " . intval($period_id);
if (!empty($student_id)) $where .= " AND fsd.student_id = " . intval($student_id);

$sql = "SELECT fsd.*, s.full_name, s.class_grade, p.period_name 
        FROM fee_slip_details fsd
        INNER JOIN students s ON fsd.student_id = s.id
        INNER JOIN fee_periods p ON fsd.fee_period_id = p.id
        $where
        ORDER BY fsd.payment_date DESC";

$res = $conn->query($sql);

if (!$res || $res->num_rows === 0) {
    echo "<tr><td colspan='9'>No records found</td></tr>";
    exit;
}

while ($row = $res->fetch_assoc()) {
    $remaining = $row['net_payable'] - $row['amount_paid'];
    $status = ($remaining <= 0) ? "Paid" : "Partial";

    echo "<tr>
        <td>{$row['full_name']} ({$row['class_grade']})</td>
        <td>{$row['period_name']}</td>
        <td>Rs " . number_format($row['total_amount'], 2) . "</td>
        <td>Rs " . number_format($row['scholarship_amount'], 2) . "</td>
        <td>Rs " . number_format($row['net_payable'], 2) . "</td>
        <td>Rs " . number_format($row['amount_paid'], 2) . "</td>
        <td>Rs " . number_format($remaining, 2) . "</td>
        <td>$status</td>
        <td><button class='btn btn-sm btn-danger refundBtn' data-id='{$row['id']}'>Refund</button></td>
    </tr>";
}