<?php
require_once '../admin/sass/db_config.php'; // ✅ adjust path
session_start();
header('Content-Type: application/json; charset=UTF-8');

// 🧩 Allow API test without session (Postman/Flutter)
$data = json_decode(file_get_contents("php://input"), true);
$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? $_POST['student_id'] ?? 0);
$school_id  = intval($_SESSION['school_id'] ?? $data['school_id'] ?? $_POST['school_id'] ?? 0);

if (!$student_id || !$school_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or missing parameters']);
    exit;
}

$year = date("Y");

// ✅ Get all fee periods for the current year
$stmt = $conn->prepare("
    SELECT id, period_name, start_date, end_date 
    FROM fee_periods 
    WHERE YEAR(start_date) = ? 
    ORDER BY start_date ASC
");
$stmt->bind_param("i", $year);
$stmt->execute();
$periods = $stmt->get_result();

if ($periods->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => "No periods found for $year"]);
    exit;
}

$response = [];
while ($period = $periods->fetch_assoc()) {
    $start_date = $period['start_date'];
    $end_date   = $period['end_date'];
    $month      = date("m", strtotime($start_date));
    $yearSel    = date("Y", strtotime($start_date));
    $daysInMonth = date("t", strtotime($start_date));

    // ✅ Fetch attendance for this period
    $sql = "
        SELECT date, status 
        FROM student_attendance 
        WHERE student_id = ? 
        AND school_id = ? 
        AND date BETWEEN ? AND ?
    ";
    $stmt2 = $conn->prepare($sql);
    $stmt2->bind_param("iiss", $student_id, $school_id, $start_date, $end_date);
    $stmt2->execute();
    $attendanceRes = $stmt2->get_result();

    $attendance = [];
    while ($row = $attendanceRes->fetch_assoc()) {
        $day = (int)date("j", strtotime($row['date']));
        $attendance[$day] = strtoupper(substr($row['status'], 0, 1));
    }

    // ✅ Generate daily record summary
    $days = [];
    for ($d = 1; $d <= $daysInMonth; $d++) {
        $currentDate = "$yearSel-$month-" . str_pad($d, 2, "0", STR_PAD_LEFT);
        $val = "N"; // Default: No record

        if (date("w", strtotime($currentDate)) == 0) {
            $val = "S"; // Sunday
        } elseif (isset($attendance[$d])) {
            $val = $attendance[$d];
        }

        // Map code meanings
        $statusFull = [
            "P" => "Present",
            "A" => "Absent",
            "L" => "Leave",
            "S" => "Sunday",
            "N" => "No Record"
        ][$val];

        $days[] = [
            "day" => $d,
            "status_code" => $val,
            "status_text" => $statusFull
        ];
    }

    $response[] = [
        "period_id" => (int)$period['id'],
        "period_name" => $period['period_name'],
        "month" => date("M-Y", strtotime($start_date)),
        "days" => $days
    ];
}

echo json_encode([
    "status" => "success",
    "year" => $year,
    "student_id" => $student_id,
    "data" => $response
]);