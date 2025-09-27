<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']);
    exit;
}

$school_id = (int)($_SESSION['admin_id'] ?? 0);
$limit = 5; // rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');
$id = (int)($_GET['id'] ?? 0);

// Fetch single route
if ($id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM transport_routes WHERE id=$id AND school_id=$school_id");
    if ($row = mysqli_fetch_assoc($res)) {
        echo json_encode(['status' => 'success', 'data' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Route not found']);
    }
    exit;
}

// Build WHERE clause
$where = "WHERE school_id=$school_id";
if ($search) {
    $where .= " AND (route_name LIKE '%$search%' OR stops LIKE '%$search%')";
}

// Count total
$totalRes = mysqli_query($conn, "SELECT COUNT(*) as total FROM transport_routes $where");
$totalRows = mysqli_fetch_assoc($totalRes)['total'];
$totalPages = ceil($totalRows / $limit);
$offset = ($page - 1) * $limit;

// Fetch rows
$sql = "SELECT * FROM transport_routes $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$data = [];
$no = $offset + 1;
while ($row = mysqli_fetch_assoc($result)) {
    $row['no'] = $no++;
    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data,
    'page' => $page,
    'total_pages' => $totalPages
]);
