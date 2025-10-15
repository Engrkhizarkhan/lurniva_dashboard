<?php
require_once '../admin/sass/db_config.php'; // adjust path if needed

// --- ✅ Enable CORS for Flutter/Postman ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Parse JSON input
$data = json_decode(file_get_contents("php://input"), true);
$student_id = intval($data['student_id'] ?? 0);
$year       = intval($data['year'] ?? date("Y"));

// ✅ Validate input
if ($student_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing or invalid student_id."
    ]);
    exit;
}

// ✅ Fetch all fee periods for the selected year
$stmt_periods = $conn->prepare("
    SELECT id, period_name, start_date, end_date
    FROM fee_periods
    WHERE YEAR(start_date) = ?
    ORDER BY start_date ASC
");
$stmt_periods->bind_param("i", $year);
$stmt_periods->execute();
$periods = $stmt_periods->get_result();

$data = [];

while ($p = $periods->fetch_assoc()) {
    $start = $p['start_date'];
    $end   = $p['end_date'];

    // ✅ Use prepared query for attendance summary
    $sql = "
        SELECT 
            SUM(status = 'Present') AS present,
            SUM(status = 'Absent') AS absent,
            SUM(status = 'Leave') AS leave_count,
            SUM(status IS NULL OR status = '') AS missing
        FROM student_attendance
        WHERE student_id = ? AND date BETWEEN ? AND ?
    ";
    $stmt_att = $conn->prepare($sql);
    $stmt_att->bind_param("iss", $student_id, $start, $end);
    $stmt_att->execute();
    $res = $stmt_att->get_result()->fetch_assoc();

    $data[] = [
        "period"  => $p['period_name'] . " (" . date("M", strtotime($start)) . ")",
        "present" => (int)$res['present'],
        "absent"  => (int)$res['absent'],
        "leave"   => (int)$res['leave_count'],
        "missing" => (int)$res['missing']
    ];

    $stmt_att->close();
}

echo json_encode([
    "status" => "success",
    "year"   => $year,
    "count"  => count($data),
    "data"   => $data
], JSON_PRETTY_PRINT);

$stmt_periods->close();
$conn->close();
?>