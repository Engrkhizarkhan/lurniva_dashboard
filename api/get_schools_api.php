<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");  // allow Flutter app
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../admin/sass/db_config.php';

$response = ["status" => "error", "data" => []];

$sql = "SELECT id, school_name, registration_number FROM schools ORDER BY school_name ASC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $schools = [];
    while ($row = $result->fetch_assoc()) {
        $schools[] = [
            "id" => $row["id"],
            "school_name" => $row["school_name"],
            "registration_number" => $row["registration_number"]
        ];
    }
    $response = [
        "status" => "success",
        "count"  => count($schools),
        "data"   => $schools
    ];
} else {
    $response = [
        "status" => "success",
        "count"  => 0,
        "data"   => []
    ];
}

echo json_encode($response);
$conn->close();
?>