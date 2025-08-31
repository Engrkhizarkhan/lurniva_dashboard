<?php
session_start();
if (!isset($_SESSION['student_id'])) {
  header("Location: logout.php");
  exit;
}
include_once('sass/db_config.php');

$school_id = $_SESSION['student_id']; // or dynamically get this if needed

$sql = "SELECT id, full_name, profile_photo AS photo FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$school = null;
if ($result->num_rows > 0) {
    $school = $result->fetch_assoc();
} else {
    // fallback/default values if no school found
    $school = [
        'id' => 0,
        'full_name' => 'Default School Name',
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
            <nav class="navbar navbar-expand-lg main-navbar sticky">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg
									collapse-btn"> <i data-feather="align-justify"></i></a></li>
                        <!-- <li>
                            <form class="form-inline mr-auto">

                                <div class="search-element">
                                  <input class="form-control" type="search" placeholder="Search" aria-label="Search" data-width="200">
                                  <button class="btn" type="submit">
                                    <i class="fas fa-search"></i>
                                  </button>
                                </div> 

                            </form>
                        </li> -->
                    </ul>
                </div>
                <ul class="navbar-nav navbar-right">
                    <li class="dropdown dropdown-list-toggle"><a href="#" data-toggle="dropdown"
                            class="nav-link nav-link-lg message-toggle"><i data-feather="mail"></i>
                            <span class="badge headerBadge1">
                            </span> </a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right pullDown">
                            <div class="dropdown-header">
                                Messages
                                <div class="float-right">
                                    <a href="#">Mark All As Read</a>
                                </div>
                            </div>
                            <div class="dropdown-list-content dropdown-list-message">
                                <!-- Messages will be loaded here dynamically -->
                            </div>

                            <div class="dropdown-footer text-center">
                                <a href="chat.php">View All <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="dropdown dropdown-list-toggle"><a href="#" data-toggle="dropdown"
                            class="nav-link notification-toggle nav-link-lg"><i data-feather="bell" class="bell"></i>
                        </a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right pullDown">
                            <div class="dropdown-header">
                                Notifications
                                <div class="float-right">
                                    <a href="#">Mark All As Read</a>
                                </div>
                            </div>
                            <div class="dropdown-list-content dropdown-list-icons">
                                <a href="#" class="dropdown-item dropdown-item-unread"> <span
                                        class="dropdown-item-icon bg-primary text-white"> <i class="fas
												fa-code"></i>
                                    </span> <span class="dropdown-item-desc">Tomorrow is Public Holiday
                                    </span>
                                </a> <a href="#" class="dropdown-item"> <span
                                        class="dropdown-item-icon bg-info text-white"> <i class="far
												fa-user"></i>
                                    </span> <span class="dropdown-item-desc">Today is Assignment 4 Last Date</span>
                                </a> <a href="#" class="dropdown-item"> <span
                                        class="dropdown-item-icon bg-success text-white"> <i class="fas
												fa-check"></i>
                                    </span> <span class="dropdown-item-desc"> Check email for new messages!
                                    </span>

                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="#">View All <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li>
                    <li class="dropdown"><a href="#" data-toggle="dropdown"
                            class="nav-link dropdown-toggle nav-link-lg nav-link-user"> <img alt="image"
                                src="uploads/profile/<?php echo htmlspecialchars($school['photo']); ?>"
                                class="user-img-radious-style"> <span class="d-sm-none d-lg-inline-block"></span></a>
                        <div class="dropdown-menu dropdown-menu-right pullDown">
                            <div class="dropdown-title">Hello Admin</div>
                            <a href="profile.php" class="dropdown-item has-icon"> <i class="far
										fa-user"></i> Profile
                            </a>
                            <a href="javascript:void(0)" class="dropdown-item settingPanelToggle"> <i
                                    class="fas fa-cog"></i>
                                Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item has-icon text-danger"> <i
                                    class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand" style="margin-top: 16px; padding-left: 10px;   height: fit-content;">
                        <a href="index.php">
                            <img alt="image" src="uploads/profile/<?php echo htmlspecialchars($school['photo']); ?>"
                                class="header-logo" style="width: 50px;border-radius: 50%;" />
                            <span class="logo-name"
                                style="font-size: 16px; font-weight: bold; margin-left: 10px;"><?php echo htmlspecialchars($school['full_name']); ?></span>
                        </a>
                    </div>
                    <ul class="sidebar-menu">
                        <li id="dashboard" class="dropdown">
                            <a href="index.php" class="nav-link"><i
                                    data-feather="monitor"></i><span>Dashboard</span></a>
                        </li>
                        <!-- <li id="graphs" class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="bar-chart-2"></i><span>Graphs</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="student-profile.php">Student Profile Visualization</a>
                                </li>
                                <li><a class="nav-link" href="academic.php">Academic Reporting</a></li>
                            </ul>
                        </li> -->

                        <li id="apps" class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="command"></i><span>Apps</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="chat.php">Chat</a></li>
                                <!-- <li><a class="nav-link" href="calendar.php">Calendar</a></li> -->
                                <li><a class="nav-link" href="apply_leave.php"> Apply for leave</a></li>
                                <li><a class="nav-link" href="meeting_request_form.php"> Apply for Meeting</a></li>
                                <li><a class="nav-link" href="student_meetings.php"> Show Meeting</a></li>
                            </ul>
                        </li>
                        <!-- <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="clipboard"></i><span>Test / Assignment</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="assigment-result.php">Result</a></li>
                            </ul>
                        </li> -->
                        <li id="test" class="dropdown">
                            <a href="assigment-result.php" class="nav-link">
                                <i data-feather="clipboard"></i><span>Test / Assignment</span>
                            </a>
                        </li>
                        <li id="exam" class="dropdown">
                            <a href="student_exam_results.php" class="nav-link">
                                <i data-feather="clipboard"></i><span>Exam Result</span>
                            </a>
                        </li>

                        <li id="dairy" class="dropdown">
                            <a href="Dairy.php" class="nav-link">
                                <i data-feather="book"></i><span>Dairy</span>
                            </a>
                        </li>
                        <li id="attendance" class="dropdown">
                            <a href="Attendance.php" class="nav-link">
                                <i data-feather="edit"></i><span>Student Attendance</span>
                            </a>
                        </li>

                    </ul>
                    </ul>

                    <div class="sidebar-brand" style="margin-top: 10px;">
                        <a href="index.php" style="display: flex; align-items: center;">
                            <img alt="image" src="assets/img/T Logo.png" class="header-logo"
                                style="height: 80px; width: auto;" />
                            <span class="logo-name"
                                style="font-size: 25px; font-weight: bold; margin-left: 10px; margin-top: 8px;">Lurniva</span>
                        </a>
                    </div>

                </aside>
            </div>