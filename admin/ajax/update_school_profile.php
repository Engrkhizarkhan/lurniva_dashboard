<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$school_id = $_SESSION['admin_id'];

// Collect and sanitize input (add more validation as needed)
$school_name = $_POST['school_name'] ?? '';
$school_type = $_POST['school_type'] ?? '';
$registration_number = $_POST['registration_number'] ?? '';
$affiliation_board = $_POST['affiliation_board'] ?? '';
$school_email = $_POST['school_email'] ?? '';
$school_phone = $_POST['school_phone'] ?? '';
$school_website = $_POST['school_website'] ?? '';
$admin_contact_person = $_POST['admin_contact_person'] ?? '';
$admin_email = $_POST['admin_email'] ?? '';
$admin_phone = $_POST['admin_phone'] ?? '';
$country = $_POST['country'] ?? '';
$state = $_POST['state'] ?? '';
$city = $_POST['city'] ?? '';
$address = $_POST['address'] ?? '';

if (empty($school_name) || empty($school_email) || empty($admin_email)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in required fields']);
    exit;
}

$stmt = $conn->prepare("UPDATE schools SET school_name=?, school_type=?, registration_number=?, affiliation_board=?, school_email=?, school_phone=?, school_website=?, admin_contact_person=?, admin_email=?, admin_phone=?, country=?, state=?, city=?, address=? WHERE id=?");
$stmt->bind_param(
    "ssssssssssssssi",
    $school_name, $school_type, $registration_number, $affiliation_board, $school_email,
    $school_phone, $school_website, $admin_contact_person, $admin_email, $admin_phone,
    $country, $state, $city, $address, $school_id
);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Profile updated']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Database update failed']);
}