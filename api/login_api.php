<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Allow mobile app requests
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../admin/sass/db_config.php';

function sendMail($to, $subject, $message) {
    $from = "shayans1215225@gmail.com"; 
    $headers  = "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    return mail($to, $subject, $message, $headers);
}

// --- Only handle POST requests ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Email and password are required"]);
    exit;
}

// =================================
//   FACULTY LOGIN
// =================================
$stmt = $conn->prepare("
    SELECT id, campus_id, full_name, email, password, photo, is_verified, verification_code
    FROM faculty 
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $faculty = $result->fetch_assoc();

    if (!password_verify($password, $faculty['password'])) {
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
        exit;
    }

    if ($faculty['is_verified'] == 1) {
        echo json_encode([
            "status" => "success",
            "type" => "faculty",
            "data" => [
                "id" => $faculty['id'],
                "full_name" => $faculty['full_name'],
                "email" => $faculty['email'],
                "campus_id" => $faculty['campus_id'],
                "photo" => $faculty['photo']
            ]
        ]);
        exit;
    } else {
        // resend verification code
        $verification_code = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        $conn->query("UPDATE faculty SET verification_code='$verification_code', code_expires_at='$expiry' WHERE id=".$faculty['id']);

        $subject = "Faculty Account Verification";
        $msg = "Hello {$faculty['full_name']},\n\nYour verification code is: $verification_code\n\nThis code expires in 5 minutes.";
        sendMail($email, $subject, $msg);

        echo json_encode([
            "status" => "verify_required",
            "type" => "faculty",
            "message" => "Verification required. Code sent to email."
        ]);
        exit;
    }
}

// =================================
//   STUDENT LOGIN
// =================================
$stmt = $conn->prepare("
    SELECT id, school_id, full_name, email, password, profile_photo, is_verified, verification_code
    FROM students
    WHERE email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $student = $result->fetch_assoc();

    if (!password_verify($password, $student['password'])) {
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
        exit;
    }

    if ($student['is_verified'] == 1) {
        echo json_encode([
            "status" => "success",
            "type" => "student",
            "data" => [
                "id" => $student['id'],
                "full_name" => $student['full_name'],
                "email" => $student['email'],
                "school_id" => $student['school_id'],
                "photo" => $student['profile_photo']
            ]
        ]);
        exit;
    } else {
        // resend verification code
        $verification_code = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));
        $conn->query("UPDATE students SET verification_code='$verification_code', code_expires_at='$expiry' WHERE id=".$student['id']);

        $subject = "Student Account Verification";
        $msg = "Hello {$student['full_name']},\n\nYour verification code is: $verification_code\n\nThis code expires in 5 minutes.";
        sendMail($email, $subject, $msg);

        echo json_encode([
            "status" => "verify_required",
            "type" => "student",
            "message" => "Verification required. Code sent to email."
        ]);
        exit;
    }
}

// =================================
//   NO ACCOUNT FOUND
// =================================
echo json_encode([
    "status" => "error",
    "message" => "No faculty or student account found with this email"
]);
exit;

?>