<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

try {
    require '../sass/db_config.php';
    
    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests are allowed');
    }
    
    // Check if admin is logged in
    if (!isset($_SESSION['admin_id'])) {
        throw new Exception('Admin not logged in');
    }
    
    $user_id = $_SESSION['admin_id'];
    $school_id = $_SESSION['admin_id'];
    
    // Validate required POST data
    if (!isset($_POST['assembly_time']) || empty($_POST['assembly_time'])) {
        throw new Exception('Assembly time is required');
    }
    
    if (!isset($_POST['leave_time']) || empty($_POST['leave_time'])) {
        throw new Exception('Leave time is required');
    }
    
    $assembly_time = $_POST['assembly_time'];
    $leave_time = $_POST['leave_time'];
    $is_finalized = isset($_POST['is_finalized']) && $_POST['is_finalized'] ? 1 : 0;
    
    // Validate and decode JSON data
    $half_day_config = null;
    if (isset($_POST['half_day_config']) && !empty($_POST['half_day_config'])) {
        $half_day_config = json_decode($_POST['half_day_config'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid half_day_config JSON: ' . json_last_error_msg());
        }
    }
    
    $classes = null;
    if (isset($_POST['classes']) && !empty($_POST['classes'])) {
        $classes = json_decode($_POST['classes'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid classes JSON: ' . json_last_error_msg());
        }
    }
    
    $created_at = date('Y-m-d H:i:s');

    // Check if school_timing already exists
    $check = $conn->prepare("SELECT id FROM school_timings WHERE school_id = ?");
    if (!$check) {
        throw new Exception('Prepare statement failed: ' . $conn->error);
    }
    
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
        
        if (!$stmt) {
            throw new Exception('Update prepare statement failed: ' . $conn->error);
        }
        
        $stmt->bind_param("sssiii", $assembly_time, $leave_time, $created_at, $is_finalized, $user_id, $timing_table_id);
    
        if ($stmt->execute()) {
            echo "🔁 General timetable updated.<br>";
        } else {
            throw new Exception('Update failed: ' . $stmt->error);
        }
    } else {
        // ➕ Insert new record
        $stmt = $conn->prepare("INSERT INTO `school_timings` 
            (`school_id`, `assembly_time`, `leave_time`, `created_at`, `is_finalized`, `created_by`) 
            VALUES (?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception('Insert prepare statement failed: ' . $conn->error);
        }
        
        $stmt->bind_param("isssii", $school_id, $assembly_time, $leave_time, $created_at, $is_finalized, $user_id);
    
        if ($stmt->execute()) {
            echo "✅ General timetable inserted.<br>";
            $timing_table_id = $stmt->insert_id;
        } else {
            throw new Exception('Insert failed: ' . $stmt->error);
        }
    }
    $stmt->close();

    // Proceed to class insertions
    if (!empty($classes)) {
        $stmt_cls = $conn->prepare("INSERT INTO `class_timetable_meta` 
            (`school_id`, `timing_table_id`, `class_name`, `section`, `total_periods`, `created_at`, `is_finalized`, `created_by`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt_cls) {
            throw new Exception('Class meta prepare statement failed: ' . $conn->error);
        }
    
        $stmt_hd = $conn->prepare("INSERT INTO `class_timetable_weekdays` 
            (`school_id`, `weekday`, `assembly_time`, `leave_time`, `total_periods`, `is_half_day`, `created_at`)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt_hd) {
            throw new Exception('Half-day prepare statement failed: ' . $conn->error);
        }
    
        $stmt_period = $conn->prepare("INSERT INTO `class_timetable_details` 
            (`timing_meta_id`, `period_number`, `period_name`, `start_time`, `end_time`, `created_at`, `teacher_id`, `is_break`, `period_type`, `created_by`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if (!$stmt_period) {
            throw new Exception('Period prepare statement failed: ' . $conn->error);
        }
    
        foreach ($classes as $class) {
            // Validate class data
            if (!isset($class['class_name']) || !isset($class['section']) || !isset($class['total_periods'])) {
                throw new Exception('Missing required class data: class_name, section, or total_periods');
            }
            
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
                        // Validate half-day config data
                        if (!isset($info['assembly_time']) || !isset($info['leave_time']) || !isset($info['total_periods'])) {
                            echo "⚠️ Skipping half-day config for {$day}: missing required data<br>";
                            continue;
                        }
                        
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
                        // Validate period data
                        if (!isset($p['period_name']) || !isset($p['start_time']) || !isset($p['end_time'])) {
                            echo "⚠️ Skipping period {$period_num}: missing required data<br>";
                            $period_num++;
                            continue;
                        }
                        
                        $period_name = $p['period_name'];
                        $start_time = $p['start_time'];
                        $end_time = $p['end_time'];
                        $teacher_id = isset($p['teacher_id']) ? $p['teacher_id'] : null;
                        $is_break = isset($p['is_break']) && $p['is_break'] ? 1 : 0;
                        $period_type = isset($p['period_type']) ? $p['period_type'] : '';
    
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
                            throw new Exception("Period insert failed: " . $stmt_period->error);
                        }
    
                        $period_num++;
                    }
                } else {
                    echo "ℹ️ No periods for this class.<br>";
                }
    
            } else {
                throw new Exception("Class insert failed: " . $stmt_cls->error);
            }
        }
    
        $stmt_cls->close();
        $stmt_hd->close();
        $stmt_period->close();
    } else {
        echo "ℹ️ No class blocks to insert.<br>";
    }
    
    echo json_encode(['status' => 'success', 'message' => 'Timetable processed successfully']);
    
} catch (Exception $e) {
    error_log('Error in timetable.php: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Error $e) {
    error_log('Fatal error in timetable.php: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'System error occurred']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>