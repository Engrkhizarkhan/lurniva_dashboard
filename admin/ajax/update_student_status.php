<?php
session_start();
header('Content-Type: application/json');
require '../sass/db_config.php';

// Ensure logged in school admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$studentId = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$studentId || !$status) {
    echo json_encode(["success" => false, "message" => "Missing data"]);
    exit;
}

$schoolId = $_SESSION['admin_id'];

// Get school's subscription dates and num_students
$stmt = $conn->prepare("SELECT subscription_start, subscription_end, num_students FROM schools WHERE id = ?");
$stmt->bind_param("i", $schoolId);
$stmt->execute();
$school = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$school) {
    echo json_encode(["success" => false, "message" => "School not found"]);
    exit;
}

// Block if subscription expired
$currentDate = date("Y-m-d");
if ($school['subscription_end'] < $currentDate) {
    echo json_encode(["success" => false, "message" => "School subscription expired"]);
    exit;
}

// Get current student status
$stmt = $conn->prepare("SELECT status FROM students WHERE id = ? AND school_id = ?");
$stmt->bind_param("ii", $studentId, $schoolId);
$stmt->execute();
$currentStudent = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$currentStudent) {
    echo json_encode(["success" => false, "message" => "Student not found"]);
    exit;
}

// Calculate num_students change
$numChange = 0;

if ($currentStudent['status'] !== 'Approved' && $status === 'Approved') {
    // Pending/Inactive -> Approved
    if ($school['num_students'] <= 0) {
        echo json_encode(["success" => false, "message" => "Student quota exceeded. Cannot approve student."]);
        exit;
    }
    $numChange = -1;
} elseif ($currentStudent['status'] === 'Approved' && ($status === 'Pending' || $status === 'Inactive')) {
    // Approved -> Pending/Inactive
    $numChange = 1;
}

// Update student
$stmt = $conn->prepare("
    UPDATE students
    SET status = ?, subscription_start = ?, subscription_end = ?
    WHERE id = ? AND school_id = ?
");
$stmt->bind_param("sssii", $status, $school['subscription_start'], $school['subscription_end'], $studentId, $schoolId);

if ($stmt->execute()) {
    // Update school's num_students if needed
    if ($numChange !== 0) {
        $stmt2 = $conn->prepare("UPDATE schools SET num_students = num_students + ? WHERE id = ?");
        $stmt2->bind_param("ii", $numChange, $schoolId);
        $stmt2->execute();
        
        $stmt2->close();
    }

    echo json_encode(["success" => true, "message" => "Student updated"]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed"]);
}

$stmt->close();
$conn->close();
?>