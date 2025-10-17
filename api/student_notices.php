<?php
session_start();
require_once '../admin/sass/db_config.php'; // ✅ use consistent db path
header('Content-Type: application/json; charset=UTF-8');

// Accept both session & POST/JSON body input
$data = json_decode(file_get_contents('php://input'), true);
$student_id = intval($_SESSION['student_id'] ?? $data['student_id'] ?? $_POST['student_id'] ?? 0);
$school_id  = intval($_SESSION['school_id'] ?? $data['school_id'] ?? $_POST['school_id'] ?? 0);

if (!$student_id || !$school_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or session expired']);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        id, 
        title, 
        notice_date, 
        expiry_date, 
        issued_by, 
        purpose, 
        notice_type, 
        audience, 
        file_path, 
        created_at
    FROM digital_notices
    WHERE school_id = ? 
      AND (audience = 'All Students' OR audience = 'Everyone')
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$notices = [];
while ($row = $result->fetch_assoc()) {
    $file_url = '';
    if (!empty($row['file_path'])) {
        $base = rtrim((isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'], '/');
        $file_url = "{$base}/uploads/notices/" . rawurlencode($row['file_path']);
    }

    $notices[] = [
        'id'           => (int)$row['id'],
        'title'        => $row['title'],
        'notice_date'  => $row['notice_date'],
        'expiry_date'  => $row['expiry_date'],
        'issued_by'    => $row['issued_by'],
        'purpose'      => $row['purpose'],
        'notice_type'  => $row['notice_type'],
        'audience'     => $row['audience'],
        'file_url'     => $file_url,
        'created_at'   => $row['created_at']
    ];
}

echo json_encode([
    'status' => 'success',
    'count'  => count($notices),
    'data'   => $notices
]);
?>