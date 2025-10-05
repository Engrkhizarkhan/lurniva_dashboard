<?php
require_once '../admin/sass/db_config.php';
require_once '../mail_library.php'; // ✅ include your email library

// --- CORS CONFIGURATION ---
$allowedOrigins = (($_SERVER['HTTP_HOST'] ?? '') === 'dashboard.lurniva.com')
    ? ['https://dashboard.lurniva.com/login.php', 'https://www.dashboard.lurniva.com/login.php']
    : [
        'http://localhost:8080',
        'http://localhost:8081',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:60706' // ✅ Flutter web port
    ];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Accept only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// ✅ Parse JSON input
$data   = json_decode(file_get_contents("php://input"), true);
$email  = trim($data['email'] ?? '');
$type   = trim($data['type'] ?? '');   // "faculty" | "student" | "parent"
$code   = trim($data['code'] ?? '');
$resend = $data['resend'] ?? false;

if (empty($email) || empty($type)) {
    echo json_encode(["status" => "error", "message" => "Missing email or type"]);
    exit;
}

// Map type → table + field
switch ($type) {
    case "faculty":
        $table = "faculty";
        $field = "email";
        break;
    case "student":
        $table = "students";
        $field = "email";
        break;
    case "parent":
        $table = "parents";
        $field = "email";
        break;
    default:
        echo json_encode(["status" => "error", "message" => "Invalid user type"]);
        exit;
}

// ✅ Handle resend request
if ($resend) {
    $newCode = rand(100000, 999999);
    $expiry  = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    $stmt = $conn->prepare("UPDATE $table SET verification_code=?, code_expires_at=? WHERE $field=? AND is_verified=0");
    $stmt->bind_param("sss", $newCode, $expiry, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // ✅ Send email via library
        $subject = "Your Verification Code";
        $body = "
        <p>Hello,</p>
        <p>Your new verification code is: <b>$newCode</b></p>
        <p>This code will expire in <b>5 minutes</b>.</p>
        <p>— Lurniva Support</p>
        ";
        $sent = sendmail($email, $subject, $body);

        if ($sent) {
            echo json_encode(["status" => "success", "message" => "New verification code sent to $email"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to send email"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Account not found or already verified"]);
    }

    $stmt->close();
    $conn->close();
    exit;
}

// ✅ Normal verification flow
if (empty($code)) {
    echo json_encode(["status" => "error", "message" => "Verification code required"]);
    exit;
}

$stmt = $conn->prepare("SELECT id, code_expires_at FROM $table 
    WHERE $field=? AND verification_code=? AND is_verified=0");
$stmt->bind_param("ss", $email, $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Check expiry
    if (strtotime($row['code_expires_at']) < time()) {
        echo json_encode(["status" => "error", "message" => "Verification code expired"]);
        exit;
    }

    // ✅ Mark verified
    $update = $conn->prepare("UPDATE $table 
        SET is_verified=1, verification_code=NULL, code_expires_at=NULL 
        WHERE id=?");
    $update->bind_param("i", $row['id']);
    $update->execute();

    echo json_encode([
        "status" => "success",
        "message" => ucfirst($type) . " verified successfully"
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid code"]);
}

$stmt->close();
$conn->close();
?>