<?php
header("Content-Type: application/json");
require_once '../../admin/sass/db_config.php'; // ✅ unified DB path


try {
    // Prepare the SQL query
    $sql = "SELECT 
                `id`, `school_id`, `parent_name`, `parent_cnic`, `full_name`, `username`, 
                `gender`, `dob`, `cnic_formb`, `class_grade`, `section`, `roll_number`, 
                `address`, `email`, `phone`, `profile_photo`, `password`, 
                `verification_code`, `is_verified`, `code_expires_at`, `verification_attempts`, 
                `status`, `created_at`, `subscription_start`, `subscription_end`, `route_id`
            FROM `students`";

    $result = $conn->query($sql);

    if (!$result) {
        echo json_encode([
            "status" => "error",
            "message" => "Query failed: " . $conn->error
        ]);
        exit;
    }

    $students = [];
    while ($row = $result->fetch_assoc()) {
        // Optional: remove sensitive data like password if not needed
        unset($row['password'], $row['verification_code']);
        $students[] = $row;
    }

    echo json_encode([
        "status" => "success",
        "count" => count($students),
        "data" => $students
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}

$conn->close();
?>