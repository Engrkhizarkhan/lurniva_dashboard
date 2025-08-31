<?php
session_start();
require_once '../sass/db_config.php';

$school_id = $_SESSION['admin_id'] ?? 0;

$sql = "SELECT id, full_name FROM faculty WHERE campus_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo '<option selected>Select Teacher</option>';
    while ($row = $result->fetch_assoc()) {
        echo '<option value="' . $row['id'] . '">' . $row['full_name'] . '</option>';
    }
} else {
    echo '<option>No teachers found</option>';
}
?>