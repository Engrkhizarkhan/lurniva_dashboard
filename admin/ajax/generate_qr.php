<?php
require_once '../sass/db_config.php';

$type = $_POST['type'] ?? '';
$id   = $_POST['id'] ?? '';

if (!$type || !$id) {
    echo "<div class='alert alert-warning'>Invalid request</div>";
    exit;
}

$data = [];

if ($type == 'class') {
    $stmt = $conn->prepare("SELECT id, class_name, section FROM class_timetable_meta WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if (!$data) {
        echo "<div class='alert alert-danger'>Class not found</div>";
        exit;
    }

    // JSON structure for Class
    $jsonData = [
        'type'       => 'class',
        'id'         => $data['id'],
        'class_name' => $data['class_name'],
        'section'    => $data['section']
    ];

} elseif ($type == 'student') {
    $stmt = $conn->prepare("SELECT id, full_name, roll_number, class_grade, section FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $data = $stmt->get_result()->fetch_assoc();

    if (!$data) {
        echo "<div class='alert alert-danger'>Student not found</div>";
        exit;
    }

    // JSON structure for Student
    $jsonData = [
        'type'        => 'student',
        'id'          => $data['id'],
        'full_name'   => $data['full_name'],
        'roll_number' => $data['roll_number'],
        'class'       => $data['class_grade'],
        'section'     => $data['section']
    ];

} else {
    echo "<div class='alert alert-warning'>Invalid type</div>";
    exit;
}

// Encode as JSON
$jsonString = json_encode($jsonData, JSON_UNESCAPED_UNICODE);

// Generate QR using qrserver API
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($jsonString) . "&size=200x200";

// Show QR
echo "<div class='text-center'>
        <img src='{$qrUrl}' alt='QR Code' class='img-fluid mb-2'><br>
        <pre>" . htmlspecialchars($jsonString) . "</pre>
      </div>";