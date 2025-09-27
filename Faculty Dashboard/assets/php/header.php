<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
  header("Location: logout.php");
  exit;
}
include_once('sass/db_config.php');

$faculty_id = $_SESSION['admin_id']; 

$sql = "SELECT id, full_name, photo, subscription_end, status 
        FROM faculty 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

$faculty = null;
if ($result->num_rows > 0) {
    $faculty = $result->fetch_assoc();

    $today = date("Y-m-d");

    // 🚨 Check subscription expiry
    if (!empty($faculty['subscription_end']) && $faculty['subscription_end'] < $today) {
        
        // If still marked Approved → set to Pending
        if ($faculty['status'] === 'Approved') {
            $update = $conn->prepare("UPDATE faculty SET status = 'Pending' WHERE id = ?");
            $update->bind_param("i", $faculty_id);
            $update->execute();
            $update->close();
        }

        // Force logout
        header("Location: logout.php");
        exit;
    }

} else {
    // fallback/default values if no faculty found
    $faculty = [
        'id' => 0,
        'full_name' => 'Default Faculty Name',
        'photo' => 'assets/img/default-logo.png'
    ];
}
$stmt->close();
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
    <link rel='shortcut icon' type='image/x-icon' href='assets/img/T Logo.png' />

</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">


            <div class="navbar-bg"></div>
            <?php

$admin_id = $_SESSION['admin_id'];

// Fetch faculty settings
$sql = "SELECT * FROM school_settings WHERE person='facility' AND person_id=? LIMIT 1";
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

// Fetch faculty info (for sidebar image and name)
$sql2 = "SELECT * FROM faculty WHERE id=?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $admin_id);
$stmt2->execute();
$res2 = $stmt2->get_result();
$faculty = $res2->fetch_assoc();
$stmt2->close();
?>

            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg main-navbar sticky">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn">
                                <i data-feather="align-justify"></i></a>
                        </li>
                    </ul>
                </div>

                <ul class="navbar-nav navbar-right">

                    <?php if(isEnabled($settings,'chat_enabled')): ?>
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
                            <div class="dropdown-list-content dropdown-list-message">
                                <!-- Messages dynamically loaded via AJAX -->
                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="chat.php">View All <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>

                    <?php if(isEnabled($settings,'meeting_enabled') || isEnabled($settings,'notice_board_enabled')): ?>
                    <li class="dropdown dropdown-list-toggle">
                        <a href="#" data-toggle="dropdown"
                            class="nav-link notification-toggle nav-link-lg position-relative">
                            <i data-feather="bell" class="bell"></i>
                            <span id="facultyNotifDot" class="position-absolute rounded-circle bg-danger"
                                style="width:8px; height:8px; top:5px; right:5px; display:none;"></span>
                        </a>

                        <div class="dropdown-menu dropdown-list dropdown-menu-right pullDown">
                            <div class="dropdown-header">
                                Notifications
                                <div class="float-right">
                                    <a href="#" id="facultyMarkAllRead">Mark All As Read</a>
                                </div>
                            </div>
                            <div class="dropdown-list-content dropdown-list-icons" id="facultyNotifList">
                                <!-- Notifications dynamically loaded via AJAX -->
                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="all_faculty_notifications.php">View All <i
                                        class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li>
                    <?php endif; ?>

                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <img alt="image" src="uploads/profile/<?php echo htmlspecialchars($faculty['photo']); ?>"
                                class="user-img-radious-style">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right pullDown">
                            <div class="dropdown-title">Hello <?php echo htmlspecialchars($faculty['full_name']); ?>
                            </div>
                            <a href="profile.php" class="dropdown-item has-icon"><i class="far fa-user"></i> Profile</a>
                            <a href="javascript:void(0)" class="dropdown-item settingPanelToggle"><i
                                    class="fas fa-cog"></i> Settings</a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item has-icon text-danger"><i
                                    class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- Sidebar -->
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand" style="margin-top:16px; padding-left:10px;">
                        <a href="index.php">
                            <img alt="image" src="uploads/profile/<?php echo htmlspecialchars($faculty['photo']); ?>"
                                class="header-logo" style="width:50px;border-radius:50%;">
                            <span class="logo-name" style="font-size:16px; font-weight:bold; margin-left:10px;">
                                <?php echo htmlspecialchars($faculty['full_name']); ?>
                            </span>
                        </a>
                    </div>

                    <ul class="sidebar-menu">

                        <!-- Dashboard -->
                        <li id="dashboard" class="dropdown">
                            <a href="index.php" class="nav-link">
                                <i data-feather="monitor"></i><span>Dashboard</span>
                            </a>
                        </li>

                        <!-- Apps -->
                        <?php if(isEnabled($settings,'chat_enabled') 
                  || isEnabled($settings,'meeting_enabled') 
                  || isEnabled($settings,'notice_board_enabled') 
                  || isEnabled($settings,'assign_task_enabled')): ?>
                        <li id="apps" class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="command"></i><span>Apps</span>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if(isEnabled($settings,'chat_enabled')): ?>
                                <li><a class="nav-link" href="chat.php">Chat</a></li>
                                <?php endif; ?>
                                <?php if(isEnabled($settings,'attendance_enabled')): ?>
                                <li><a class="nav-link" href="apply_leave.php">Apply for leave</a></li>
                                <?php endif; ?>
                                <?php if(isEnabled($settings,'meeting_enabled')): ?>
                                <li><a class="nav-link" href="meeting_request_form.php">Apply for Meeting</a></li>
                                <li><a class="nav-link" href="teacher_meetings.php">Show Meeting</a></li>
                                <?php endif; ?>
                                <?php if(isEnabled($settings,'notice_board_enabled')): ?>
                                <li><a class="nav-link" href="faculty_notice_board.php">Notice Board</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <!-- Test / Assignment -->
                        <?php if(isEnabled($settings,'tests_assignments_enabled')): ?>
                        <li id="test" class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="clipboard"></i><span>Test / Assignment</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="assignment-test.php">Add</a></li>
                                <li><a class="nav-link" href="assigment-result.php">Submit Result</a></li>
                                <li><a class="nav-link" href="show-result.php">Results</a></li>
                                <li><a class="nav-link" href="exam_results.php">Exam Results</a></li>
                                <li><a class="nav-link" href="show-exam-result.php">View Exam Results</a></li>
                            </ul>
                        </li>
                        <?php endif; ?>

                        <!-- Diary -->
                        <?php if(isEnabled($settings,'dairy_enabled')): ?>
                        <li id="dairyFormContainer" class="dropdown">
                            <a href="Dairy.php" class="nav-link">
                                <i data-feather="book"></i><span>Diary</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Behavior -->
                        <?php if(isEnabled($settings,'behavior_enabled')): ?>
                        <li id="behaviorFormContainer" class="dropdown">
                            <a href="student_behavior.php" class="nav-link">
                                <i data-feather="alert-circle"></i><span>Behavior</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Student Attendance -->
                        <?php if(isEnabled($settings,'attendance_enabled')): ?>
                        <li id="Attendance" class="dropdown">
                            <a href="Attendance.php" class="nav-link">
                                <i data-feather="edit"></i><span>Student Attendance</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Student Leave -->
                        <?php if(isEnabled($settings,'attendance_enabled')): ?>
                        <li id="leaved" class="dropdown">
                            <a href="leaved.php" class="nav-link">
                                <i data-feather="log-out"></i><span>Student Leave</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Students List -->
                        <li id="students_list" class="dropdown">
                            <a href="students_list.php" class="nav-link">
                                <i data-feather="users"></i><span>See all students profile</span>
                            </a>
                        </li>
                    </ul>

                    <div class="sidebar-brand" style="margin-top:10px;">
                        <a href="index.php" style="display:flex; align-items:center;">
                            <img alt="image" src="assets/img/T Logo.png" class="header-logo"
                                style="height:80px; width:auto;">
                            <span class="logo-name"
                                style="font-size:25px; font-weight:bold; margin-left:10px; margin-top:8px;">
                                Lurniva
                            </span>
                        </a>
                    </div>
                </aside>
            </div>