<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']);
    exit;
}

$school_id = (int)($_SESSION['admin_id'] ?? 0);

$page   = (int)($_GET['page'] ?? 1);
$search = trim($_GET['search'] ?? '');
$id     = (int)($_GET['id'] ?? 0);
$limit  = 10;
$offset = ($page - 1) * $limit;

// Fetch single driver
if ($id > 0) {
    $sql = "SELECT d.*, b.bus_number 
            FROM drivers d 
            LEFT JOIN buses b ON d.bus_id = b.id 
            WHERE d.id=? AND d.school_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $school_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    echo json_encode(['status' => 'success', 'data' => $res]);
    exit;
}

// Build WHERE clause
$where = "WHERE d.school_id=$school_id";
$params = [];
$types = "";

if ($search != '') {
    $like = "%" . $search . "%";
    $where .= " AND (d.name LIKE ? OR d.license_no LIKE ?)";
    $params = [$like, $like];
    $types = "ss";
}

// Count total
$sql_count = "SELECT COUNT(*) as total FROM drivers d $where";
$stmt = $conn->prepare($sql_count);
if ($search != '') $stmt->bind_param($types, ...$params);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

// Fetch drivers
$sql = "SELECT d.*, b.bus_number 
        FROM drivers d 
        LEFT JOIN buses b ON d.bus_id = b.id 
        $where 
        ORDER BY d.id DESC 
        LIMIT $offset,$limit";

$stmt = $conn->prepare($sql);
if ($search != '') $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
$no = $offset + 1;
while ($row = $res->fetch_assoc()) {
    $row['no'] = $no++;
    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data,
    'page' => $page,
    'total_pages' => $total_pages
]);
