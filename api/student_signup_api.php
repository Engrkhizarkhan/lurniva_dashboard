<?php
require_once '../admin/sass/db_config.php';
require_once '../mail_library.php'; // ✅ include PHPMailer helper

// --------------------
// ✅ CORS CONFIGURATION
// --------------------
$allowedOrigins = [
    'http://localhost:8080', 'http://localhost:8081', 'http://localhost:3000',
    'http://localhost:5173', 'http://localhost:60706', 'https://dashboard.lurniva.com',
    'https://www.dashboard.lurniva.com'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --------------------
// ✅ READ INPUT
// --------------------
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

$form_type = strtolower(trim($data['form_type'] ?? ''));

// --------------------
// ✅ COMMON FIELDS
// --------------------
$verification_code = rand(100000, 999999);
$is_verified = 0;
$code_expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));
$verification_attempts = 0;
$status = "Pending";

// --------------------
// ✅ HELPER: CHECK GLOBAL UNIQUENESS
// --------------------
function checkUnique($conn, $email, $cnic, $tables) {
    foreach ($tables as $table) {
        $query = "";
        if ($table === 'students') $query = "SELECT id FROM students WHERE email=? OR cnic_formb=?";
        elseif ($table === 'parents') $query = "SELECT id FROM parents WHERE email=? OR parent_cnic=?";
        elseif ($table === 'faculty') $query = "SELECT id FROM faculty WHERE email=? OR cnic=?";
        elseif ($table === 'schools') $query = "SELECT id FROM schools WHERE school_email=?";

        if ($query) {
            $stmt = $conn->prepare($query);
            if ($table === 'schools') $stmt->bind_param("s", $email);
            else $stmt->bind_param("ss", $email, $cnic);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                return $table;
            }
            $stmt->close();
        }
    }
    return false;
}

// ===================================================================
// ✅ STUDENT SIGNUP
// ===================================================================
if ($form_type === 'student') {
    $required = [
        "school_id", "parent_name", "parent_cnic", "full_name", "gender", "dob",
        "cnic_formb", "class_grade", "section", "roll_number", "address",
        "email", "phone", "password"
    ];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(["status" => "error", "message" => "Missing field: $field"]);
            exit;
        }
    }

    // ✅ GLOBAL CHECK
    $existing = checkUnique($conn, $data['email'], $data['cnic_formb'], ['students','parents','faculty','schools']);
    if ($existing) {
        echo json_encode(["status"=>"error","message"=>"Email or CNIC already exists in $existing table"]);
        exit;
    }

    $school_id   = intval($data['school_id']);
    $parent_name = $data['parent_name'];
    $parent_cnic = $data['parent_cnic'];
    $full_name   = $data['full_name'];
    $gender      = $data['gender'];
    $dob         = $data['dob'];
    $cnic_formb  = $data['cnic_formb'];
    $class_grade = $data['class_grade'];
    $section     = $data['section'];
    $roll_number = $data['roll_number'];
    $address     = $data['address'];
    $email       = $data['email'];
    $phone       = $data['phone'];
    $password    = password_hash($data['password'], PASSWORD_DEFAULT);

    // Optional profile photo (Base64)
    $profile_name = null;
    if (!empty($data['profile_photo'])) {
        $profile_name = time() . "_student.png";
        $path = "../student/uploads/profile/";
        if (!is_dir($path)) mkdir($path, 0777, true);
        file_put_contents($path . $profile_name, base64_decode($data['profile_photo']));
    }

    $stmt = $conn->prepare("INSERT INTO students 
        (school_id, parent_name, parent_cnic, full_name, gender, dob, cnic_formb,
         class_grade, section, roll_number, address, email, phone, profile_photo,
         password, status, verification_code, is_verified, code_expires_at, verification_attempts)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "isssssssssssssssissi",
        $school_id, $parent_name, $parent_cnic, $full_name, $gender, $dob, $cnic_formb,
        $class_grade, $section, $roll_number, $address, $email, $phone, $profile_name,
        $password, $status, $verification_code, $is_verified, $code_expires_at, $verification_attempts
    );

    if ($stmt->execute()) {
        $subject = "Student Account Verification - Lurniva";
        $body = "<p>Hello <b>$full_name</b>,</p><p>Your verification code is:</p>
                 <h2 style='color:#007bff;'>$verification_code</h2>
                 <p>This code expires in <b>5 minutes</b>.</p>
                 <br><p>Best regards,<br><b>Lurniva Support Team</b></p>";

        sendMail($email, $subject, $body);

        echo json_encode(["status"=>"success","message"=>"Student registered. Please verify email.","student_id"=>$conn->insert_id]);
    } else {
        echo json_encode(["status"=>"error","message"=>$stmt->error]);
    }
    $stmt->close();
}

// ===================================================================
// ✅ PARENT SIGNUP
// ===================================================================
elseif ($form_type === 'parent') {
    $required = ["full_name", "parent_cnic", "email", "phone", "password"];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            echo json_encode(["status" => "error", "message" => "Missing field: $field"]);
            exit;
        }
    }

    // ✅ GLOBAL CHECK
    $existing = checkUnique($conn, $data['email'], $data['parent_cnic'], ['students','parents','faculty','schools']);
    if ($existing) {
        echo json_encode(["status"=>"error","message"=>"Email or CNIC already exists in $existing table"]);
        exit;
    }

    $full_name   = $data['full_name'];
    $parent_cnic = $data['parent_cnic'];
    $email       = $data['email'];
    $phone       = $data['phone'];
    $password    = password_hash($data['password'], PASSWORD_DEFAULT);

    $profile_name = null;
    if (!empty($data['profile_photo'])) {
        $profile_name = time() . "_parent.png";
        $path = "../parent/uploads/profile/";
        if (!is_dir($path)) mkdir($path, 0777, true);
        file_put_contents($path . $profile_name, base64_decode($data['profile_photo']));
    }

    $stmt = $conn->prepare("INSERT INTO parents 
        (full_name, parent_cnic, email, phone, profile_photo,
         password, status, verification_code, is_verified, code_expires_at, verification_attempts)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param(
        "sssssssissi",
        $full_name, $parent_cnic, $email, $phone, $profile_name,
        $password, $status, $verification_code, $is_verified, $code_expires_at, $verification_attempts
    );

    if ($stmt->execute()) {
        $subject = "Parent Account Verification - Lurniva";
        $body = "<p>Hello <b>$full_name</b>,</p><p>Your verification code is:</p>
                 <h2 style='color:#007bff;'>$verification_code</h2>
                 <p>This code expires in <b>5 minutes</b>.</p>
                 <br><p>Best regards,<br><b>Lurniva Support Team</b></p>";

        sendMail($email, $subject, $body);

        echo json_encode(["status"=>"success","message"=>"Parent registered. Please verify email.","parent_id"=>$conn->insert_id]);
    } else {
        echo json_encode(["status"=>"error","message"=>$stmt->error]);
    }
    $stmt->close();
}

// ===================================================================
// ❌ INVALID TYPE
// ===================================================================
else {
    echo json_encode(["status" => "error", "message" => "Invalid form_type"]);
}

$conn->close();
?>