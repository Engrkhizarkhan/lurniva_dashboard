<?php
require_once '../sass/db_config.php'; // Your DB connection
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

function clean_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = [
        'school_name', 'school_type', 'registration_number', 'affiliation_board',
        'school_email', 'school_phone', 'country', 'state', 'city', 'address',
        'admin_contact_person', 'admin_email', 'admin_phone', 'password'
    ];

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            echo json_encode(['type' => 'danger', 'message' => "Field '$field' is required."]);
            exit;
        }
    }

    // Sanitize inputs
    $school_name = clean_input($_POST['school_name']);
    $school_type = clean_input($_POST['school_type']);
    $registration_number = clean_input($_POST['registration_number']);
    $affiliation_board = clean_input($_POST['affiliation_board']);
    $school_email = clean_input($_POST['school_email']);
    $school_phone = clean_input($_POST['school_phone']);
    $school_website = isset($_POST['school_website']) ? clean_input($_POST['school_website']) : '';
    $country = clean_input($_POST['country']);
    $state = clean_input($_POST['state']);
    $city = clean_input($_POST['city']);
    $address = clean_input($_POST['address']);
    $admin_contact_person = clean_input($_POST['admin_contact_person']);
    $admin_email = clean_input($_POST['admin_email']);
    $admin_phone = clean_input($_POST['admin_phone']);
    $password = password_hash(clean_input($_POST['password']), PASSWORD_DEFAULT);

    // Check duplicates
    $check = $conn->prepare("SELECT id FROM schools WHERE admin_email = ? OR school_email = ? OR registration_number = ?");
    $check->bind_param("sss", $admin_email, $school_email, $registration_number);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo json_encode(['type' => 'danger', 'message' => "Email or registration number already exists."]);
        exit;
    }

    // Upload logo
    $logo_name = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $target_dir = __DIR__ . '/../uploads/logos/';
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logo_name = uniqid('logo_', true) . '.' . $file_ext;
        move_uploaded_file($_FILES['logo']['tmp_name'], $target_dir . $logo_name);
    }

    // Insert school
    $stmt = $conn->prepare("INSERT INTO schools 
        (school_name, school_type, registration_number, affiliation_board, school_email, school_phone, school_website, country, state, city, address, logo, admin_contact_person, admin_email, admin_phone, password) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssssssssss", $school_name, $school_type, $registration_number, $affiliation_board, $school_email, $school_phone, $school_website, $country, $state, $city, $address, $logo_name, $admin_contact_person, $admin_email, $admin_phone, $password);

    if ($stmt->execute()) {
        echo json_encode(['type' => 'success', 'message' => 'School registered successfully!']);
    } else {
        echo json_encode(['type' => 'danger', 'message' => 'Failed to register school: ' . $stmt->error]);
    }
}