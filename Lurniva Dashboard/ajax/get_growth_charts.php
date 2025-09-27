<?php
require_once '../sass/db_config.php';
header('Content-Type: application/json');

$currentYear = date("Y");

// --- Schools added per month ---
$schools_per_month = array_fill(0, 12, 0);
$res = $conn->query("
    SELECT MONTH(created_at) as m, COUNT(*) as total 
    FROM schools 
    WHERE YEAR(created_at) = $currentYear
    GROUP BY MONTH(created_at)
");
while ($row = $res->fetch_assoc()) {
    $schools_per_month[$row['m'] - 1] = (int)$row['total'];
}

// --- Students added per month ---
$students_per_month = array_fill(0, 12, 0);
$res = $conn->query("
    SELECT MONTH(created_at) as m, COUNT(*) as total 
    FROM students 
    WHERE YEAR(created_at) = $currentYear
    GROUP BY MONTH(created_at)
");
while ($row = $res->fetch_assoc()) {
    $students_per_month[$row['m'] - 1] = (int)$row['total'];
}

// --- Faculty added per month ---
$faculty_per_month = array_fill(0, 12, 0);
$res = $conn->query("
    SELECT MONTH(created_at) as m, COUNT(*) as total 
    FROM faculty 
    WHERE YEAR(created_at) = $currentYear
    GROUP BY MONTH(created_at)
");
while ($row = $res->fetch_assoc()) {
    $faculty_per_month[$row['m'] - 1] = (int)$row['total'];
}

// --- Revenue growth per month ---
$revenue_per_month = array_fill(0, 12, 0);
$res = $conn->query("
    SELECT MONTH(created_at) as m, SUM(total_price) as total 
    FROM student_plan_orders 
    WHERE YEAR(created_at) = $currentYear AND LOWER(status)='paid'
    GROUP BY MONTH(created_at)
");
while ($row = $res->fetch_assoc()) {
    $revenue_per_month[$row['m'] - 1] = (int)$row['total'];
}

// --- Final JSON ---
echo json_encode([
    "schools_per_month" => $schools_per_month,
    "students_per_month" => $students_per_month,
    "faculty_per_month" => $faculty_per_month,
    "revenue_per_month" => $revenue_per_month,
    "year" => $currentYear
]);