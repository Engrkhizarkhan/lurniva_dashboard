<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: logout.php");
  exit;
}
include_once('sass/db_config.php');

$school_id = $_SESSION['admin_id']; 

$sql = "SELECT id, username AS school_name, logo, subscription_end, status 
        FROM schools 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$school = null;
if ($result->num_rows > 0) {
    $school = $result->fetch_assoc();

    $today = date("Y-m-d");

    // 🚨 Check subscription expiry
    if (!empty($school['subscription_end']) && $school['subscription_end'] < $today) {
        
        // If still marked Approved → set to Pending
        if ($school['status'] === 'Approved') {
            $update = $conn->prepare("UPDATE schools SET status = 'Pending' WHERE id = ?");
            $update->bind_param("i", $school_id);
            $update->execute();
            $update->close();
        }

        // Force logout
        header("Location: logout.php");
        exit;
    }

} else {
    // fallback/default values if no school found
    $school = [
        'id' => 0,
        'school_name' => 'Default School Name',
        'logo' => 'assets/img/default-logo.png'
    ];
}
$stmt->close();
// include_once("check_module.php");


$admin_id = $_SESSION['admin_id'];

// Fetch admin settings
$sql = "SELECT * FROM school_settings WHERE person='admin' AND person_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();



// Helper function
function isEnabled($settings, $module) {
    return isset($settings[$module]) && $settings[$module] == 1;
}

?>


<!DOCTYPE html>
<html lang="en">


<!-- index.php  21 Nov 2019 03:44:50 GMT -->

<head>
    <title>Admin Dashboard</title>

    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <!-- General CSS Files -->
    <link rel="stylesheet" href="assets/css/app.min.css">
    <link rel="stylesheet" href="assets/bundles/prism/prism.css">
    <!-- Template CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <!-- Custom style CSS -->
    <link rel="stylesheet" href="assets/css/custom.css">

    <link rel="stylesheet" href="assets/bundles/bootstrap-daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="assets/bundles/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="assets/bundles/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="assets/bundles/jquery-selectric/selectric.css">
    <link rel="stylesheet" href="assets/bundles/bootstrap-timepicker/css/bootstrap-timepicker.min.css">
    <link rel="stylesheet" href="assets/bundles/bootstrap-tagsinput/dist/bootstrap-tagsinput.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel='shortcut icon' type='image/x-icon' href='assets/img/T Logo.png' />

</head>

<body>


    <div id="app">
        <div class="main-wrapper main-wrapper-1">


            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar sticky">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn">
                                <i data-feather="align-justify"></i></a></li>
                    </ul>
                </div>

                <ul class="navbar-nav navbar-right">
                    <?php if (isEnabled($settings, 'chat_enabled')): ?>
                    <li class="dropdown dropdown-list-toggle">
                        <a href="#" data-toggle="dropdown" class="nav-link nav-link-lg message-toggle">
                            <i data-feather="mail"></i>
                            <span class="badge headerBadge1"></span>
                        </a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right pullDown">
                            <div class="dropdown-header">
                                Messages
                                <div class="float-right"><a href="#">Mark All As Read</a></div>
                            </div>
                            <div class="dropdown-list-content dropdown-list-message"></div>
                            <div class="dropdown-footer text-center">
                                <a href="chat.php">View All <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>

                    <?php if (isEnabled($settings, 'meeting_enabled') || isEnabled($settings, 'notice_board_enabled')): ?>
                    <li class="dropdown dropdown-list-toggle">
                        <a href="#" data-toggle="dropdown"
                            class="nav-link notification-toggle nav-link-lg position-relative">
                            <i data-feather="bell" class="bell"></i>
                            <span id="notifDot" class="position-absolute rounded-circle bg-danger"
                                style="width:8px; height:8px; top:5px; right:5px; display:none;"></span>
                        </a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right pullDown">
                            <div class="dropdown-header">
                                Notifications
                                <div class="float-right"><a href="#" id="markAllRead">Mark All As Read</a></div>
                            </div>
                            <div class="dropdown-list-content dropdown-list-icons" id="notifList"></div>
                            <div class="dropdown-footer text-center">
                                <a href="all_admin_notifications.php">View All <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>

                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <img alt="image" src="uploads/logos/<?php echo htmlspecialchars($school['logo']); ?>"
                                class="user-img-radious-style">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right pullDown">
                            <div class="dropdown-title">Hello Admin</div>
                            <a href="profile.php" class="dropdown-item has-icon"><i class="far fa-user"></i> Profile</a>
                            <a href="setting.php" class="dropdown-item has-icon"><i class="fas fa-cog"></i> Settings</a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item has-icon text-danger"><i
                                    class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand" style="margin-top:16px; padding-left:10px;">
                        <a href="index.php">
                            <img alt="image" src="uploads/logos/<?php echo htmlspecialchars($school['logo']); ?>"
                                class="header-logo" style="width:50px;border-radius:50%;">
                            <span class="logo-name"><?php echo htmlspecialchars($school['school_name']); ?></span>
                        </a>
                    </div>

                    <ul class="sidebar-menu">
                        <!-- Dashboard -->
                        <li class="dropdown">
                            <a href="index.php" class="nav-link"><i
                                    data-feather="monitor"></i><span>Dashboard</span></a>
                        </li>

                        <!-- Apps -->
                        <?php if (isEnabled($settings, 'chat_enabled') || isEnabled($settings, 'meeting_enabled') || isEnabled($settings, 'notice_board_enabled') || isEnabled($settings, 'assign_task_enabled')): ?>
                        <li id="apps" class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="command"></i><span>Apps</span></a>
                            <ul class="dropdown-menu">
                                <?php if (isEnabled($settings, 'chat_enabled')): ?>
                                <li><a class="nav-link" href="chat.php">Chat</a></li>
                                <?php endif; ?>
                                <?php if (isEnabled($settings, 'meeting_enabled')): ?>
                                <li><a class="nav-link" href="meeting_form.php">Meeting Scheduler</a></li>
                                <li><a class="nav-link" href="meeting_requests.php">Meeting Requested</a></li>
                                <?php endif; ?>
                                <?php if (isEnabled($settings, 'notice_board_enabled')): ?>
                                <li><a class="nav-link" href="noticeboard.php">Digital Notice Board</a></li>
                                <?php endif; ?>
                                <?php if (isEnabled($settings, 'assign_task_enabled')): ?>
                                <li><a class="nav-link" href="assign_task.php">Assign Task</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <!-- Attendance -->
                        <?php if (isEnabled($settings, 'attendance_enabled')): ?>
                        <li><a href="Attendance.php" class="nav-link"><i data-feather="edit"></i> Faculty Attendance</a>
                        </li>
                        <li><a href="faculty_attendance.php" class="nav-link"><i data-feather="edit"></i> Show Faculty
                                Attendance</a></li>
                        <?php endif; ?>

                        <!-- Time Table & Exams -->
                        <?php if (isEnabled($settings, 'timetable_enabled') || isEnabled($settings, 'exam_enabled')): ?>
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="layout"></i><span>Time Table & Exams</span></a>
                            <ul class="dropdown-menu">
                                <?php if (isEnabled($settings, 'timetable_enabled')): ?>
                                <li><a class="nav-link" href="timetable.php">Create Time Table</a></li>
                                <li><a class="nav-link" href="view_all_timetable.php">See Time Table</a></li>
                                <?php endif; ?>
                                <?php if (isEnabled($settings, 'exam_enabled')): ?>
                                <li><a class="nav-link" href="create_exam.php">Create Exam</a></li>
                                <li><a class="nav-link" href="add_exam.php">Add Subject</a></li>
                                <li><a class="nav-link" href="date_sheet_view.php">See Date-Sheet</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <!-- Fee -->
                        <?php if (isEnabled($settings, 'fee_enabled')): ?>
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="dollar-sign"></i><span>Fee</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="fee_slip.php">Fee Slip</a></li>
                                <li><a class="nav-link" href="submit_student_fee.php">Submit Student Fee</a></li>
                                <li><a class="nav-link" href="installments.php">Installments</a></li>
                                <li><a class="nav-link" href="installment_slips.php">Installment Slips</a></li>
                                <li><a class="nav-link" href="fee_period_form.php">Fee Period</a></li>
                                <li><a class="nav-link" href="fee_strutter.php">Add Class Fee Plan</a></li>
                                <li><a class="nav-link" href="show_fee_structures.php">View Class Fee Plan</a></li>
                                <li><a class="nav-link" href="enroll_student_fee_plan.php">Student Fee Plan</a></li>
                                <li><a class="nav-link" href="fee_structure_view.php">All Students Fee Structure</a>
                                </li>
                                <li><a class="nav-link" href="fee_type.php">Fee Type</a></li>
                                <li><a class="nav-link" href="enroll_scholarship.php">Scholarship Form</a></li>
                                <li><a class="nav-link" href="load_scholarships.php">Scholarship</a></li>
                                <li><a class="nav-link" href="get_submitted_fees.php">Submitted Fee</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <!-- Library -->
                        <?php if (isEnabled($settings, 'library_enabled')): ?>
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="book"></i><span>Library</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="library_books.php">Manage Books</a></li>
                                <li><a class="nav-link" href="Issue_book.php">Assign Book</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <!-- Transport -->
                        <?php if (isEnabled($settings, 'transport_enabled')): ?>
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="truck"></i><span>Transport</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="transport_dashboard.php">Transport Dashboard</a></li>
                                <li><a class="nav-link" href="buses.php">Manage Buses</a></li>
                                <li><a class="nav-link" href="drivers.php">Manage Drivers</a></li>
                                <li><a class="nav-link" href="routes.php">Manage Routes</a></li>
                                <li><a class="nav-link" href="student_routes.php">Assign Students</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>
                    </ul>

                </aside>
            </div>