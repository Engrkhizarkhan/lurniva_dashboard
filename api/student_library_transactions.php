<?php
session_start();
require_once '../admin/sass/db_config.php'; // ✅ consistent path
header('Content-Type: application/json; charset=UTF-8');

// Allow JSON body for API use
$data = json_decode(file_get_contents("php://input"), true);
$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? $_POST['student_id'] ?? 0);

if (!$student_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or session expired']);
    exit;
}

// Pagination
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : intval($data['page'] ?? 1);
$limit  = 10;
$offset = ($page - 1) * $limit;

// Search filter
$search = trim($_GET['search'] ?? $data['search'] ?? '');
$searchLike = '%' . $search . '%';

// Count total
$where = "WHERE t.student_id = ? AND (t.status='Issued' OR t.status='Overdue')";
if ($search !== '') {
    $where .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.category LIKE ?)";
}

$countSql = "SELECT COUNT(*) AS total 
             FROM library_transactions t
             LEFT JOIN books b ON t.book_id = b.id
             $where";
$countStmt = $conn->prepare($countSql);

if ($search !== '') {
    $countStmt->bind_param("isss", $student_id, $searchLike, $searchLike, $searchLike);
} else {
    $countStmt->bind_param("i", $student_id);
}
$countStmt->execute();
$totalRes = $countStmt->get_result()->fetch_assoc();
$total = intval($totalRes['total']);
$total_pages = ceil($total / $limit);

// Fetch data
$sql = "SELECT 
            t.id, t.book_id, t.issue_date, t.due_date, t.return_date, t.status,
            b.title AS book_title, b.author, b.category
        FROM library_transactions t
        LEFT JOIN books b ON t.book_id = b.id
        $where
        ORDER BY FIELD(t.status,'Issued','Overdue','Returned'), t.id DESC
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);

if ($search !== '') {
    $stmt->bind_param("isssii", $student_id, $searchLike, $searchLike, $searchLike, $offset, $limit);
} else {
    $stmt->bind_param("iii", $student_id, $offset, $limit);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    // ✅ Mark overdue books
    if ($row['status'] === 'Issued' && date('Y-m-d') > $row['due_date']) {
        $row['status'] = 'Overdue';
    }
    $data[] = [
        'id'          => (int)$row['id'],
        'book_id'     => (int)$row['book_id'],
        'book_title'  => $row['book_title'],
        'author'      => $row['author'],
        'category'    => $row['category'],
        'issue_date'  => $row['issue_date'],
        'due_date'    => $row['due_date'],
        'return_date' => $row['return_date'],
        'status'      => $row['status']
    ];
}

echo json_encode([
    'status'       => 'success',
    'page'         => $page,
    'total_pages'  => $total_pages,
    'total_records'=> $total,
    'data'         => $data
]);
?>