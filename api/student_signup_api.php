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
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Read JSON
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

// ✅ Type check (student | parent)
$type = strtolower(trim($data["type"] ?? ""));
if (!in_array($type, ["student", "parent"])) {
    echo json_encode(["status" => "error", "message" => "Invalid signup type"]);
    exit;
}

// ✅ Common fields
$email    = trim($data["email"] ?? "");
$password = $data["password"] ?? "";
$fullName = trim($data["full_name"] ?? "");
if (empty($email) || empty($password) || empty($fullName)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ✅ Profile photo
$profile_name = null;
if (!empty($data["profile_photo"])) {
    $profile_name = time() . "_$type.png";
    $imageData = base64_decode($data["profile_photo"]);
    $path = "../$type/uploads/profile/";
    if (!is_dir($path)) mkdir($path, 0777, true);
    file_put_contents($path . $profile_name, $imageData);
}

// ✅ Verification fields
$verification_code = rand(100000, 999999);
$is_verified = 0;
$code_expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));
$verification_attempts = 0;
$status = "pending";

if ($type === "parent") {
    // ================== PARENT SIGNUP ==================
    $parentCnic = trim($data["parent_cnic"] ?? "");
    $phone      = trim($data["phone"] ?? "");

    if (empty($parentCnic) || empty($phone)) {
        echo json_encode(["status" => "error", "message" => "Missing CNIC or phone"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO parents 
        (full_name, parent_cnic, email, phone, profile_photo,
         password, status, verification_code, is_verified, code_expires_at, verification_attempts)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sssssssissi",
        $fullName, $parentCnic, $email, $phone, $profile_name,
        $hashedPassword, $status, $verification_code, $is_verified, $code_expires_at, $verification_attempts
    );

    if ($stmt->execute()) {
        $id = $conn->insert_id;
        $subject = "Parent Account Verification - Lurniva";
        $message = "Hello $fullName,\n\nYour verification code is: $verification_code\n\nThis code expires in 5 minutes.\n\nRegards,\nLurniva Support";
        $headers = "From: Support@lurniva.com";

        @mail($email, $subject, $message, $headers);

        echo json_encode([
            "status" => "success",
            "type"   => "parent",
            "message"=> "Parent registered. OTP sent to $email.",
            "parent_id" => $id
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
    $stmt->close();

} elseif ($type === "student") {
    // ================== STUDENT SIGNUP ==================
    $schoolId   = intval($data["school_id"] ?? 0);
    $parentName = trim($data["parent_name"] ?? "");
    $parentCnic = trim($data["parent_cnic"] ?? "");
    $gender     = trim($data["gender"] ?? "");
    $dob        = trim($data["dob"] ?? "");
    $classGrade = trim($data["class_grade"] ?? "");
    $section    = trim($data["section"] ?? "");
    $rollNo     = trim($data["roll_number"] ?? "");
    $address    = trim($data["address"] ?? "");
    $phone      = trim($data["phone"] ?? "");

    if (empty($schoolId) || empty($parentCnic) || empty($classGrade) || empty($section)) {
        echo json_encode(["status" => "error", "message" => "Missing required student fields"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO students 
        (school_id, parent_name, parent_cnic, full_name, gender, dob,
         class_grade, section, roll_number, address, email, phone, profile_photo,
         password, status, verification_code, is_verified, code_expires_at, verification_attempts)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "issssssssssssssissi",
        $schoolId, $parentName, $parentCnic, $fullName, $gender, $dob,
        $classGrade, $section, $rollNo, $address, $email, $phone, $profile_name,
        $hashedPassword, $status, $verification_code, $is_verified, $code_expires_at, $verification_attempts
    );

    if ($stmt->execute()) {
        $id = $conn->insert_id;
        $subject = "Student Account Verification - Lurniva";
        $message = "Hello $fullName,\n\nYour verification code is: $verification_code\n\nThis code expires in 5 minutes.\n\nRegards,\nLurniva Support";
        $headers = "From: Support@lurniva.com";

        @mail($email, $subject, $message, $headers);

        echo json_encode([
            "status" => "success",
            "type"   => "student",
            "message"=> "Student registered. OTP sent to $email.",
            "student_id" => $id
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
    $stmt->close();
}

$conn->close();
?>