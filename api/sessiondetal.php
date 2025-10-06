<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../admin/sass/db_config.php'; // adjust path if needed

try {
    // ✅ Fetch all schools
    $query = "
        SELECT 
            id, 
            school_name, 
            school_type, 
            registration_number, 
            affiliation_board, 
            school_email, 
            school_phone, 
            school_website, 
            country, 
            state, 
            city, 
            address, 
            logo, 
            admin_contact_person, 
            username, 
            admin_email, 
            admin_phone, 
            password, 
            verification_code, 
            is_verified, 
            status, 
            code_expires_at, 
            verification_attempts, 
            created_at, 
            subscription_start, 
            subscription_end, 
            num_students
        FROM schools
        ORDER BY id DESC
    ";

    $result = $conn->query($query);

    if (!$result) {
        echo json_encode([
            "status" => "error",
            "message" => "Database query failed: " . $conn->error
        ]);
        exit;
    }

    $schools = [];
    while ($row = $result->fetch_assoc()) {
        $schools[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "count"  => count($schools),
        "data"   => $schools
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Exception: " . $e->getMessage()
    ]);
}

$conn->close();
?>