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

if (!isset($data['school_id']) || !isset($data['class_name'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

$school_id  = intval($data['school_id']);
$class_name = trim($data['class_name']);

// ✅ Fetch distinct sections
$stmt = $conn->prepare("
    SELECT DISTINCT section 
    FROM class_timetable_meta 
    WHERE school_id = ? AND class_name = ?
    ORDER BY section ASC
");
$stmt->bind_param("is", $school_id, $class_name);
$stmt->execute();
$result = $stmt->get_result();

$sections = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row['section'];
    }
}

echo json_encode([
    "status" => "success",
    "count"  => count($sections),
    "data"   => $sections
]);

$stmt->close();
$conn->close();
?>