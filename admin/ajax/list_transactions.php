<?php
session_start();
require_once '../sass/db_config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session expired']); 
    exit;
}

$school_id = (int)($_SESSION['admin_id'] ?? 0);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // records per page
$offset = ($page - 1) * $limit;

// Search filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Count total rows
$count_q = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM library_transactions t
    LEFT JOIN books b ON t.book_id = b.id
    LEFT JOIN students s ON t.student_id = s.id
    LEFT JOIN faculty f ON t.faculty_id = f.id
    WHERE (b.title LIKE '%$search%' 
       OR s.full_name LIKE '%$search%' 
       OR f.full_name LIKE '%$search%')
      AND t.school_id = $school_id
");
$total_rows = mysqli_fetch_assoc($count_q)['total'];
$total_pages = ceil($total_rows / $limit);

// Fetch paginated transactions
$q = mysqli_query($conn, "
    SELECT 
        t.*,
        b.title AS book_title,
        s.full_name AS student_name,
        f.full_name AS faculty_name,
        lf.fine_amount,
        lf.paid_status
    FROM library_transactions t
    LEFT JOIN books b ON t.book_id = b.id
    LEFT JOIN students s ON t.student_id = s.id
    LEFT JOIN faculty f ON t.faculty_id = f.id
    LEFT JOIN library_fines lf ON lf.transaction_id = t.id
    WHERE (b.title LIKE '%$search%' 
       OR s.full_name LIKE '%$search%' 
       OR f.full_name LIKE '%$search%')
      AND t.school_id = $school_id
    ORDER BY FIELD(t.status,'Issued','Overdue','Returned'), t.id DESC
    LIMIT $offset, $limit
");

$data = [];
while ($row = mysqli_fetch_assoc($q)) {
    // Mark Overdue
    if ($row['status'] === 'Issued' && date('Y-m-d') > $row['due_date']) {
        $row['status'] = 'Overdue';
    }
    $data[] = $row;
}

echo json_encode([
    'status'=>'success',
    'data'=>$data,
    'page'=>$page,
    'total_pages'=>$total_pages
]);
