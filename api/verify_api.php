<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../admin/sass/db_config.php';

// ✅ Accept only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// ✅ Parse JSON input
$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$type  = trim($data['type'] ?? ''); // "faculty" | "student"
$code  = trim($data['code'] ?? '');
$resend= $data['resend'] ?? false;

if (empty($email) || empty($type)) {
    echo json_encode(["status" => "error", "message" => "Missing email or type"]);
    exit;
}

$table = null;
if ($type === "faculty") {
    $table = "faculty";
    $field = "email";
} else if ($type === "student") {
    $table = "students";
    $field = "email";
} else if ($type === "school") {
    $table = "schools";
    $field = "school_email";
} else {
    echo json_encode(["status" => "error", "message" => "Invalid user type"]);
    exit;
}

if ($resend) {
    // ✅ Generate new code
    $newCode = rand(100000, 999999);
    $expiry  = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    $stmt = $conn->prepare("UPDATE $table SET verification_code=?, code_expires_at=? WHERE $field=? AND is_verified=0");
    $stmt->bind_param("sss", $newCode, $expiry, $email);
    $stmt->execute();

    // TODO: send email properly
    @mail($email, "Your Verification Code", "Your new code is: $newCode\n\nThis code expires in 5 minutes.", "From: noreply@yourapp.com");

    echo json_encode([
        "status" => "success",
        "message" => "New verification code sent to $email"
    ]);
    exit;
}

if (empty($code)) {
    echo json_encode(["status" => "error", "message" => "Verification code required"]);
    exit;
}

// ✅ Check code
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
    $update = $conn->prepare("UPDATE $table SET is_verified=1, verification_code=NULL, code_expires_at=NULL WHERE id=?");
    $update->bind_param("i", $row['id']);
    $update->execute();

    echo json_encode([
        "status" => "success",
        "message" => ucfirst($type) . " verified successfully"
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid code"]);
}