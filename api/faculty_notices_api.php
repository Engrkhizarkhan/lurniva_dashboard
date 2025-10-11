<?php
require_once '../admin/sass/db_config.php';


// --- ✅ CORS CONFIGURATION ---
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// --- ✅ Handle preflight request ---
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- ✅ Read JSON input ---
$data = json_decode(file_get_contents("php://input"), true);
$school_id = intval($data['school_id'] ?? 0);
$user_type = $data['user_type'] ?? 'faculty'; // default to faculty if not provided

if (!$school_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing school_id"
    ]);
    exit;
}

// --- ✅ Set audience filter ---
if ($user_type === 'faculty') {
    $audience_condition = "('Faculty', 'Everyone')";
} elseif ($user_type === 'student') {
    $audience_condition = "('Student', 'Everyone')";
} else {
    $audience_condition = "('Everyone')";
}

// --- ✅ Fetch notices ---
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
    AND audience IN $audience_condition
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$notices = [];
while ($row = $result->fetch_assoc()) {
    $notices[] = [
        "id"          => (int)$row['id'],
        "title"       => $row['title'],
        "notice_date" => $row['notice_date'],
        "expiry_date" => $row['expiry_date'],
        "issued_by"   => $row['issued_by'],
        "purpose"     => $row['purpose'],
        "notice_type" => $row['notice_type'],
        "audience"    => $row['audience'],
        "file_url"    => $row['file_path'] 
            ? "https://dashboard.lurniva.com/uploads/notices/{$row['file_path']}" 
            : null,
        "created_at"  => $row['created_at']
    ];
}

if (count($notices) > 0) {
    echo json_encode([
        "status" => "success",
        "count"  => count($notices),
        "data"   => $notices
    ]);
} else {
    echo json_encode([
        "status" => "empty",
        "message" => "No notices available."
    ]);
}

$stmt->close();
$conn->close();
?>