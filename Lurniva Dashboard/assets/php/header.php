<?php
session_start();
if (!isset($_SESSION['app_admin_id'])) {
  header("Location: logout.php");
  exit;
}
include_once('sass/db_config.php');

// ✅ Get App Admin Data
$sql = "SELECT id, full_name, email, profile_image FROM app_admin WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['app_admin_id']);
$stmt->execute();
$result = $stmt->get_result();

$admin = $result->fetch_assoc() ?? [
    'id' => 0,
    'name' => 'App Admin',
    'email' => 'admin@lurniva.com',
    'logo' => 'assets/img/default-logo.png'
];

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>App Admin Dashboard</title>
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

    <!-- Extra Plugins -->
    <link rel="stylesheet" href="assets/bundles/bootstrap-daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="assets/bundles/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="assets/bundles/select2/dist/css/select2.min.css">
    <link rel="stylesheet" href="assets/bundles/jquery-selectric/selectric.css">
    <link rel="stylesheet" href="assets/bundles/bootstrap-timepicker/css/bootstrap-timepicker.min.css">
    <link rel="stylesheet" href="assets/bundles/bootstrap-tagsinput/dist/bootstrap-tagsinput.css">

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/T Logo.png" />
</head>

<body>
    <div id="app">
        <div class="main-wrapper main-wrapper-1">

            <!-- NAVBAR -->
            <div class="navbar-bg"></div>
            <nav class="navbar navbar-expand-lg main-navbar sticky">
                <div class="form-inline mr-auto">
                    <ul class="navbar-nav mr-3">
                        <li>
                            <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg collapse-btn">
                                <i data-feather="align-justify"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <ul class="navbar-nav navbar-right">
                    <!-- Messages -->
                    <!-- <li class="dropdown dropdown-list-toggle">
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
                               
                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="chat.php">View All <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li> -->

                    <!-- Notifications -->
                    <li class="dropdown dropdown-list-toggle">
                        <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg">
                            <i data-feather="bell" class="bell"></i>
                        </a>
                        <div class="dropdown-menu dropdown-list dropdown-menu-right pullDown">
                            <div class="dropdown-header">
                                Notifications
                                <div class="float-right"><a href="#">Mark All As Read</a></div>
                            </div>
                            <div class="dropdown-list-content dropdown-list-icons">
                                <a href="#" class="dropdown-item dropdown-item-unread">
                                    <span class="dropdown-item-icon bg-primary text-white"><i
                                            class="fas fa-code"></i></span>
                                    <span class="dropdown-item-desc">Tomorrow is Public Holiday</span>
                                </a>
                            </div>
                            <div class="dropdown-footer text-center">
                                <a href="#">View All <i class="fas fa-chevron-right"></i></a>
                            </div>
                        </div>
                    </li>

                    <!-- Profile -->
                    <li class="dropdown">
                        <a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
                            <img alt="image" src="<?php echo htmlspecialchars($admin['profile_image']); ?>"
                                class="user-img-radious-style">
                            <span class="d-sm-none d-lg-inline-block"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right pullDown">
                            <div class="dropdown-title">Hello, <?php echo htmlspecialchars($admin['full_name']); ?>
                            </div>
                            <a href="profile.php" class="dropdown-item has-icon"><i class="far fa-user"></i> Profile</a>
                            <a href="settings.php" class="dropdown-item has-icon"><i class="fas fa-cog"></i>
                                Settings</a>
                            <div class="dropdown-divider"></div>
                            <a href="logout.php" class="dropdown-item has-icon text-danger">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>

            <!-- SIDEBAR -->
            <div class="main-sidebar sidebar-style-2">
                <aside id="sidebar-wrapper">
                    <div class="sidebar-brand" style="margin-top:16px; padding-left:10px;">
                        <a href="index.php">
                            <img alt="image" src="assets/img/Final Logo (1).jpg" class="header-logo"
                                style="width:50px; border-radius:50%;" />
                            <span class="logo-name" style="font-size:16px; font-weight:bold; margin-left:10px;">
                                Lurniva
                            </span>
                        </a>
                    </div>

                    <ul class="sidebar-menu">
                        <!-- Dashboard -->
                        <li id="dashboard" class="dropdown">
                            <a id="dashboard" href="index.php" class="nav-link">
                                <i data-feather="monitor"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <!-- Graphs -->
                        <li id="chart" class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="bar-chart-2"></i>
                                <span>Graphs</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="development.php">Lurniva Development</a></li>
                                <li><a class="nav-link" href="revenue.php">Lurniva Revenue</a></li>
                            </ul>
                        </li>

                        <!-- Apps -->
                        <!-- <li id="apps" class="dropdown">
                            <a id="app_link" href="#" class="menu-toggle nav-link has-dropdown">
                                <i data-feather="command"></i>
                                <span>Apps</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="chat.php">Chat</a></li>
                                <li><a class="nav-link" href="calendar.php">Calendar</a></li>
                            </ul>
                        </li> -->

                        <!-- Schools Approval -->
                        <li id="approvals" class="dropdown">
                            <a id="approvals" href="approvals.php" class="nav-link">
                                <i data-feather="clipboard"></i>
                                <span>Schools Approval</span>
                            </a>
                        </li>

                        <!-- Payment Plans -->
                        <li id="paymentplans" class="dropdown">
                            <a href="paymentplan.php" class="nav-link">
                                <i data-feather="book"></i>
                                <span>Payment Plans</span>
                            </a>
                        </li>
                    </ul>

                    <!-- 
                    <div class="sidebar-brand" style="margin-top:20px;">
                        <a href="index.php" style="display:flex; align-items:center;">
                            <img alt="image" src="assets/img/Final Logo (1).jpg" class="header-logo"
                                style="height:80px; width:auto;" />
                            <span class="logo-name"
                                style="font-size:22px; font-weight:bold; margin-left:10px;">Lurniva</span>
                        </a>
                    </div> -->
                </aside>
            </div>