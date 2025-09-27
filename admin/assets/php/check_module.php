<?php
session_start();
include_once('sass/db_config.php');

// Make sure user is logged in
if (!isset($_SESSION['school_id'])) {
    header("Location: logout.php");
    exit;
}

$school_id = $_SESSION['school_id'];

// Get settings once
$sql = "SELECT attendance_enabled, behavior_enabled, chat_enabled, dairy_enabled, 
               exam_enabled, fee_enabled, library_enabled, meeting_enabled, 
               notice_board_enabled, assign_task_enabled, tests_assignments_enabled, 
               timetable_enabled, transport_enabled
        FROM school_settings
        WHERE person = 'school' AND person_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

// Map modules → disabled pages
$restricted_pages = [
    'fee_enabled' => [
        "fee_slip.php", "submit_student_fee.php", "installments.php",
        "installment_slips.php", "fee_period_form.php", "fee_strutter.php",
        "show_fee_structures.php", "enroll_student_fee_plan.php",
        "fee_structure_view.php", "fee_type.php", "enroll_scholarship.php",
        "load_scholarships.php", "get_submitted_fees.php"
    ],
    'attendance_enabled' => [
        "Attendance.php", "faculty_attendance.php"
    ],
    'library_enabled' => [
        "library_books.php", "Issue_book.php"
    ],
    'meeting_enabled' => [
        "meeting_form.php", "meeting_requests.php"
    ],
    'notice_board_enabled' => [
        "noticeboard.php"
    ],
    'exam_enabled' => [
        "create_exam.php","add_exam.php ", "date_sheet_view.php"
    ],
    'assign_task_enabled'=>[
        "assign_task.php"
    ],
    'timetable_enabled'=>[
        "timetable.php ","view_all_timetable.php"
    ],
    'transport_enabled'=>[
        "transport_dashboard.php ","buses.php","drivers.php","routes.php","student_routes.php"
    ]
    // 👉 Add more as needed...
];

// Get current file name
$current_page = basename($_SERVER['PHP_SELF']);

// Loop settings
foreach ($restricted_pages as $setting_key => $pages) {
    if (isset($settings[$setting_key]) && $settings[$setting_key] == 0) {
        if (in_array($current_page, $pages)) {
            // 🚨 Module disabled → logout
            header("Location: logout.php");
            exit;
        }
    }
}
?>