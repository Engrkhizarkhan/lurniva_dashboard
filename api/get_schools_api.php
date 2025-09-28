<?php
require_once '../admin/sass/db_config.php';

// --- CORS CONFIGURATION ---
$allowedOrigins = (($_SERVER['HTTP_HOST'] ?? '') === 'dashboard.lurniva.com')
      ? ['https://dashboard.lurniva.com/login.php', 'https://www.dashboard.lurniva.com/login.php']

    : [
        'http://localhost:8080',
        'http://localhost:8081',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:60706' // ✅ add your current Flutter port
    ];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$response = ["status" => "error", "data" => []];

$sql = "SELECT id, school_name, registration_number FROM schools ORDER BY school_name ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $schools = [];
    while ($row = $result->fetch_assoc()) {
        $schools[] = [
            "id" => $row["id"],
            "school_name" => $row["school_name"],
            "registration_number" => $row["registration_number"]
        ];
    }
    $response = [
        "status" => "success",
        "count"  => count($schools),
        "data"   => $schools
    ];
} else {
    $response = [
        "status" => "success",
        "count"  => 0,
        "data"   => []
    ];
}

echo json_encode($response);
$conn->close();
?>