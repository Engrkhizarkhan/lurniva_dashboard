<?php
require_once '../sass/db_config.php';
header('Content-Type: application/json');

$currentYear = date('Y');
$progressData = [];

// Get revenue per month (example: student_plan_orders table)
for ($m = 1; $m <= 12; $m++) {
    $res = $conn->query("
        SELECT COALESCE(SUM(total_price),0) AS month_total
        FROM student_plan_orders
        WHERE status='Paid'
          AND MONTH(created_at) = $m
          AND YEAR(created_at) = $currentYear
    ")->fetch_assoc();
    $progressData[] = (int)$res['month_total'];
}

// Send JSON
echo json_encode([
    "year" => $currentYear,
    "progress" => $progressData
]);