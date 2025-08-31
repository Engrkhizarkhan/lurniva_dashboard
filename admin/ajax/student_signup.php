<?php
header('Content-Type: application/json');

// DB connection
require_once '../sass/db_config.php'; // Update this path if needed

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No data received"]);
    exit;
}

// Validate required fields
$required = ['school_id', 'parent_name', 'full_name', 'gender', 'dob', 'cnic_formb', 'class_grade', 'section', 'email', 'phone', 'password'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(["status" => "error", "message" => "$field is required"]);
        exit;
    }
}

// Sanitize inputs
$school_id = $conn->real_escape_string($data['school_id']);
$parent_name = $conn->real_escape_string($data['parent_name']);
$full_name = $conn->real_escape_string($data['full_name']);
$gender = $conn->real_escape_string($data['gender']);
$dob = $conn->real_escape_string($data['dob']);
$cnic_formb = $conn->real_escape_string($data['cnic_formb']);
$class_grade = $conn->real_escape_string($data['class_grade']);
$section = $conn->real_escape_string($data['section']);
$roll_number = !empty($data['roll_number']) ? $conn->real_escape_string($data['roll_number']) : '';
$address = !empty($data['address']) ? $conn->real_escape_string($data['address']) : '';
$email = $conn->real_escape_string($data['email']);
$phone = $conn->real_escape_string($data['phone']);
$parent_email = $conn->real_escape_string($data['parent_email']);
$password = password_hash($data['password'], PASSWORD_DEFAULT);

// Check if school exists
$school_check = $conn->query("SELECT id FROM schools WHERE id = '$school_id'");
if ($school_check->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid school ID"]);
    exit;
}

// Check if student email OR parent email exists anywhere
$emailExists = $conn->query("SELECT id FROM students WHERE email = '$email' OR parent_email = '$email'")
    ->num_rows > 0 ||
    $conn->query("SELECT id FROM students WHERE email = '$parent_email' OR parent_email = '$parent_email'")
    ->num_rows > 0 ||
    $conn->query("SELECT id FROM faculty WHERE email = '$email' OR email = '$parent_email'")
    ->num_rows > 0 ||
    $conn->query("SELECT id FROM schools WHERE admin_email = '$email' OR admin_email = '$parent_email'")
    ->num_rows > 0;

if ($emailExists) {
    echo json_encode(["status" => "error", "message" => "Student or Parent Email already exists"]);
    exit;
}

// Handle profile photo base64 (optional)
$profile_photo = '';
if (!empty($data['profile_photo_base64'])) {
    $imgData = base64_decode($data['profile_photo_base64']);
    $folder = '../uploads/profile/';
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
    $fileName = $folder . uniqid('student_') . '.jpg';
    if (file_put_contents($fileName, $imgData)) {
        $profile_photo = $conn->real_escape_string($fileName);
    }
}

// Insert student
$sql = "INSERT INTO students 
    (school_id, parent_name, full_name, gender, dob, cnic_formb, class_grade, section, roll_number, address, email, phone, profile_photo, parent_email,password) 
    VALUES 
    ('$school_id', '$parent_name', '$full_name', '$gender', '$dob', '$cnic_formb', '$class_grade', '$section', '$roll_number', '$address', '$email', '$phone', '$profile_photo', '$parent_email','$password')";

if ($conn->query($sql)) {
    echo json_encode(["status" => "success", "message" => "Student registered successfully"]);
} else {
    echo json_encode(["status" => "error", "message" => "Registration failed: " . $conn->error]);
}

$conn->close();