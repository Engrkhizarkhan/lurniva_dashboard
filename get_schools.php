<?php
require_once 'admin/sass/db_config.php';

header('Content-Type: application/json');

$sql = "SELECT id, school_name, registration_number FROM schools ORDER BY school_name ASC";
$result = $conn->query($sql);

$schools = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schools[] = $row;
    }
}

echo json_encode(['status' => 'success', 'data' => $schools]);
?>