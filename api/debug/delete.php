<?php
require_once '../../admin/sass/db_config.php';

// ✅ Get the ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo "<script>alert('Invalid faculty ID.'); window.history.back();</script>";
    exit;
}

// ✅ Prepare delete query for faculty table
$stmt = $conn->prepare("DELETE FROM faculty WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo "<script>alert('Faculty record deleted successfully.'); window.location.href=document.referrer;</script>";
    } else {
        echo "<script>alert('Faculty record not found.'); window.location.href=document.referrer;</script>";
    }
} else {
    echo "<script>alert('Error deleting record: " . addslashes($stmt->error) . "'); window.location.href=document.referrer;</script>";
}

$stmt->close();
$conn->close();
?>