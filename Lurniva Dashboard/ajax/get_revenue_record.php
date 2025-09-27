<?php
require_once '../sass/db_config.php';
header('Content-Type: application/json');

$currentYear = date('Y');

// --- Users Count Per Month (Students + Faculty + Schools) ---
$usersPerMonth = [];
for ($m = 1; $m <= 12; $m++) {
    $schools = $conn->query("
        SELECT COUNT(*) as cnt 
        FROM schools 
        WHERE YEAR(created_at) = $currentYear AND MONTH(created_at) = $m
    ")->fetch_assoc()['cnt'];

    $students = $conn->query("
        SELECT COUNT(*) as cnt 
        FROM students 
        WHERE YEAR(created_at) = $currentYear AND MONTH(created_at) = $m
    ")->fetch_assoc()['cnt'];

    $faculty = $conn->query("
        SELECT COUNT(*) as cnt 
        FROM faculty 
        WHERE YEAR(created_at) = $currentYear AND MONTH(created_at) = $m
    ")->fetch_assoc()['cnt'];

    $usersPerMonth[] = $schools + $students + $faculty;
}

// --- Schools Revenue Per Month ---
$revenuePerMonth = [];
for ($m = 1; $m <= 12; $m++) {
    $revenue = $conn->query("
        SELECT COALESCE(SUM(total_price),0) as total 
        FROM student_plan_orders 
        WHERE status='Paid' 
          AND YEAR(created_at) = $currentYear 
          AND MONTH(created_at) = $m
    ")->fetch_assoc()['total'];

    $revenuePerMonth[] = (int)$revenue;
}

// --- Response ---
echo json_encode([
    "year" => $currentYear,
    "users_per_month" => $usersPerMonth,
    "schools_revenue" => $revenuePerMonth
]);