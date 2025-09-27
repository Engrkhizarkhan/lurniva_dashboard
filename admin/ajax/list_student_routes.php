<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']);
    exit;
}

$school_id = (int)($_SESSION['admin_id'] ?? 0);
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit  = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch single assignment
if ($id > 0) {
    $stmt = $conn->prepare("
        SELECT sr.id, s.full_name AS student_name, r.route_name, sr.assigned_at
        FROM transport_student_routes sr
        JOIN students s ON sr.student_id = s.id
        JOIN transport_routes r ON sr.route_id = r.id
        WHERE sr.id = ? AND s.school_id = ? AND r.school_id = ?
    ");
    $stmt->bind_param("iii", $id, $school_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Assignment not found']);
    }
    exit;
}

// Search filter
$where = "WHERE s.school_id=? AND r.school_id=?";
$paramTypes = "ii";
$params = [$school_id, $school_id];

if ($search !== '') {
    $where .= " AND (s.full_name LIKE ? OR r.route_name LIKE ?)";
    $like = "%$search%";
    $paramTypes .= "ss";
    $params = array_merge($params, [$like, $like]);
}

// Count total rows
$sqlCount = "
    SELECT COUNT(*) as total
    FROM transport_student_routes sr
    JOIN students s ON sr.student_id = s.id
    JOIN transport_routes r ON sr.route_id = r.id
    $where
";
$countStmt = $conn->prepare($sqlCount);
$countStmt->bind_param($paramTypes, ...$params);
$countStmt->execute();
$totalResult = $countStmt->get_result()->fetch_assoc();
$total = $totalResult['total'];
$totalPages = ceil($total / $limit);

// Fetch paginated rows
$sqlData = "
    SELECT sr.id, s.full_name AS student_name, r.route_name, sr.assigned_at
    FROM transport_student_routes sr
    JOIN students s ON sr.student_id = s.id
    JOIN transport_routes r ON sr.route_id = r.id
    $where
    ORDER BY sr.id DESC
    LIMIT ? OFFSET ?
";

$paramTypesData = $paramTypes . "ii";
$paramsData = array_merge($params, [$limit, $offset]);

$stmt = $conn->prepare($sqlData);
$stmt->bind_param($paramTypesData, ...$paramsData);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
$no = $offset + 1;
while ($r = $result->fetch_assoc()) {
    $r['no'] = $no++;
    $data[] = $r;
}

echo json_encode([
    'status'      => 'success',
    'data'        => $data,
    'page'        => $page,
    'total_pages' => $totalPages
]);
