<?php
session_start();
header('Content-Type: application/json');

require '../sass/db_config.php';

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Get current year and month for revenue filter
$currentYear = date('Y');
$currentMonth = date('m');

// Total Students
$stmt = $conn->prepare("SELECT COUNT(*) as total_students FROM students WHERE school_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res_students = $stmt->get_result();
$total_students = ($res_students->num_rows > 0) ? $res_students->fetch_assoc()['total_students'] : 0;
$stmt->close();

// Total Teachers
$stmt = $conn->prepare("SELECT COUNT(*) as total_teachers FROM faculty WHERE campus_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res_teachers = $stmt->get_result();
$total_teachers = ($res_teachers->num_rows > 0) ? $res_teachers->fetch_assoc()['total_teachers'] : 0;
$stmt->close();

// Total Tasks
$stmt = $conn->prepare("SELECT COUNT(*) as total_tasks FROM school_tasks WHERE school_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res_tasks = $stmt->get_result();
$total_tasks = ($res_tasks->num_rows > 0) ? $res_tasks->fetch_assoc()['total_tasks'] : 0;
$stmt->close();

// Current Month Revenue
$sql_revenue = "
    SELECT IFNULL(SUM(amount_paid), 0) AS total_revenue 
    FROM fee_slip_details 
    WHERE school_id = ? 
    AND YEAR(payment_date) = ? 
    AND MONTH(payment_date) = ?
";

$stmt = $conn->prepare($sql_revenue);
$stmt->bind_param("iii", $admin_id, $currentYear, $currentMonth);
$stmt->execute();
$res_revenue = $stmt->get_result();
$total_revenue = ($res_revenue->num_rows > 0) ? $res_revenue->fetch_assoc()['total_revenue'] : 0;
$stmt->close();

$formatted_revenue = number_format($total_revenue);

echo json_encode([
    'total_students' => (int)$total_students,
    'total_teachers' => (int)$total_teachers,
    'total_tasks' => (int)$total_tasks,
    'total_revenue' => $formatted_revenue,
    'current_month' => date('M')  // Optional: send current month name for UI
]);