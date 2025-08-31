<?php
session_start();
require '../sass/db_config.php';

header('Content-Type: application/json');

// Assuming school_id is stored in session after login
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$school_id = $_SESSION['admin_id'];

$stmt = $conn->prepare("SELECT id, school_name, school_type, registration_number, affiliation_board, school_email, school_phone, school_website, country, state, city, address, logo, admin_contact_person, admin_email, admin_phone FROM schools WHERE id = ?");
$stmt->bind_param('i', $school_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'School not found']);
    exit;
}

$data = $result->fetch_assoc();

echo json_encode(['status' => 'success', 'data' => $data]);