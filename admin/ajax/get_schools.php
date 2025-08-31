<?php
header('Content-Type: application/json');
require_once '../sass/db_config.php'; // Update this path if needed

try {
    $sql = "SELECT id, school_name, registration_number FROM schools ORDER BY school_name ASC";
    $result = $conn->query($sql);

    $schools = [];
    while ($row = $result->fetch_assoc()) {
        $schools[] = $row;
    }

    echo json_encode([
        'status' => 'success',
        'data' => $schools
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
// {
//   "status": "success",
//   "data": [
//     {
//       "id": 1,
//       "school_name": "Lurniva Public School",
//       "registration_number": "REG12345"
//     },
//     {
//       "id": 2,
//       "school_name": "Bright Future Academy",
//       "registration_number": "REG67890"
//     }
//   ]
// }
?>