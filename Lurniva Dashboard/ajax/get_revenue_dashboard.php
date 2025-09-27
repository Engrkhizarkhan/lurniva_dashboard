<?php
require_once '../sass/db_config.php';

header('Content-Type: application/json');

// --- Total Users ---
$totalSchools = $conn->query("SELECT COUNT(*) AS cnt FROM schools")->fetch_assoc()['cnt'];
$totalStudents = $conn->query("SELECT COUNT(*) AS cnt FROM students")->fetch_assoc()['cnt'];
$totalTeachers = $conn->query("SELECT COUNT(*) AS cnt FROM faculty")->fetch_assoc()['cnt'];

$totalUsers = $totalSchools + $totalStudents + $totalTeachers;

// --- Total Income ---
$totalIncome = $conn->query("
    SELECT COALESCE(SUM(total_price),0) AS total_income 
    FROM student_plan_orders 
    WHERE LOWER(status)='paid'
")->fetch_assoc()['total_income'];

// --- Monthly Revenue (current month) ---
$currentMonth = date('m');
$currentYear = date('Y');

$monthlyRevenue = $conn->query("
    SELECT COALESCE(SUM(total_price),0) AS monthly_income
    FROM student_plan_orders 
    WHERE LOWER(status)='paid'
      AND MONTH(created_at) = $currentMonth 
      AND YEAR(created_at) = $currentYear
")->fetch_assoc()['monthly_income'];

// --- Yearly Revenue (current year) ---
$yearlyRevenue = $conn->query("
    SELECT COALESCE(SUM(total_price),0) AS yearly_income
    FROM student_plan_orders 
    WHERE LOWER(status)='paid'
      AND YEAR(created_at) = $currentYear
")->fetch_assoc()['yearly_income'];

// --- Monthly Revenue Data for Chart ---
$monthlyData = [];
for ($m = 1; $m <= 12; $m++) {
    $res = $conn->query("
        SELECT COALESCE(SUM(total_price),0) AS month_total 
        FROM student_plan_orders 
        WHERE LOWER(status)='paid'
          AND MONTH(created_at) = $m 
          AND YEAR(created_at) = $currentYear
    ")->fetch_assoc();
    $monthlyData[] = (int)$res['month_total'];
}

// --- Response ---
echo json_encode([
    "total_users" => (int)$totalUsers,
    "total_schools" => (int)$totalSchools,
    "total_students" => (int)$totalStudents,
    "total_teachers" => (int)$totalTeachers,
    "total_income" => (int)$totalIncome,
    "monthly_revenue" => (int)$monthlyRevenue,
    "yearly_revenue" => (int)$yearlyRevenue,
    "monthly_revenue_data" => $monthlyData,
    "current_year" => $currentYear
]);