<?php
require_once '../sass/db_config.php';
session_start();

if (!isset($_SESSION['school_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$student_id = $_POST['studentId'];
$year = date("Y");

// Get all fee periods for this year
$periods = $conn->query("
    SELECT id, period_name, start_date, end_date 
    FROM fee_periods 
    WHERE YEAR(start_date) = '$year'
    ORDER BY start_date ASC
");

$data = [];

while ($p = $periods->fetch_assoc()) {
    $start = $p['start_date'];
    $end   = $p['end_date'];

    // ✅ Use backticks or alias instead of reserved keyword "leave"
    $sql = "
        SELECT 
            SUM(status='Present') AS present,
            SUM(status='Absent') AS absent,
            SUM(status='Leave') AS leave_count,
            SUM(status IS NULL OR status='') AS missing
        FROM student_attendance 
        WHERE student_id = '$student_id'
        AND date BETWEEN '$start' AND '$end'
    ";

    $res = $conn->query($sql)->fetch_assoc();

    $data[] = [
        "period"  => $p['period_name'] . " (" . date("M", strtotime($start)) . ")",
        "present" => (int)$res['present'],
        "absent"  => (int)$res['absent'],
        "leave"   => (int)$res['leave_count'],
        "missing" => (int)$res['missing']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);