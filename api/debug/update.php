<?php
header('Content-Type: application/json');
require '../sass/db_config.php';

// Fetch all parents
$query = "
    SELECT 
        id, 
        full_name, 
        parent_cnic, 
        email, 
        phone, 
        profile_photo, 
        password, 
        status, 
        verification_code, 
        is_verified, 
        code_expires_at, 
        verification_attempts, 
        created_at, 
        subscription_start, 
        subscription_end
    FROM parents
";

$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $parents = [];
    while ($row = $result->fetch_assoc()) {
        $parents[] = $row;
    }
    echo json_encode(["success" => true, "data" => $parents], JSON_PRETTY_PRINT);
} else {
    echo json_encode(["success" => false, "message" => "No parents found"]);
}

$conn->close();
?>