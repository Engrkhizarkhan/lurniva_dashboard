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

// Get school's subscription and student count
$stmt = $conn->prepare("SELECT subscription_start, subscription_end, num_students FROM schools WHERE id = ?");
$stmt->bind_param("i", $schoolId);
$stmt->execute();
$school = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$school) {
    echo json_encode(["success" => false, "message" => "School not found"]);
    exit;
}

$currentDate = date("Y-m-d");
if ($school['subscription_end'] < $currentDate) {
    echo json_encode(["success" => false, "message" => "School subscription expired"]);
    exit;
}

// Get student and linked parent info
$stmt = $conn->prepare("SELECT id, parent_cnic, status FROM students WHERE id = ? AND school_id = ?");
$stmt->bind_param("ii", $studentId, $schoolId);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    echo json_encode(["success" => false, "message" => "Student not found"]);
    exit;
}

$numChange = 0;

if ($student['status'] !== 'Approved' && $status === 'Approved') {
    if ($school['num_students'] <= 0) {
        echo json_encode(["success" => false, "message" => "Student quota exceeded. Cannot approve student."]);
        exit;
    }
    $numChange = -1;
} elseif ($student['status'] === 'Approved' && ($status === 'Pending' || $status === 'Inactive')) {
    $numChange = 1;
}

// Update student
$stmt = $conn->prepare("UPDATE students
    SET status = ?, subscription_start = ?, subscription_end = ?
    WHERE id = ? AND school_id = ? ");
$stmt->bind_param("sssii", $status, $school['subscription_start'], $school['subscription_end'], $studentId, $schoolId);
$studentUpdated = $stmt->execute();
$stmt->close();

// Update parent's record if student was updated successfully
if ($studentUpdated && !empty($student['parent_cnic'])) {
    $stmt2 = $conn->prepare(" UPDATE parents 
        SET status = ?, subscription_start = ?, subscription_end = ?
        WHERE parent_cnic = ? ");
    $stmt2->bind_param("ssss", $status, $school['subscription_start'], $school['subscription_end'], $student['parent_cnic']);
    $stmt2->execute();
    $stmt2->close();
}

// Update school's remaining student quota
if ($studentUpdated && $numChange !== 0) {
    $stmt3 = $conn->prepare("UPDATE schools SET num_students = num_students + ? WHERE id = ?");
    $stmt3->bind_param("ii", $numChange, $schoolId);
    $stmt3->execute();
    $stmt3->close();
}

if ($studentUpdated) {
    echo json_encode(["success" => true, "message" => "Student and parent updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Update failed"]);
}

$conn->close();
?>