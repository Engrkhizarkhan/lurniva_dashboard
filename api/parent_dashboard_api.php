<?php
require_once '../admin/sass/db_config.php';

// --- CORS CONFIGURATION ---
$allowedOrigins = (($_SERVER['HTTP_HOST'] ?? '') === 'dashboard.lurniva.com')
        ? ['https://dashboard.lurniva.com/login.php', 'https://www.dashboard.lurniva.com/login.php']

    : [
        'http://localhost:8080',
        'http://localhost:8081',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:60706' // ✅ add your current Flutter port
    ];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Require parent_cnic (from request instead of session)
$data = json_decode(file_get_contents("php://input"), true);
$parent_cnic = $data['parent_cnic'] ?? null;

if (!$parent_cnic) {
    echo json_encode(["status" => "error", "message" => "Missing parent_cnic"]);
    exit;
}

// ---------------------------
// ✅ Fetch Children Count
// ---------------------------
$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM students WHERE parent_cnic = ?");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();
$childrenCount = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// ---------------------------
// ✅ Get all student IDs
// ---------------------------
$stmt = $conn->prepare("SELECT id FROM students WHERE parent_cnic = ?");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();
$res = $stmt->get_result();
$studentIds = [];
while ($row = $res->fetch_assoc()) {
    $studentIds[] = $row['id'];
}
$stmt->close();

$attendancePercent = 0;
if (!empty($studentIds)) {
    $studentIdsStr = implode(",", $studentIds);

    $period = $conn->query("
        SELECT id, start_date, end_date 
        FROM fee_periods 
        WHERE status = 'active' AND period_type = 'monthly'
        ORDER BY start_date DESC LIMIT 1
    ")->fetch_assoc();

    if ($period) {
        $startDate = $period['start_date'];
        $endDate   = $period['end_date'];

        $totalDays = $conn->query("
            SELECT COUNT(*) as total_days 
            FROM student_attendance 
            WHERE student_id IN ($studentIdsStr) 
              AND date BETWEEN '$startDate' AND '$endDate'
        ")->fetch_assoc()['total_days'] ?? 0;

        $presentDays = $conn->query("
            SELECT COUNT(*) as present_days 
            FROM student_attendance 
            WHERE student_id IN ($studentIdsStr) 
              AND status = 'Present' 
              AND date BETWEEN '$startDate' AND '$endDate'
        ")->fetch_assoc()['present_days'] ?? 0;

        if ($totalDays > 0) {
            $attendancePercent = round(($presentDays / $totalDays) * 100, 2);
        }
    }
}

// ---------------------------
// ✅ Pending Fee
// ---------------------------
$stmt = $conn->prepare("
    SELECT SUM(balance_due) AS pending_fee
    FROM fee_slip_details
    WHERE student_id IN (SELECT id FROM students WHERE parent_cnic = ?)
");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();
$pendingFee = $stmt->get_result()->fetch_assoc()['pending_fee'] ?? 0;
$stmt->close();

// ---------------------------
// ✅ Behavior Alerts
// ---------------------------
$stmt = $conn->prepare("
    SELECT COUNT(*) AS alerts 
    FROM student_behavior
    WHERE student_id IN (SELECT id FROM students WHERE parent_cnic = ?) 
      AND parent_approved=0
");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();
$behaviorAlerts = $stmt->get_result()->fetch_assoc()['alerts'] ?? 0;
$stmt->close();

// ---------------------------
// ✅ Attendance Trend (last 7 days)
// ---------------------------
$stmt = $conn->prepare("
    SELECT DATE(date) AS date, 
           AVG(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) * 100 AS percentage
    FROM student_attendance
    WHERE student_id IN (SELECT id FROM students WHERE parent_cnic = ?)
    GROUP BY DATE(date)
    ORDER BY date DESC
    LIMIT 7
");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();
$res = $stmt->get_result();
$attendanceTrend = [];
while ($row = $res->fetch_assoc()) {
    $attendanceTrend[] = [
        "date" => $row['date'],
        "percentage" => round($row['percentage'], 2)
    ];
}
$stmt->close();
$attendanceTrend = array_reverse($attendanceTrend);

// ---------------------------
// ✅ Fee Status (Paid vs Pending)
// ---------------------------
$stmt = $conn->prepare("
    SELECT 
        SUM(amount_paid) AS paid, 
        SUM(balance_due) AS pending
    FROM fee_slip_details
    WHERE student_id IN (SELECT id FROM students WHERE parent_cnic = ?)
");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$feePaid    = $res['paid'] ?? 0;
$feePending = $res['pending'] ?? 0;
$stmt->close();

// ---------------------------
// ✅ Children Details
// ---------------------------
$stmt = $conn->prepare("
    SELECT id, full_name, class_grade, section, profile_photo 
    FROM students 
    WHERE parent_cnic = ?
");
$stmt->bind_param("s", $parent_cnic);
$stmt->execute();
$res = $stmt->get_result();
$children = [];

while ($student = $res->fetch_assoc()) {
    // Attendance % for each student
    $attStmt = $conn->prepare("
        SELECT 
          SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS presents,
          COUNT(*) AS total_days
        FROM student_attendance
        WHERE student_id = ?
    ");
    $attStmt->bind_param("i", $student['id']);
    $attStmt->execute();
    $attData = $attStmt->get_result()->fetch_assoc();
    $attStmt->close();

    $studentAttendance = 0;
    if ($attData['total_days'] > 0) {
        $studentAttendance = round(($attData['presents'] / $attData['total_days']) * 100, 2);
    }

    $children[] = [
        "id" => $student['id'],
        "full_name" => $student['full_name'],
        "class_grade" => $student['class_grade'],
        "section" => $student['section'],
        "profile_photo" => $student['profile_photo'] ?: "default.png",
        "attendance_percent" => $studentAttendance
    ];
}
$stmt->close();

// ✅ Response
echo json_encode([
    "status" => "success",
    "data" => [
        "children_count" => $childrenCount,
        "attendance_percent" => $attendancePercent,
        "pending_fee" => $pendingFee,
        "behavior_alerts" => $behaviorAlerts,
        "attendance_trend" => $attendanceTrend,
        "fee_status" => [
            "paid" => $feePaid,
            "pending" => $feePending
        ],
        "children" => $children
    ]
]);

$conn->close();
?>