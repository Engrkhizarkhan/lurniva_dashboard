<?php
header('Content-Type: application/json');
session_start(); // Important: start the session!
require_once '../sass/db_config.php';

$user_id = $_SESSION['admin_id'];
$school_id = $_SESSION['admin_id']; // Or use $_SESSION['school_id'] if that’s separate

$output = [];

$sql_classes = "
    SELECT id, class_name, section 
    FROM class_timetable_meta 
    WHERE school_id = $school_id
";
$res_classes = $conn->query($sql_classes);

if ($res_classes->num_rows > 0) {
    while ($class = $res_classes->fetch_assoc()) {
        $class_id = $class['id'];

        // Get max possible periods for this class
        $sql_max = "SELECT MAX(period_number) as max_p FROM class_timetable_details WHERE timing_meta_id = $class_id";
        $max_res = $conn->query($sql_max);
        $max_p = ($max_res->fetch_assoc())['max_p'];

        $days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        $day_data = [];

        foreach ($days as $day) {
            // Get half-day info
            $sql_day = "SELECT total_periods, is_half_day FROM class_timetable_weekdays WHERE school_id = $school_id AND weekday = '$day'";
            $day_res = $conn->query($sql_day);
            $total_periods = $max_p;
            if ($day_row = $day_res->fetch_assoc()) {
                if ($day_row['is_half_day']) {
                    $total_periods = $day_row['total_periods'];
                }
            }

            // Get all periods for this class
            $sql_periods = "
                SELECT d.period_number, d.period_name, d.start_time, d.end_time, d.period_type, d.teacher_id, t.full_name as teacher_name
                FROM class_timetable_details d
                LEFT JOIN faculty t ON d.teacher_id = t.id
                WHERE d.timing_meta_id = $class_id AND d.period_number <= $total_periods
                ORDER BY d.period_number ASC
            ";

            $res_p = $conn->query($sql_periods);
            $periods = [];
            while ($row = $res_p->fetch_assoc()) {
                $periods[$row['period_number']] = $row;
            }

            $day_data[] = [
                'name' => $day,
                'periods' => $periods,
                'is_half_day' => $day_row['is_half_day'] ?? 0
            ];
        }

        $output[] = [
            'class_name' => $class['class_name'],
            'section' => $class['section'],
            'max_periods' => $max_p,
            'days' => $day_data
        ];
    }
}

echo json_encode($output);
$conn->close();