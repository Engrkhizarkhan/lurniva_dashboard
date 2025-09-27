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

// Fetch single book for edit
if ($id > 0) {
    $q = mysqli_query($conn, "SELECT * FROM books WHERE id=$id AND school_id=$school_id");
    if ($row = mysqli_fetch_assoc($q)) {
        echo json_encode(['status'=>'success','data'=>$row]);
    } else {
        echo json_encode(['status'=>'error','message'=>'Book not found']);
    }
    exit;
}

// Build WHERE for search
$where = "WHERE school_id=$school_id";
if ($search != '') {
    $s = mysqli_real_escape_string($conn, $search);
    $where .= " AND (title LIKE '%$s%' OR author LIKE '%$s%' OR isbn LIKE '%$s%' OR category LIKE '%$s%')";
}

// Count total
$totalRes = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM books $where");
$totalRow = mysqli_fetch_assoc($totalRes);
$total    = $totalRow['cnt'];
$total_pages = ceil($total / $limit);

// Fetch books
$q = mysqli_query($conn, "SELECT * FROM books $where ORDER BY id DESC LIMIT $offset, $limit");
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
