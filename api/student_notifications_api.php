<?php
session_start();
require_once '../admin/sass/db_config.php';
header('Content-Type: application/json; charset=UTF-8');

// ✅ Verify session
$school_id  = intval($_SESSION['school_id'] ?? 0);
$student_id = intval($_SESSION['student_id'] ?? 0);

if (!$school_id || !$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$allData = [];

/* ---------------------------------------------------
   1️⃣ General Student Notifications
--------------------------------------------------- */
$stmt = $conn->prepare("
    SELECT id, type, module, title, is_read, created_at
    FROM notifications
    WHERE user_id = ? 
      AND user_type = 'student'
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $allData[] = [
        'id'         => $row['id'],
        'title'      => $row['title'],
        'status'     => $row['is_read'] == 0 ? 'Open' : 'Read',
        'type'       => $row['type'],
        'module'     => $row['module'],
        'created_at' => $row['created_at']
    ];
}

/* ---------------------------------------------------
   2️⃣ Bus Problem Notifications (last 3 hours)
--------------------------------------------------- */

// Get student's route
$routeQuery = $conn->prepare("
    SELECT route_id 
    FROM transport_student_routes 
    WHERE student_id = ? AND school_id = ?
    LIMIT 1
");
$routeQuery->bind_param("ii", $student_id, $school_id);
$routeQuery->execute();
$routeResult = $routeQuery->get_result()->fetch_assoc();

if ($routeResult) {
    $route_id = intval($routeResult['route_id']);

    // Fetch all bus IDs in this school
    $bus_ids = [];
    $busRes = $conn->query("SELECT id FROM buses WHERE school_id = $school_id");
    while ($b = $busRes->fetch_assoc()) {
        $bus_ids[] = $b['id'];
    }

    if (!empty($bus_ids)) {
        $bus_ids_str = implode(',', $bus_ids);

        $busSql = "
            SELECT bp.id, b.bus_number, bp.problem, bp.status, bp.created_at
            FROM bus_problems bp
            JOIN buses b ON b.id = bp.bus_id
            WHERE bp.school_id = $school_id
              AND bp.bus_id IN ($bus_ids_str)
              AND bp.status = 'Open'
              AND bp.created_at >= NOW() - INTERVAL 3 HOUR
            ORDER BY bp.created_at DESC
            LIMIT 10
        ";
        $busQ = $conn->query($busSql);

        while ($row = $busQ->fetch_assoc()) {
            $allData[] = [
                'id'         => 'bus_' . $row['id'],
                'title'      => "🚨 Bus #{$row['bus_number']}: {$row['problem']}",
                'status'     => $row['status'],
                'type'       => 'bus',
                'created_at' => $row['created_at']
            ];
        }
    }
}

/* ---------------------------------------------------
   3️⃣ Sort merged data by created_at DESC
--------------------------------------------------- */
usort($allData, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));

echo json_encode(['status' => 'success', 'data' => $allData], JSON_PRETTY_PRINT);