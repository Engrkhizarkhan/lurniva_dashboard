<?php
require_once '../admin/sass/db_config.php';
require_once '../mail_library.php'; // ✅ include your PHPMailer library

// --- CORS CONFIGURATION ---
$allowedOrigins = ($_SERVER['HTTP_HOST'] === 'dashboard.lurniva.com')
    ? ['https://dashboard.lurniva.com/login.php', 'https://www.dashboard.lurniva.com/login.php']
    : [
        'http://localhost:8080',
        'http://localhost:8081',
        'http://localhost:3000',
        'http://localhost:5173',
        'http://localhost:60706'
    ];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- Only allow POST ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// --- Get JSON input ---
$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$current_date = date("Y-m-d");

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Email and password are required"]);
    exit;
}

// ========================
// FACULTY LOGIN
// ========================
$stmt = $conn->prepare("
    SELECT id, campus_id, full_name, email, password, photo, is_verified, status, subscription_end
    FROM faculty
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $faculty = $result->fetch_assoc();

    // Subscription check
    if (empty($faculty['subscription_end']) || $faculty['subscription_end'] < $current_date) {
        $conn->query("UPDATE faculty SET status='Pending' WHERE id=" . $faculty['id']);
        echo json_encode(["status" => "error", "message" => "Faculty subscription expired. Please renew."]);
        exit;
    }

    if (!password_verify($password, $faculty['password'])) {
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
        exit;
    }

    if ($faculty['is_verified'] == 1) {
        echo json_encode([
            "status" => "success",
            "type"   => "faculty",
            "data"   => [
                "id" => $faculty['id'],
                "full_name" => $faculty['full_name'],
                "email" => $faculty['email'],
                "campus_id" => $faculty['campus_id'],
                "photo" => $faculty['photo']
            ]
        ]);
        exit;
    } else {
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        $conn->query("UPDATE faculty SET verification_code='$otp', code_expires_at='$expiry' WHERE id=" . $faculty['id']);

        $body = "
            <h2>Faculty Account Verification</h2>
            <p>Hello <strong>{$faculty['full_name']}</strong>,</p>
            <p>Your OTP is: <strong>$otp</strong></p>
            <p>This code expires in <b>5 minutes</b>.</p>
            <p>– Lurniva Support</p>
        ";

        sendMail($faculty['email'], "Faculty Account Verification", $body);

        echo json_encode([
            "status" => "verify_required",
            "type" => "faculty",
            "message" => "Verification required. OTP sent to email."
        ]);
        exit;
    }
}

// ========================
// STUDENT LOGIN
// ========================
$stmt = $conn->prepare("
    SELECT id, school_id, full_name, email, password, profile_photo, is_verified, status, subscription_end
    FROM students
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $student = $result->fetch_assoc();

    if (empty($student['subscription_end']) || $student['subscription_end'] < $current_date) {
        $conn->query("UPDATE students SET status='Pending' WHERE id=" . $student['id']);
        echo json_encode(["status" => "error", "message" => "Student subscription expired. Please renew."]);
        exit;
    }

    if (!password_verify($password, $student['password'])) {
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
        exit;
    }

    if ($student['is_verified'] == 1) {
        echo json_encode([
            "status" => "success",
            "type"   => "student",
            "data"   => [
                "id" => $student['id'],
                "full_name" => $student['full_name'],
                "email" => $student['email'],
                "school_id" => $student['school_id'],
                "photo" => $student['profile_photo']
            ]
        ]);
        exit;
    } else {
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        $conn->query("UPDATE students SET verification_code='$otp', code_expires_at='$expiry' WHERE id=" . $student['id']);

        $body = "
            <h2>Student Account Verification</h2>
            <p>Hello <strong>{$student['full_name']}</strong>,</p>
            <p>Your OTP is: <strong>$otp</strong></p>
            <p>This code expires in <b>5 minutes</b>.</p>
            <p>– Lurniva Support</p>
        ";

        sendMail($student['email'], "Student Account Verification", $body);

        echo json_encode([
            "status" => "verify_required",
            "type" => "student",
            "message" => "Verification required. OTP sent to email."
        ]);
        exit;
    }
}

// ========================
// PARENT LOGIN
// ========================
$stmt = $conn->prepare("
    SELECT id, full_name, parent_cnic, email, phone, profile_photo, password, status, is_verified, subscription_end
    FROM parents
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $parent = $result->fetch_assoc();

    if (empty($parent['subscription_end']) || $parent['subscription_end'] < $current_date) {
        $conn->query("UPDATE parents SET status='Pending' WHERE id=" . $parent['id']);
        echo json_encode(["status" => "error", "message" => "Parent subscription expired. Please renew."]);
        exit;
    }

    if (!password_verify($password, $parent['password'])) {
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
        exit;
    }

    if ($parent['is_verified'] == 1) {
        echo json_encode([
            "status" => "success",
            "type"   => "parent",
            "data"   => [
                "id" => $parent['id'],
                "full_name" => $parent['full_name'],
                "email" => $parent['email'],
                "phone" => $parent['phone'],
                "cnic" => $parent['parent_cnic'],
                "photo" => $parent['profile_photo']
            ]
        ]);
        exit;
    } else {
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        $conn->query("UPDATE parents SET verification_code='$otp', code_expires_at='$expiry' WHERE id=" . $parent['id']);

        $body = "
            <h2>Parent Account Verification</h2>
            <p>Hello <strong>{$parent['full_name']}</strong>,</p>
            <p>Your OTP is: <strong>$otp</strong></p>
            <p>This code expires in <b>5 minutes</b>.</p>
            <p>– Lurniva Support</p>
        ";

        sendMail($parent['email'], "Parent Account Verification", $body);

        echo json_encode([
            "status" => "verify_required",
            "type" => "parent",
            "message" => "Verification required. OTP sent to email."
        ]);
        exit;
    }
}

// ========================
// NO ACCOUNT FOUND
// ========================
echo json_encode([
    "status" => "error",
    "message" => "No student, faculty, or parent account found with this email"
]);
exit;
?>