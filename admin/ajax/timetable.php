<?php
session_start();
require_once '../sass/db_config.php';

$user_id = $_SESSION['admin_id'];
$school_id = $_SESSION['admin_id'];

$assembly_time = $_POST['assembly_time'];
$leave_time = $_POST['leave_time'];
$is_finalized = isset($_POST['is_finalized']) && $_POST['is_finalized'] ? 1 : 0;

$half_day_config = json_decode($_POST['half_day_config'], true);
$classes = json_decode($_POST['classes'], true);

$created_at = date('Y-m-d H:i:s');

// Check if school_timing already exists
$check = $conn->prepare("SELECT id FROM school_timings WHERE school_id = ?");
$check->bind_param("i", $school_id);
$check->execute();
$result = $check->get_result();
$timing_table_id = null;

if ($result->num_rows > 0) {
    // 🔁 Update existing record
    $existing = $result->fetch_assoc();
    $timing_table_id = $existing['id'];

    $stmt = $conn->prepare("UPDATE `school_timings` 
        SET `assembly_time` = ?, `leave_time` = ?, `created_at` = ?, `is_finalized` = ?, `created_by` = ?
        WHERE `id` = ?");
    $stmt->bind_param("sssiii", $assembly_time, $leave_time, $created_at, $is_finalized, $user_id, $timing_table_id);

    if ($stmt->execute()) {
        echo "🔁 General timetable updated.<br>";
    } else {
        echo "❌ Update failed: " . $stmt->error . "<br>";
        exit;
    }
} else {
    // ➕ Insert new record
    $stmt = $conn->prepare("INSERT INTO `school_timings` 
        (`school_id`, `assembly_time`, `leave_time`, `created_at`, `is_finalized`, `created_by`) 
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssii", $school_id, $assembly_time, $leave_time, $created_at, $is_finalized, $user_id);

    if ($stmt->execute()) {
        echo "✅ General timetable inserted.<br>";
        $timing_table_id = $stmt->insert_id;
    } else {
        echo "❌ Insert failed: " . $stmt->error . "<br>";
        exit;
    }
}
$stmt->close();


// Proceed to class insertions
if (!empty($classes)) {
    $stmt_cls = $conn->prepare("INSERT INTO `class_timetable_meta` 
        (`school_id`, `timing_table_id`, `class_name`, `section`, `total_periods`, `created_at`, `is_finalized`, `created_by`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt_hd = $conn->prepare("INSERT INTO `class_timetable_weekdays` 
        (`school_id`, `weekday`, `assembly_time`, `leave_time`, `total_periods`, `is_half_day`, `created_at`)
        VALUES (?, ?, ?, ?, ?, ?, ?)");

    $stmt_period = $conn->prepare("INSERT INTO `class_timetable_details` 
        (`timing_meta_id`, `period_number`, `period_name`, `start_time`, `end_time`, `created_at`, `teacher_id`, `is_break`, `period_type`, `created_by`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($classes as $class) {
        $class_name = $class['class_name'];
        $section = $class['section'];
        $class_periods = $class['total_periods'];

        $stmt_cls->bind_param(
            "iissisii",
            $school_id,
            $timing_table_id,
            $class_name,
            $section,
            $class_periods,
            $created_at,
            $is_finalized,
            $user_id
        );

        if ($stmt_cls->execute()) {
            $class_meta_id = $stmt_cls->insert_id;
            echo "✅ Class inserted: {$class_name} - {$section}<br>";

            // Insert Half-Day config
            if (!empty($half_day_config)) {
                foreach ($half_day_config as $day => $info) {
                    $weekday = $day;
                    $hd_assembly = $info['assembly_time'];
                    $hd_leave = $info['leave_time'];
                    $total_periods = $info['total_periods'];
                    $is_half_day = 1;

                    $stmt_hd->bind_param(
                        "issssis",
                        $school_id,
                        $weekday,
                        $hd_assembly,
                        $hd_leave,
                        $total_periods,
                        $is_half_day,
                        $created_at
                    );

                    if ($stmt_hd->execute()) {
                        echo "✅ Half-day config inserted for {$weekday} (Class ID {$class_meta_id}).<br>";
                    } else {
                        echo "❌ Half-day insert failed: " . $stmt_hd->error . "<br>";
                    }
                }
            }

            // Insert Periods for this class
            if (!empty($class['periods'])) {
                $period_num = 1;
                foreach ($class['periods'] as $p) {
                    $period_name = $p['period_name'];
                    $start_time = $p['start_time'];
                    $end_time = $p['end_time'];
                    $teacher_id = $p['teacher_id'];
                    $is_break = $p['is_break'] ? 1 : 0;
                    $period_type = $p['period_type'];

                    $stmt_period->bind_param(
                        "iisssiiisi",
                        $class_meta_id,
                        $period_num,
                        $period_name,
                        $start_time,
                        $end_time,
                        $created_at,
                        $teacher_id,
                        $is_break,
                        $period_type,
                        $user_id
                    );

                    if ($stmt_period->execute()) {
                        echo "✅ Period inserted: {$period_name} (Class ID {$class_meta_id})<br>";
                    } else {
                        echo "❌ Period insert failed: " . $stmt_period->error . "<br>";
                    }

                    $period_num++;
                }
            } else {
                echo "ℹ️ No periods for this class.<br>";
            }

        } else {
            echo "❌ Class insert failed: " . $stmt_cls->error . "<br>";
        }
    }

    $stmt_cls->close();
    $stmt_hd->close();
    $stmt_period->close();
} else {
    echo "ℹ️ No class blocks to insert.<br>";
}

$conn->close();
?>