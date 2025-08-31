<?php
session_start();
header('Content-Type: application/json');

require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

$currentYear = date('Y');
$currentMonth = date('m');

// Total Students
$stmt = $conn->prepare("SELECT COUNT(*) AS total_students FROM students WHERE school_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res = $stmt->get_result();
$total_students = ($res->num_rows > 0) ? $res->fetch_assoc()['total_students'] : 0;
$stmt->close();

// Total Teachers
$stmt = $conn->prepare("SELECT COUNT(*) AS total_teachers FROM faculty WHERE campus_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res = $stmt->get_result();
$total_teachers = ($res->num_rows > 0) ? $res->fetch_assoc()['total_teachers'] : 0;
$stmt->close();

// Total Income (sum all amount_paid for current year)
$stmt = $conn->prepare("SELECT IFNULL(SUM(amount_paid),0) AS total_income FROM fee_slip_details WHERE school_id = ? AND YEAR(payment_date) = ?");
$stmt->bind_param("ii", $admin_id, $currentYear);
$stmt->execute();
$res = $stmt->get_result();
$total_income = ($res->num_rows > 0) ? $res->fetch_assoc()['total_income'] : 0;
$stmt->close();

// Monthly Revenue (current month)
$stmt = $conn->prepare("SELECT IFNULL(SUM(amount_paid),0) AS monthly_revenue FROM fee_slip_details WHERE school_id = ? AND YEAR(payment_date) = ? AND MONTH(payment_date) = ?");
$stmt->bind_param("iii", $admin_id, $currentYear, $currentMonth);
$stmt->execute();
$res = $stmt->get_result();
$monthly_revenue = ($res->num_rows > 0) ? $res->fetch_assoc()['monthly_revenue'] : 0;
$stmt->close();

// Yearly Revenue (same as total_income, but for clarity)
$yearly_revenue = $total_income;

// Monthly revenue data for graph: total per month in current year
$monthly_revenue_data = [];
for ($m = 1; $m <= 12; $m++) {
    $stmt = $conn->prepare("SELECT IFNULL(SUM(amount_paid),0) AS month_revenue FROM fee_slip_details WHERE school_id = ? AND YEAR(payment_date) = ? AND MONTH(payment_date) = ?");
    $stmt->bind_param("iii", $admin_id, $currentYear, $m);
    $stmt->execute();
    $res = $stmt->get_result();
    $month_rev = 0;
    if ($res->num_rows > 0) {
        $month_rev = $res->fetch_assoc()['month_revenue'];
    }
    $monthly_revenue_data[] = (float)$month_rev;
    $stmt->close();
}

echo json_encode([
    'total_students' => (int)$total_students,
    'total_teachers' => (int)$total_teachers,
    'total_income' => number_format($total_income),
    'monthly_revenue' => number_format($monthly_revenue),
    'yearly_revenue' => number_format($yearly_revenue),
    'monthly_revenue_data' => $monthly_revenue_data,
    'current_year' => $currentYear
]);