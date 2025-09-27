<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']);
    exit;
}

$school_id = (int)($_SESSION['admin_id'] ?? 0);

// If specific single id requested
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id > 0) {
    $res = mysqli_query($conn, "SELECT * FROM buses WHERE id=$id AND school_id=$school_id LIMIT 1");
    if ($row = mysqli_fetch_assoc($res)) {
        echo json_encode(['status'=>'success','data'=>$row]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Bus not found']);
    }
    exit;
}

$page = isset($_GET['page']) ? max(1,intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

$where = "WHERE school_id=$school_id";
if ($search !== '') {
    $where .= " AND (bus_number LIKE '%$search%' OR status LIKE '%$search%')";
}

// Count total
$totalRes = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM buses $where");
$totalRow = mysqli_fetch_assoc($totalRes);
$total = intval($totalRow['cnt']);
$total_pages = ($total > 0) ? ceil($total / $limit) : 1;

// Fetch records
$q = mysqli_query($conn, "SELECT * FROM buses $where ORDER BY id DESC LIMIT $offset, $limit");
$data = [];
$no = $offset + 1;
while ($row = mysqli_fetch_assoc($q)) {
    $row['no'] = $no++;
    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data,
    'page' => $page,
    'total_pages' => $total_pages
]);
