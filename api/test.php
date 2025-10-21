<?php
require_once '../admin/sass/db_config.php'; // adjust path as needed
header('Content-Type: application/json; charset=UTF-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Optional: Pagination or filtering
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 100;
$offset = ($page - 1) * $limit;

// Main Query
$sql = "SELECT 
            id, school_name, school_type, registration_number, affiliation_board,
            school_email, school_phone, school_website, country, state, city,
            address, logo, admin_contact_person, username, admin_email, admin_phone,
            password, verification_code, is_verified, status, code_expires_at,
            verification_attempts, created_at, subscription_start, subscription_end,
            num_students
        FROM schools
        ORDER BY created_at DESC
        LIMIT ?, ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

$schools = [];
while ($row = $result->fetch_assoc()) {
    // You may want to remove passwords from the output for security reasons
    unset($row['password']);
    $schools[] = $row;
}

// Response
if (empty($schools)) {
    echo json_encode([
        "status" => "error",
        "message" => "No schools found"
    ]);
    exit;
}

echo json_encode([
    "status" => "success",
    "count" => count($schools),
    "page" => $page,
    "data" => $schools
]);
?>