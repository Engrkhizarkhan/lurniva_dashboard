<?php
session_start();
if (!isset($_SESSION['student_id'])) {
  header("Location: logout.php");
  exit;
}
include_once('sass/db_config.php');

$student_id = $_SESSION['student_id']; 

$sql = "SELECT id, full_name, profile_photo AS photo, subscription_end, status 
        FROM students 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$student = null;
if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();

    $today = date("Y-m-d");

    // 🚨 Check subscription expiry
    if (!empty($student['subscription_end']) && $student['subscription_end'] < $today) {

        // If still Approved → set to Pending
        if ($student['status'] === 'Approved') {
            $update = $conn->prepare("UPDATE students SET status = 'Pending' WHERE id = ?");
            $update->bind_param("i", $student_id);
            $update->execute();
            $update->close();
        }

        // Force logout
        header("Location: logout.php");
        exit;
    }

} else {
    // fallback/default values if no student found
    $student = [
        'id' => 0,
        'full_name' => 'Default Student Name',
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
// Fetch student settings
$sql = "SELECT * FROM school_settings WHERE person='student' AND person_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

// Helper function
function isEnabled($settings, $module) {
    return isset($settings[$module]) && $settings[$module] == 1;
}
?>

            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg main-navbar sticky">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn">
                                <i data-feather="align-justify"></i></a>
                        </li>
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
                                <!-- Messages will be loaded here dynamically -->
                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="chat.php">View All <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li>
                    <?php endif?>

                    <li class="dropdown dropdown-list-toggle">
                        <a href="#" data-toggle="dropdown"
                            class="nav-link notification-toggle nav-link-lg position-relative">
                            <i data-feather="bell" class="bell"></i>
                            <!-- Small red dot -->
                            <span id="notifDot" class="position-absolute rounded-circle bg-danger"
                                style="width:8px; height:8px; top:5px; right:5px; display:none;"></span>
                        </a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right pullDown">
                            <div class="dropdown-header">
                                Notifications
                                <div class="float-right"><a href="#" id="markAllRead">Mark All As Read</a></div>
                            </div>
                            <div class="dropdown-list-content dropdown-list-icons" id="notifList">
                                <!-- AJAX-loaded notifications -->
                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="all_notifications.php">View All <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li>

                    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
                    <script>
                    function getNotificationLink(module, type) {
                        const m = (module || "").trim().toLowerCase();
                        const t = (type || "").trim().toLowerCase();
                        const key = (m === "general" ? t : m);
                        if (t === "bus") return "student_transport.php";
                        const map = {
                            notice: "student_notice_board.php",
                            library: "student_library.php",
                            behavior: "StudentBehavior.php",
                            dairy: "Dairy.php",
                            exam: "student_exam_results.php",
                            assignment: "assigment-result.php",
                            test: "assigment-result.php",
                            meeting: "student_meetings.php"
                        };
                        return map[key] || "#";
                    }

                    function loadNotifications() {
                        $.getJSON("ajax/get_notifications.php", res => {
                            if (res.status !== "success") return;
                            let hasOpen = false;
                            let html = "";
                            res.data.forEach(n => {
                                if (n.status === "Open") hasOpen = true;
                                const link = getNotificationLink(n.module, n.type);
                                html += `
                    <a href="${link}" class="dropdown-item ${n.status === "Open" ? "dropdown-item-unread" : ""}" data-id="${n.id}">
                        <span class="dropdown-item-icon ${n.type === "bus" ? "bg-danger" : "bg-primary"} text-white">
                            <i class="fas ${n.type === "bus" ? "fa-bus" : "fa-bell"}"></i>
                        </span>
                        <span class="dropdown-item-desc">
                            ${n.title}
                            <div class="time text-primary">${n.created_at}</div>
                        </span>
                    </a>`;
                            });
                            $("#notifList").html(html);
                            $("#notifDot").toggle(hasOpen);
                        });
                    }

                    loadNotifications();
                    setInterval(loadNotifications, 5000);

                    $(document).on("click", "#notifList a", function(e) {
                        e.preventDefault();
                        const notifId = $(this).data("id");
                        const href = $(this).attr("href");
                        $.post("ajax/mark_notifications_read.php", {
                            notif_id: notifId
                        }, () => {
                            window.location.href = href;
                        });
                    });
                    </script>

                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <img alt="image" src="uploads/profile/<?php echo htmlspecialchars($student['photo']); ?>"
                                class="user-img-radious-style">
                            <span class="d-sm-none d-lg-inline-block"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right pullDown">
                            <div class="dropdown-title">Hello Student</div>
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
                    <div class="sidebar-brand" style="margin-top: 16px; padding-left: 10px; height: fit-content;">
                        <a href="index.php">
                            <img alt="image" src="uploads/profile/<?php echo htmlspecialchars($student['photo']); ?>"
                                class="header-logo" style="width: 50px;border-radius: 50%;" />
                            <span class="logo-name"
                                style="font-size: 16px; font-weight: bold; margin-left: 10px;"><?php echo htmlspecialchars($student['full_name']); ?></span>
                        </a>
                    </div>
                    <ul class="sidebar-menu">

                        <!-- Dashboard -->
                        <li id="dashboard" class="dropdown">
                            <a href="index.php" class="nav-link"><i
                                    data-feather="monitor"></i><span>Dashboard</span></a>
                        </li>
                        <!-- Apps -->
                        <li id="apps" class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="command"></i><span>Apps</span></a>
                            <ul class="dropdown-menu">
                                <?php if(isEnabled($settings,'chat_enabled')): ?>
                                <li><a class="nav-link" href="chat.php">Chat</a></li>
                                <?php endif; ?>
                                <?php if(isEnabled($settings,'attendance_enabled')): ?>
                                <li><a class="nav-link" href="apply_leave.php"> Apply for leave</a></li>
                                <?php endif; ?>
                                <?php if(isEnabled($settings,'meeting_enabled')): ?>
                                <li><a class="nav-link" href="meeting_request_form.php"> Apply for Meeting</a></li>
                                <li><a class="nav-link" href="student_meetings.php"> Show Meeting</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>

                        <!-- Test / Assignment -->
                        <?php if(isEnabled($settings,'tests_assignments_enabled')): ?>
                        <li id="test" class="dropdown">
                            <a href="assigment-result.php" class="nav-link">
                                <i data-feather="clipboard"></i><span>Test / Assignment</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Exam -->
                        <?php if(isEnabled($settings,'exam_enabled')): ?>
                        <li id="exam" class="dropdown">
                            <a href="student_exam_results.php" class="nav-link">
                                <i data-feather="clipboard"></i><span>Exam Result</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Diary -->
                        <?php if(isEnabled($settings,'dairy_enabled')): ?>
                        <li id="dairy" class="dropdown">
                            <a href="Dairy.php" class="nav-link">
                                <i data-feather="book"></i><span>Dairy</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Behavior -->
                        <?php if(isEnabled($settings,'behavior_enabled')): ?>
                        <li id="behaviorFormContainer" class="dropdown">
                            <a href="StudentBehavior.php" class="nav-link">
                                <i data-feather="alert-circle"></i><span>Behavior</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Notice Board -->
                        <?php if(isEnabled($settings,'notice_board_enabled')): ?>
                        <li id="noticeBoardContainer" class="dropdown">
                            <a href="student_notice_board.php" class="nav-link">
                                <i data-feather="bell"></i><span>Notice Board</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Library -->
                        <?php if(isEnabled($settings,'library_enabled')): ?>
                        <li id="student_library" class="dropdown">
                            <a href="student_library.php" class="nav-link">
                                <i data-feather="book"></i><span>Library</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Student Attendance -->
                        <?php if(isEnabled($settings,'attendance_enabled')): ?>
                        <li id="attendance" class="dropdown">
                            <a href="Attendance.php" class="nav-link">
                                <i data-feather="edit"></i><span>Student Attendance</span>
                            </a>
                        </li>
                        <?php endif; ?>

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