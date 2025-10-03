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

// ✅ Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['school_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing school_id"]);
    exit;
}

$school_id = intval($data['school_id']);

// ✅ Fetch distinct classes
$stmt = $conn->prepare("
    SELECT DISTINCT class_name 
    FROM class_timetable_meta 
    WHERE school_id = ? 
    ORDER BY class_name ASC
");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$classes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row['class_name'];
    }
}

echo json_encode([
    "status" => "success",
    "count"  => count($classes),
    "data"   => $classes
]);

$stmt->close();
$conn->close();
?>