<?php
session_start();
require_once '../admin/sass/db_config.php';

// --- ✅ Enable CORS (for Flutter/Postman) ---
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Check login session
if (!isset($_SESSION['admin_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized: session expired or invalid.'
    ]);
    exit;
}

$faculty_id = intval($_SESSION['admin_id']) ?? $data['admin_id'];

// ✅ Fetch faculty profile
$stmt = $conn->prepare("
    SELECT 
        id, 
        campus_id, 
        full_name, 
        cnic, 
        qualification, 
        subjects, 
        email, 
        phone, 
        address, 
        joining_date, 
        employment_type, 
        schedule_preference, 
        photo, 
        status, 
        rating 
    FROM faculty 
    WHERE id = ?
");
$stmt->bind_param('i', $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

// ✅ Check if faculty exists
if ($result->num_rows === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Faculty not found.'
    ]);
    exit;
}

$data = $result->fetch_assoc();

// ✅ Return successful response
echo json_encode([
    'status' => 'success',
    'faculty_id' => $faculty_id,
    'data' => $data
], JSON_PRETTY_PRINT);

// Cleanup
$stmt->close();
$conn->close();
?>