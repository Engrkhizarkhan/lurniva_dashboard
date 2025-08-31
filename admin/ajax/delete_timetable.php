<?php
require_once '../sass/db_config.php';
session_start();

if (!isset($_POST['timing_table_id'])) {
    http_response_code(400);
    echo "Timetable ID is missing.";
    exit;
}

$timing_table_id = intval($_POST['timing_table_id']);

// Optionally delete dependent records first
$conn->query("DELETE FROM class_timetable_details WHERE timing_meta_id IN (SELECT id FROM class_timetable_meta WHERE timing_table_id = $timing_table_id)");
$conn->query("DELETE FROM class_timetable_weekdays WHERE timetable_id IN (SELECT id FROM class_timetable_meta WHERE timing_table_id = $timing_table_id)");
$conn->query("DELETE FROM class_timetable_meta WHERE timing_table_id = $timing_table_id");
$conn->query("DELETE FROM school_timings WHERE id = $timing_table_id");

echo "✅ Timetable deleted successfully.";