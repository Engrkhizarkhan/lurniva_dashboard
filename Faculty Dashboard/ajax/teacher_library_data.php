<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if(!isset($_SESSION['admin_id'])){
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$teacher_id = $_SESSION['admin_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page-1)*$limit;
$search = mysqli_real_escape_string($conn, $_GET['search'] ?? '');

// Count total
$where = "WHERE t.faculty_id = $teacher_id";
if($search){
    $where .= " AND (b.title LIKE '%$search%' OR b.author LIKE '%$search%' OR b.category LIKE '%$search%')";
}

$totalRes = mysqli_query($conn, "
    SELECT COUNT(*) as total
    FROM library_transactions t
    LEFT JOIN books b ON t.book_id = b.id
    $where AND (t.status='Issued' OR t.status='Overdue')
");
$total_rows = mysqli_fetch_assoc($totalRes)['total'];
$total_pages = ceil($total_rows/$limit);

// Fetch data
$q = mysqli_query($conn, "
    SELECT t.*, b.title AS book_title, b.author, b.category, b.available
    FROM library_transactions t
    LEFT JOIN books b ON t.book_id = b.id
    $where AND (t.status='Issued' OR t.status='Overdue')
    ORDER BY FIELD(t.status,'Overdue','Issued'), t.id DESC
    LIMIT $offset, $limit
");

$data = [];
$no = $offset+1;
while($row = mysqli_fetch_assoc($q)){
    if($row['status'] === 'Issued' && date('Y-m-d') > $row['due_date']){
        $row['status'] = 'Overdue';
    }
    $row['no'] = $no++;
    $data[] = $row;
}

echo json_encode([
    'status'=>'success',
    'data'=>$data,
    'page'=>$page,
    'total_pages'=>$total_pages
]);
