<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');

header('Content-Type: application/json');
session_start();
require_once '../sass/db_config.php';

$user_id = $_SESSION['admin_id'];
$school_id = $_SESSION['admin_id']; // or $_SESSION['school_id'] if separate

$output = [];

$sql_classes = "
    SELECT id, class_name, section 
    FROM class_timetable_meta 
    WHERE school_id = $school_id
";
$res_classes = $conn->query($sql_classes);

if ($res_classes && $res_classes->num_rows > 0) {
    while ($class = $res_classes->fetch_assoc()) {
        $class_id = (int)$class['id'];

        // Get max possible periods for this class
        $sql_max = "SELECT MAX(period_number) AS max_p FROM class_timetable_details WHERE timing_meta_id = $class_id";
        $max_res = $conn->query($sql_max);
        $max_row = $max_res ? $max_res->fetch_assoc() : null;
        $max_p = (int)($max_row['max_p'] ?? 0);

        $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        $day_data = [];

        foreach ($days as $day) {
            // Get half-day info
            $sql_day = "
                SELECT total_periods, is_half_day 
                FROM class_timetable_weekdays 
                WHERE school_id = $school_id 
                AND weekday = '$day'
            ";
            $day_res = $conn->query($sql_day);
            $day_row = $day_res ? $day_res->fetch_assoc() : null;

            // Default total_periods to max_p (or at least 1)
            $total_periods = (int)($max_p ?: 1);
            if (!empty($day_row) && !empty($day_row['is_half_day']) && !empty($day_row['total_periods'])) {
                $total_periods = (int)$day_row['total_periods'];
            }

            // Get all periods for this class safely
            $sql_periods = "
                SELECT d.period_number, d.period_name, d.start_time, d.end_time, 
                       d.period_type, d.teacher_id, t.full_name AS teacher_name
                FROM class_timetable_details d
                LEFT JOIN faculty t ON d.teacher_id = t.id
                WHERE d.timing_meta_id = $class_id 
                  AND d.period_number <= $total_periods
                ORDER BY d.period_number ASC
            ";

            // Log the query if debugging
            // error_log('DEBUG SQL: ' . $sql_periods);

            $res_p = $conn->query($sql_periods);
            $periods = [];
            if ($res_p) {
                while ($row = $res_p->fetch_assoc()) {
                    $periods[$row['period_number']] = $row;
                }
            }

            $day_data[] = [
                'name' => $day,
                'periods' => $periods,
                'is_half_day' => (int)($day_row['is_half_day'] ?? 0)
            ];
        }

        $output[] = [
            'id' => $class['id'],
            'class_name' => $class['class_name'],
            'section' => $class['section'],
            'max_periods' => $max_p,
            'days' => $day_data
        ];
    }
}

echo json_encode($output, JSON_PRETTY_PRINT);
$conn->close();