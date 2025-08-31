<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../admin/sass/db_config.php';

/**
 * Insert default settings for a student
 */
function createDefaultSettings($conn, $person, $person_id) {
    $stmt = $conn->prepare("INSERT INTO school_settings 
        (person, person_id, layout, sidebar_color, color_theme, mini_sidebar, sticky_header) 
        VALUES (?, ?, '1', '1', 'white', '0', '0')");
    $stmt->bind_param("si", $person, $person_id);
    $stmt->execute();
    $stmt->close();
}

// ✅ Accept only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// ✅ Get JSON body
$data = json_decode(file_get_contents("php://input"), true);

$school_id     = intval($data['school_id'] ?? 0);
$parent_name   = trim($data['parent_name'] ?? '');
$full_name     = trim($data['full_name'] ?? '');
$gender        = trim($data['gender'] ?? '');
$dob           = trim($data['dob'] ?? '');
$cnic_formb    = trim($data['cnic_formb'] ?? '');
$class_grade   = trim($data['class_grade'] ?? '');
$section       = trim($data['section'] ?? '');
$roll_number   = trim($data['roll_number'] ?? '');
$address       = trim($data['address'] ?? '');
$email         = trim($data['email'] ?? '');
$parent_email  = trim($data['parent_email'] ?? '');
$phone         = trim($data['phone'] ?? '');
$password      = $data['password'] ?? '';
$profile_base64= $data['profile_photo'] ?? null; // optional, base64

// ✅ Validation
if (empty($full_name) || empty($email) || empty($password) || empty($school_id)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

// ✅ Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// ✅ Generate verification code
$verification_code = rand(100000, 999999);
$is_verified = 0;
$code_expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));
$verification_attempts = 0;
$status = "pending";

// ✅ Handle profile photo (if base64 provided)
$profile_name = null;
if ($profile_base64) {
    $profile_name = time() . "_profile.png";
    $adminPath = "admin/uploads/profile/";
    if (!is_dir($adminPath)) mkdir($adminPath, 0777, true);

    file_put_contents($adminPath . $profile_name, base64_decode($profile_base64));

    // also copy to student folder
    $schoolPath = "student/uploads/profile/";
    if (!is_dir($schoolPath)) mkdir($schoolPath, 0777, true);
    copy($adminPath . $profile_name, $schoolPath . $profile_name);
}

// ✅ Insert student
$stmt = $conn->prepare("INSERT INTO students 
    (school_id, parent_name, full_name, gender, dob, cnic_formb,
     class_grade, section, roll_number, address,
     email, parent_email, phone, profile_photo,
     password, status, verification_code, is_verified, code_expires_at, verification_attempts)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param(
    "issssssssssssssssisi",
    $school_id,
    $parent_name,
    $full_name,
    $gender,
    $dob,
    $cnic_formb,
    $class_grade,
    $section,
    $roll_number,
    $address,
    $email,
    $parent_email,
    $phone,
    $profile_name,
    $hashed_password,
    $status,
    $verification_code,
    $is_verified,
    $code_expires_at,
    $verification_attempts
);

if ($stmt->execute()) {
    $student_id = $conn->insert_id;

    // ✅ Create default settings
    createDefaultSettings($conn, "student", $student_id);

    // ✅ Send verification email
    $to = $email;
    $subject = "Student Account Verification";
    $message = "Hello $full_name,\n\nYour verification code is: $verification_code\n\nThis code expires in 5 minutes.";
    $headers = "From: shayans1215225@gmail.com";

    @mail($to, $subject, $message, $headers);

    echo json_encode([
        "status" => "success",
        "message" => "Student registered successfully. Verification code sent.",
        "student" => [
            "id" => $student_id,
            "full_name" => $full_name,
            "email" => $email,
            "school_id" => $school_id,
            "photo" => $profile_name ? "student/uploads/profile/$profile_name" : null
        ]
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Execute failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>