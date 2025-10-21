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
                                    </span> <span class="dropdown-item-desc">Tommorrow is Public Holiday
                                    </span>
                                </a> <a href="#" class="dropdown-item"> <span
                                        class="dropdown-item-icon bg-info text-white"> <i class="far
												fa-user"></i>
                                    </span> <span class="dropdown-item-desc">Today is Assignment 4 Last Date
                                    </span>
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
                                src="uploads/logos/<?php echo htmlspecialchars($school['logo']); ?>"
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
                            <img alt="image" src="uploads/logos/<?php echo htmlspecialchars($school['logo']); ?>"
                                class="header-logo" style="width: 50px;border-radius: 50%;" />
                            <span class="logo-name"
                                style="font-size: 16px; font-weight: bold; margin-left: 10px;"><?php echo htmlspecialchars($school['school_name']); ?></span>
                        </a>
                    </div>
                    <ul class="sidebar-menu">
                        <!-- <li class="menu-header">Main</li> -->
                        <li class="dropdown ">
                            <a href="index.php" class="nav-link"><i
                                    data-feather="monitor"></i><span>Dashboard</span></a>
                        </li>
                        <!-- <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="bar-chart-2"></i><span>Graphs</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="widget-chart.php">Revenue / Cost Chart</a></li>
                                <li><a class="nav-link" href="widget-data.php">Student Profile Visualization</a></li>
                                <li><a class="nav-link" href="academic.php">Academic Reporting</a></li>
                                <li><a class="nav-link" href="socitiesclub.php">Societies Club</a></li>
                            </ul>
                        </li> -->

                        <li id="apps" class="dropdown">
                            <a id="app_link" href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="command"></i><span>Apps</span></a>
                            <ul class="dropdown-menu">
                                <li><a id="chat" class="nav-link" href="chat.php">Chat</a></li>
                                <!-- <li><a class="nav-link" href="calendar.php">Calendar</a></li> -->
                                <li><a class="nav-link" href="meeting_form.php">Meeting Scheduler</a></li>
                                <li><a class="nav-link" href="noticeboard.php">Digital Notice Board</a></li>
                                <li><a class="nav-link" href="students_list.php">Student</a></li>
                                <li><a class="nav-link" href="assign_task.php">Assign task</a></li>
                            </ul>
                        </li>
                        <li class="dropdown active">
                            <a id="attendance" href="Attendance.php" class="nav-link"
                                style="background-color: transparent !important; box-shadow: none !important; color: black !important;">
                                <i data-feather="edit" style="color: rgb(78, 77, 77) !important;"></i>
                                <span style="color: rgb(78, 77, 77) !important;">Attendance</span>
                            </a>
                        </li>

                        <li class="dropdown">
                            <a id="timetable" href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="layout"></i><span>Time Table</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="timetable.php">Create Time Table</a></li>
                                <li><a class="nav-link" href="view_all_timetable.php">See Time Table</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a id="fee_type" href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="layout"></i><span>Fee</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="fee_slip.php">Fee Slip</a></li>
                                <li><a class="nav-link" href="submit_student_fee.php">Submit Student Fee</a></li>
                                <li><a class="nav-link" href="fee_period_form.php">Fee Period</a></li>
                                <li><a class="nav-link" href="fee_strutter.php">Add Class Fee Plan</a></li>
                                <li><a class="nav-link" href="show_fee_structures.php">View Class Fee Plan</a></li>
                                <li><a class="nav-link" href="enroll_student_fee_plan.php">Student Fee Plan</a></li>
                                <li><a class="nav-link" href="fee_structure_view.php">All Students Fee
                                        Structure</a></li>
                                <li><a class="nav-link" href="fee_type.php">Fee Type</a></li>
                                <li><a class="nav-link" href="enroll_scholarship.php">Scholarship Form</a></li>
                                <li><a class="nav-link" href="load_scholarships.php">Scholarship</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="mail"></i><span>Email</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="email-inbox.php">Inbox</a></li>
                                <li><a class="nav-link" href="email-compose.php">Compose</a></li>
                                <li><a class="nav-link" href="email-read.php">read</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="facultyForm" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="layout"></i><span>Forms</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="faculty_registration.php">Faculty Registration</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" id="Managements" class="menu-toggle nav-link has-dropdown"><i
                                    data-feather="grid"></i><span>Managements</span></a>
                            <ul class="dropdown-menu">
                                <li><a class="nav-link" href="leaved.php">Leave Management</a></li>
                            </ul>
                        </li>

                    </ul>
                    </ul>

                    <!-- Add space above the bottom logo -->
                    <div class="sidebar-brand" style="margin-top: 10px;">
                        <a href="index.php" style="display: flex; align-items: center;">
                            <img alt="image" src="assets/img/Final Logo (1).jpg" class="header-logo"
                                style="height: 80px; width: auto;" />
                            <span class="logo-name"
                                style="font-size: 25px; font-weight: bold; margin-left: 10px; margin-top: 8px;">Lurniva</span>
                        </a>
                    </div>

                </aside>
            </div>
            <style>
            #app_link {
                padding-left: 20px;
                color: #6777ef !important;
                background-color: #f0f3ff;
            }

            #apps ul {
                display: block !important;
            }

            #chat {
                color: #000;
            }
            </style>
            <!-- Main Content -->
            <div class="main-content">
                <section class="section">
                    <div class="section-body">
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                                <div class="card">
                                    <div class="body">
                                        <div id="plist" class="people-list">
                                            <div class="chat-search">
                                                <input type="text" class="form-control" placeholder="Search..." />
                                            </div>
                                            <div class="m-b-20">
                                                <div id="chat-scroll">
                                                    <ul class="chat-list list-unstyled m-b-0"></ul>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
                                <div class="card">
                                    <div class="chat">
                                        <div class="chat-header clearfix">
                                            <img src="" alt="avatar">
                                            <div class="chat-about">
                                                <div class="chat-with"></div>
                                                <div class="chat-num-messages"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Chat Messages Section -->
                                    <div class="chat-box" id="mychatbox"
                                        style="padding: 15px; height: 300px; overflow-y: auto;">
                                        <!-- Incoming and outgoing messages will be loaded dynamically -->
                                    </div>
                                    <!-- Message Input -->
                                    <div class="card-footer chat-form">
                                        <form style="display: flex; gap: 10px; align-items: center;" id="chatForm"
                                            enctype="multipart/form-data">
                                            <input type="hidden" name="receiver_id" id="receiverId" value="">
                                            <input type="hidden" name="receiver_designation" id="receiverDesignation"
                                                value="">

                                            <input type="text" id="chatInput" class="form-control" name="message"
                                                placeholder="Type your message...">
                                            <input type="file" id="fileUpload" name="file_attachment"
                                                style="display:none;">
                                            <button type="button" class="btn btn-primary" id="attachBtn"><i
                                                    class="fas fa-paperclip"></i></button>


                                            <button class="btn btn-primary" type="button" id="startRecordingBtn"><i
                                                    class="fas fa-microphone-alt"></i></button>
                                            <button type="submit" class="btn btn-primary"><i
                                                    class="far fa-paper-plane"></i></button>
                                        </form>

                                    </div>


                                </div>
                            </div>
                </section>
                <div class="settingSidebar">
                    <a href="javascript:void(0)" class="settingPanelToggle"> <i class="fa fa-spin fa-cog"></i>
                    </a>
                    <div class="settingSidebar-body ps-container ps-theme-default">
                        <div class=" fade show active">
                            <div class="setting-panel-header">Setting Panel
                            </div>
                            <div class="p-15 border-bottom">
                                <h6 class="font-medium m-b-10">Select Layout</h6>
                                <div class="selectgroup layout-color w-50">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="value" value="1"
                                            class="selectgroup-input-radio select-layout" checked>
                                        <span class="selectgroup-button">Light</span>
                                    </label>
                                    <label class="selectgroup-item">
                                        <input type="radio" name="value" value="2"
                                            class="selectgroup-input-radio select-layout">
                                        <span class="selectgroup-button">Dark</span>
                                    </label>
                                </div>
                            </div>
                            <div class="p-15 border-bottom">
                                <h6 class="font-medium m-b-10">Sidebar Color</h6>
                                <div class="selectgroup selectgroup-pills sidebar-color">
                                    <label class="selectgroup-item">
                                        <input type="radio" name="icon-input" value="1"
                                            class="selectgroup-input select-sidebar">
                                        <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                                            data-original-title="Light Sidebar"><i class="fas fa-sun"></i></span>
                                    </label>
                                    <label class="selectgroup-item">
                                        <input type="radio" name="icon-input" value="2"
                                            class="selectgroup-input select-sidebar" checked>
                                        <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                                            data-original-title="Dark Sidebar"><i class="fas fa-moon"></i></span>
                                    </label>
                                </div>
                            </div>
                            <div class="p-15 border-bottom">
                                <h6 class="font-medium m-b-10">Color Theme</h6>
                                <div class="theme-setting-options">
                                    <ul class="choose-theme list-unstyled mb-0">
                                        <li title="white" class="active">
                                            <div class="white"></div>
                                        </li>
                                        <li title="cyan">
                                            <div class="cyan"></div>
                                        </li>
                                        <li title="black">
                                            <div class="black"></div>
                                        </li>
                                        <li title="purple">
                                            <div class="purple"></div>
                                        </li>
                                        <li title="orange">
                                            <div class="orange"></div>
                                        </li>
                                        <li title="green">
                                            <div class="green"></div>
                                        </li>
                                        <li title="red">
                                            <div class="red"></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="p-15 border-bottom">
                                <div class="theme-setting-options">
                                    <label class="m-b-0">
                                        <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                                            id="mini_sidebar_setting">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="control-label p-l-10">Mini Sidebar</span>
                                    </label>
                                </div>
                            </div>
                            <div class="p-15 border-bottom">
                                <div class="theme-setting-options">
                                    <label class="m-b-0">
                                        <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input"
                                            id="sticky_header_setting">
                                        <span class="custom-switch-indicator"></span>
                                        <span class="control-label p-l-10">Sticky Header</span>
                                    </label>
                                </div>
                            </div>
                            <div class="mt-4 mb-4 p-3 align-center rt-sidebar-last-ele">
                                <a href="#" class="btn btn-icon icon-left btn-primary btn-restore-theme">
                                    <i class="fas fa-undo"></i> Restore Default
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <footer class="main-footer">


                    <footer class="footer d-flex justify-content-center align-items-center" style="height: 20px;">
                        <div class="text-center">
                            All Rights Reserved to <strong>Lurniva</strong> @developed by <strong>SUIT
                                Incubation</strong>
                        </div>
                    </footer>


                    <div class="footer-right">
                    </div>
                </footer>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script>
        $(document).ready(function() {
            // =====================
            // Trigger file browse on attach button click
            // =====================
            $('#attachBtn').on('click', function() {
                $('#fileUpload').click();
            });

            let currentChatId = null;
            let currentChatDesignation = null;

            // =====================
            // Load Contacts Initially
            // =====================
            loadChatContacts();

            function loadChatContacts() {
                $.ajax({
                    url: 'ajax/fetch_contacts.php',
                    type: 'POST',
                    success: function(response) {
                        $('.chat-list').html(response);
                    },
                    error: function() {
                        $('.chat-list').html('<li>Error loading contacts</li>');
                    }
                });
            }

            // =====================
            // Open a Chat with User
            // =====================
            $(document).on('click', '.open-chat-data', function() {
                var sender_id = $(this).data('sender-id');
                var sender_designation = $(this).data('sender-designation');
                var sender_name = $(this).find('.name').text();
                var sender_photo = $(this).find('img').attr('src');

                $('.chat-with').text(sender_name);
                $('.chat-header img').attr('src', sender_photo);

                currentChatId = sender_id;
                currentChatDesignation = sender_designation;

                $('#receiverId').val(sender_id);
                $('#receiverDesignation').val(sender_designation);

                loadChatMessages(sender_id, sender_designation);
            });

            // =====================
            // File Attachment Name Preview (does not overwrite message)
            // =====================
            $('#fileUpload').on('change', function() {
                const file = this.files[0];
                if (file) {
                    $('#chatInput').val(function(i, val) {
                        return val ? val + ' ' + file.name : file.name;
                    });
                }
            });

            // =====================
            // Send Text or File Message
            // =====================
            $('#chatForm').on('submit', function(e) {
                e.preventDefault();

                if (!currentChatId || !currentChatDesignation) {
                    alert("Please select a contact before sending.");
                    return;
                }

                const formData = new FormData(this);
                const message = formData.get('message').trim();
                const file = $('#fileUpload')[0].files[0];

                if (!message && !file) {
                    alert("Please enter a message or attach a file.");
                    return;
                }

                formData.set('receiver_id', currentChatId);
                formData.set('receiver_designation', currentChatDesignation);

                $.ajax({
                    url: 'ajax/send_message.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.trim() === 'success') {
                            $('#chatInput').val('');
                            $('#fileUpload').val('');
                            loadChatMessages(currentChatId, currentChatDesignation);
                        } else {
                            alert('Error: ' + response);
                        }
                    },
                    error: function() {
                        alert('Failed to send message.');
                    }
                });
            });

            // =====================
            // Voice Note Recording
            // =====================
            let mediaRecorder;
            let audioChunks = [];

            $('#startRecordingBtn').on('click', function() {
                if (!currentChatId || !currentChatDesignation) {
                    alert("Please select a contact before recording.");
                    return;
                }

                if (!mediaRecorder || mediaRecorder.state === 'inactive') {
                    startRecording();
                } else {
                    stopRecording();
                }
            });

            function startRecording() {
                navigator.mediaDevices.getUserMedia({
                        audio: true
                    })
                    .then(stream => {
                        mediaRecorder = new MediaRecorder(stream);
                        audioChunks = [];

                        mediaRecorder.ondataavailable = event => {
                            if (event.data.size > 0) {
                                audioChunks.push(event.data);
                            }
                        };

                        mediaRecorder.onstop = () => {
                            const audioBlob = new Blob(audioChunks, {
                                type: 'audio/webm'
                            });
                            sendVoiceNote(audioBlob);
                        };

                        mediaRecorder.start();
                        $('#startRecordingBtn i').addClass('recording');
                    })
                    .catch(() => alert('Microphone access denied.'));
            }

            function stopRecording() {
                if (mediaRecorder && mediaRecorder.state === 'recording') {
                    mediaRecorder.stop();
                    $('#startRecordingBtn i').removeClass('recording');
                }
            }

            function sendVoiceNote(blob) {
                const formData = new FormData();
                formData.append('voice_note', blob);
                formData.append('receiver_id', currentChatId);
                formData.append('receiver_designation', currentChatDesignation);

                $.ajax({
                    url: 'ajax/send_message.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.trim() === 'success') {
                            loadChatMessages(currentChatId, currentChatDesignation);
                        } else {
                            alert("Failed to send voice note: " + response);
                        }
                    },
                    error: function() {
                        alert("An error occurred while sending the voice note.");
                    }
                });
            }

            // =====================
            // Load Messages
            // =====================
            function loadChatMessages(sender_id, sender_designation) {
                $.ajax({
                    url: 'ajax/load_chat.php',
                    type: 'POST',
                    data: {
                        sender_id: sender_id,
                        sender_designation: sender_designation
                    },
                    success: function(response) {
                        $('#mychatbox').html(response);
                        $('#mychatbox').scrollTop($('#mychatbox')[0].scrollHeight);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error loading chat:", error);
                    }
                });
            }

            // =====================
            // Auto-refresh chat every 3 seconds
            // =====================
            setInterval(function() {
                if (currentChatId && currentChatDesignation) {
                    loadChatMessages(currentChatId, currentChatDesignation);
                }
            }, 3000);

        });
        </script>



        <script src="assets/bundles/cleave-js/dist/cleave.min.js"></script>
        <script src="assets/bundles/cleave-js/dist/addons/cleave-phone.us.js"></script>
        <script src="assets/bundles/jquery-pwstrength/jquery.pwstrength.min.js"></script>
        <script src="assets/bundles/bootstrap-daterangepicker/daterangepicker.js"></script>
        <script src="assets/bundles/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js"></script>
        <script src="assets/bundles/bootstrap-timepicker/js/bootstrap-timepicker.min.js"></script>
        <script src="assets/bundles/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
        <script src="assets/bundles/select2/dist/js/select2.full.min.js"></script>
        <script src="assets/bundles/jquery-selectric/jquery.selectric.min.js"></script>
        <!-- Page Specific JS File -->
        <script src="assets/js/page/forms-advanced-forms.js"></script>
        <!-- General JS Scripts -->
        <script src="assets/js/app.min.js"></script>
        <!-- JS Libraies -->
        <script src="assets/bundles/prism/prism.js"></script>
        <!-- Page Specific JS File -->
        <!-- Template JS File -->
        <!-- <script src="assets/js/scripts.js"></script> -->
        <!-- Custom JS File -->
        <script src="assets/js/custom.js"></script>
        <script>
        "use strict";

        $(window).on("load", function() {
            $(".loader").fadeOut("slow");
        });

        feather.replace();
        // Global
        $(function() {
            let sidebar_nicescroll_opts = {
                    cursoropacitymin: 0,
                    cursoropacitymax: 0.8,
                    zindex: 892,
                },
                now_layout_class = null;

            var sidebar_sticky = function() {
                if ($("body").hasClass("layout-2")) {
                    $("body.layout-2 #sidebar-wrapper").stick_in_parent({
                        parent: $("body"),
                    });
                    $("body.layout-2 #sidebar-wrapper").stick_in_parent({
                        recalc_every: 1
                    });
                }
            };
            sidebar_sticky();

            var sidebar_nicescroll;
            var update_sidebar_nicescroll = function() {
                let a = setInterval(function() {
                    if (sidebar_nicescroll != null) sidebar_nicescroll.resize();
                }, 10);

                setTimeout(function() {
                    clearInterval(a);
                }, 600);
            };

            var sidebar_dropdown = function() {
                if ($(".main-sidebar").length) {
                    $(".main-sidebar").niceScroll(sidebar_nicescroll_opts);
                    sidebar_nicescroll = $(".main-sidebar").getNiceScroll();

                    $(".main-sidebar .sidebar-menu li a.has-dropdown")
                        .off("click")
                        .on("click", function() {
                            var me = $(this);

                            me.parent()
                                .find("> .dropdown-menu")
                                .slideToggle(500, function() {
                                    update_sidebar_nicescroll();
                                    return false;
                                });
                            return false;
                        });
                }
            };
            sidebar_dropdown();

            if ($("#top-5-scroll").length) {
                $("#top-5-scroll")
                    .css({
                        height: 315,
                    })
                    .niceScroll();
            }
            if ($("#scroll-new").length) {
                $("#scroll-new")
                    .css({
                        height: 200,
                    })
                    .niceScroll();
            }

            $(".main-content").css({
                minHeight: $(window).outerHeight() - 95,
            });

            $(".nav-collapse-toggle").click(function() {
                $(this).parent().find(".navbar-nav").toggleClass("show");
                return false;
            });

            $(document).on("click", function(e) {
                $(".nav-collapse .navbar-nav").removeClass("show");
            });

            var toggle_sidebar_mini = function(mini) {
                let body = $("body");

                if (!mini) {
                    body.removeClass("sidebar-mini");
                    $(".main-sidebar").css({
                        overflow: "hidden",
                    });
                    setTimeout(function() {
                        $(".main-sidebar").niceScroll(sidebar_nicescroll_opts);
                        sidebar_nicescroll = $(".main-sidebar").getNiceScroll();
                    }, 500);
                    $(".main-sidebar .sidebar-menu > li > ul .dropdown-title").remove();
                    $(".main-sidebar .sidebar-menu > li > a").removeAttr("data-toggle");
                    $(".main-sidebar .sidebar-menu > li > a").removeAttr(
                        "data-original-title"
                    );
                    $(".main-sidebar .sidebar-menu > li > a").removeAttr("title");
                } else {
                    body.addClass("sidebar-mini");
                    body.removeClass("sidebar-show");
                    sidebar_nicescroll.remove();
                    sidebar_nicescroll = null;
                    $(".main-sidebar .sidebar-menu > li").each(function() {
                        let me = $(this);

                        if (me.find("> .dropdown-menu").length) {
                            me.find("> .dropdown-menu").hide();
                            me.find("> .dropdown-menu").prepend(
                                '<li class="dropdown-title pt-3">' + me.find("> a").text() +
                                "</li>"
                            );
                        } else {
                            me.find("> a").attr("data-toggle", "tooltip");
                            me.find("> a").attr("data-original-title", me.find("> a").text());
                            $("[data-toggle='tooltip']").tooltip({
                                placement: "right",
                            });
                        }
                    });
                }
            };

            // sticky header toggle function
            var toggle_sticky_header = function(sticky) {
                if (!sticky) {
                    $(".main-navbar")[0].classList.remove("sticky");
                } else {
                    $(".main-navbar")[0].classList += " sticky";
                }
            };

            $(".menu-toggle").on("click", function(e) {
                var $this = $(this);
                $this.toggleClass("toggled");
            });

            $.each($(".main-sidebar .sidebar-menu li.active"), function(i, val) {
                var $activeAnchors = $(val).find("a:eq(0)");

                $activeAnchors.addClass("toggled");
                $activeAnchors.next().show();
            });

            $("[data-toggle='sidebar']").click(function() {
                var body = $("body"),
                    w = $(window);

                if (w.outerWidth() <= 1024) {
                    body.removeClass("search-show search-gone");
                    if (body.hasClass("sidebar-gone")) {
                        body.removeClass("sidebar-gone");
                        body.addClass("sidebar-show");
                    } else {
                        body.addClass("sidebar-gone");
                        body.removeClass("sidebar-show");
                    }

                    update_sidebar_nicescroll();
                } else {
                    body.removeClass("search-show search-gone");
                    if (body.hasClass("sidebar-mini")) {
                        toggle_sidebar_mini(false);
                    } else {
                        toggle_sidebar_mini(true);
                    }
                }

                return false;
            });

            var toggleLayout = function() {
                var w = $(window),
                    layout_class = $("body").attr("class") || "",
                    layout_classes =
                    layout_class.trim().length > 0 ? layout_class.split(" ") : "";

                if (layout_classes.length > 0) {
                    layout_classes.forEach(function(item) {
                        if (item.indexOf("layout-") != -1) {
                            now_layout_class = item;
                        }
                    });
                }

                if (w.outerWidth() <= 1024) {
                    if ($("body").hasClass("sidebar-mini")) {
                        toggle_sidebar_mini(false);
                        $(".main-sidebar").niceScroll(sidebar_nicescroll_opts);
                        sidebar_nicescroll = $(".main-sidebar").getNiceScroll();
                    }

                    $("body").addClass("sidebar-gone");
                    $("body").removeClass("layout-2 layout-3 sidebar-mini sidebar-show");
                    $("body")
                        .off("click")
                        .on("click", function(e) {
                            if (
                                $(e.target).hasClass("sidebar-show") ||
                                $(e.target).hasClass("search-show")
                            ) {
                                $("body").removeClass("sidebar-show");
                                $("body").addClass("sidebar-gone");
                                $("body").removeClass("search-show");

                                update_sidebar_nicescroll();
                            }
                        });

                    update_sidebar_nicescroll();

                    if (now_layout_class == "layout-3") {
                        let nav_second_classes = $(".navbar-secondary").attr("class"),
                            nav_second = $(".navbar-secondary");

                        nav_second.attr("data-nav-classes", nav_second_classes);
                        nav_second.removeAttr("class");
                        nav_second.addClass("main-sidebar");

                        let main_sidebar = $(".main-sidebar");
                        main_sidebar
                            .find(".container")
                            .addClass("sidebar-wrapper")
                            .removeClass("container");
                        main_sidebar
                            .find(".navbar-nav")
                            .addClass("sidebar-menu")
                            .removeClass("navbar-nav");
                        main_sidebar.find(".sidebar-menu .nav-item.dropdown.show a").click();
                        main_sidebar.find(".sidebar-brand").remove();
                        main_sidebar.find(".sidebar-menu").before(
                            $("<div>", {
                                class: "sidebar-brand",
                            }).append(
                                $("<a>", {
                                    href: $(".navbar-brand").attr("href"),
                                }).html($(".navbar-brand").html())
                            )
                        );
                        setTimeout(function() {
                            sidebar_nicescroll = main_sidebar.niceScroll(sidebar_nicescroll_opts);
                            sidebar_nicescroll = main_sidebar.getNiceScroll();
                        }, 700);

                        sidebar_dropdown();
                        $(".main-wrapper").removeClass("container");
                    }
                } else {
                    $("body").removeClass("sidebar-gone sidebar-show");
                    if (now_layout_class) $("body").addClass(now_layout_class);

                    let nav_second_classes = $(".main-sidebar").attr("data-nav-classes"),
                        nav_second = $(".main-sidebar");

                    if (
                        now_layout_class == "layout-3" &&
                        nav_second.hasClass("main-sidebar")
                    ) {
                        nav_second.find(".sidebar-menu li a.has-dropdown").off("click");
                        nav_second.find(".sidebar-brand").remove();
                        nav_second.removeAttr("class");
                        nav_second.addClass(nav_second_classes);

                        let main_sidebar = $(".navbar-secondary");
                        main_sidebar
                            .find(".sidebar-wrapper")
                            .addClass("container")
                            .removeClass("sidebar-wrapper");
                        main_sidebar
                            .find(".sidebar-menu")
                            .addClass("navbar-nav")
                            .removeClass("sidebar-menu");
                        main_sidebar.find(".dropdown-menu").hide();
                        main_sidebar.removeAttr("style");
                        main_sidebar.removeAttr("tabindex");
                        main_sidebar.removeAttr("data-nav-classes");
                        $(".main-wrapper").addClass("container");
                        // if(sidebar_nicescroll != null)
                        //   sidebar_nicescroll.remove();
                    } else if (now_layout_class == "layout-2") {
                        $("body").addClass("layout-2");
                    } else {
                        update_sidebar_nicescroll();
                    }
                }
            };
            toggleLayout();
            $(window).resize(toggleLayout);

            $("[data-toggle='search']").click(function() {
                var body = $("body");

                if (body.hasClass("search-gone")) {
                    body.addClass("search-gone");
                    body.removeClass("search-show");
                } else {
                    body.removeClass("search-gone");
                    body.addClass("search-show");
                }
            });

            // tooltip
            $("[data-toggle='tooltip']").tooltip();

            // popover
            $('[data-toggle="popover"]').popover({
                container: "body",
            });

            // Select2
            if (jQuery().select2) {
                $(".select2").select2();
            }

            // Selectric
            if (jQuery().selectric) {
                $(".selectric").selectric({
                    disableOnMobile: false,
                    nativeOnMobile: false,
                });
            }

            $(".notification-toggle").dropdown();
            $(".notification-toggle")
                .parent()
                .on("shown.bs.dropdown", function() {
                    $(".dropdown-list-icons").niceScroll({
                        cursoropacitymin: 0.3,
                        cursoropacitymax: 0.8,
                        cursorwidth: 7,
                    });
                });

            $(".message-toggle").dropdown();
            $(".message-toggle")
                .parent()
                .on("shown.bs.dropdown", function() {
                    $(".dropdown-list-message").niceScroll({
                        cursoropacitymin: 0.3,
                        cursoropacitymax: 0.8,
                        cursorwidth: 7,
                    });
                });

            if (jQuery().summernote) {
                $(".summernote").summernote({
                    dialogsInBody: true,
                    minHeight: 250,
                });
                $(".summernote-simple").summernote({
                    dialogsInBody: true,
                    minHeight: 150,
                    toolbar: [
                        ["style", ["bold", "italic", "underline", "clear"]],
                        ["font", ["strikethrough"]],
                        ["para", ["paragraph"]],
                    ],
                });
            }

            // Dismiss function
            $("[data-dismiss]").each(function() {
                var me = $(this),
                    target = me.data("dismiss");

                me.click(function() {
                    $(target).fadeOut(function() {
                        $(target).remove();
                    });
                    return false;
                });
            });

            // Collapsable
            $("[data-collapse]").each(function() {
                var me = $(this),
                    target = me.data("collapse");

                me.click(function() {
                    $(target).collapse("toggle");
                    $(target).on("shown.bs.collapse", function() {
                        me.html('<i class="fas fa-minus"></i>');
                    });
                    $(target).on("hidden.bs.collapse", function() {
                        me.html('<i class="fas fa-plus"></i>');
                    });
                    return false;
                });
            });

            // Background
            $("[data-background]").each(function() {
                var me = $(this);
                me.css({
                    backgroundImage: "url(" + me.data("background") + ")",
                });
            });

            // Custom Tab
            $("[data-tab]").each(function() {
                var me = $(this);

                me.click(function() {
                    if (!me.hasClass("active")) {
                        var tab_group = $('[data-tab-group="' + me.data("tab") + '"]'),
                            tab_group_active = $(
                                '[data-tab-group="' + me.data("tab") + '"].active'
                            ),
                            target = $(me.attr("href")),
                            links = $('[data-tab="' + me.data("tab") + '"]');

                        links.removeClass("active");
                        me.addClass("active");
                        target.addClass("active");
                        tab_group_active.removeClass("active");
                    }
                    return false;
                });
            });

            // Bootstrap 4 Validation
            $(".needs-validation").submit(function() {
                var form = $(this);
                if (form[0].checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.addClass("was-validated");
            });

            // alert dismissible
            $(".alert-dismissible").each(function() {
                var me = $(this);

                me.find(".close").click(function() {
                    me.alert("close");
                });
            });

            if ($(".main-navbar").length) {}

            // Image cropper
            $("[data-crop-image]").each(function(e) {
                $(this).css({
                    overflow: "hidden",
                    position: "relative",
                    height: $(this).data("crop-image"),
                });
            });

            // Slide Toggle
            $("[data-toggle-slide]").click(function() {
                let target = $(this).data("toggle-slide");

                $(target).slideToggle();
                return false;
            });

            // Dismiss modal
            $("[data-dismiss=modal]").click(function() {
                $(this).closest(".modal").modal("hide");

                return false;
            });

            // Width attribute
            $("[data-width]").each(function() {
                $(this).css({
                    width: $(this).data("width"),
                });
            });

            // Height attribute
            $("[data-height]").each(function() {
                $(this).css({
                    height: $(this).data("height"),
                });
            });

            // Chocolat
            if ($(".chocolat-parent").length && jQuery().Chocolat) {
                $(".chocolat-parent").Chocolat();
            }

            // Sortable card
            if ($(".sortable-card").length && jQuery().sortable) {
                $(".sortable-card").sortable({
                    handle: ".card-header",
                    opacity: 0.8,
                    tolerance: "pointer",
                });
            }

            // Daterangepicker
            if (jQuery().daterangepicker) {
                if ($(".datepicker").length) {
                    $(".datepicker").daterangepicker({
                        locale: {
                            format: "YYYY-MM-DD"
                        },
                        singleDatePicker: true,
                    });
                }
                if ($(".datetimepicker").length) {
                    $(".datetimepicker").daterangepicker({
                        locale: {
                            format: "YYYY-MM-DD hh:mm"
                        },
                        singleDatePicker: true,
                        timePicker: true,
                        timePicker24Hour: true,
                    });
                }
                if ($(".daterange").length) {
                    $(".daterange").daterangepicker({
                        locale: {
                            format: "YYYY-MM-DD"
                        },
                        drops: "down",
                        opens: "right",
                    });
                }
            }

            // Timepicker
            if (jQuery().timepicker && $(".timepicker").length) {
                $(".timepicker").timepicker({
                    icons: {
                        up: "fas fa-chevron-up",
                        down: "fas fa-chevron-down",
                    },
                });
            }

            $("#mini_sidebar_setting").on("change", function() {
                var _val = $(this).is(":checked") ? "checked" : "unchecked";
                if (_val === "checked") {
                    toggle_sidebar_mini(true);
                } else {
                    toggle_sidebar_mini(false);
                }
            });
            $("#sticky_header_setting").on("change", function() {
                if ($(".main-navbar")[0].classList.contains("sticky")) {
                    toggle_sticky_header(false);
                } else {
                    toggle_sticky_header(true);
                }
            });

            $(".theme-setting-toggle").on("click", function() {
                if ($(".theme-setting")[0].classList.contains("active")) {
                    $(".theme-setting")[0].classList.remove("active");
                } else {
                    $(".theme-setting")[0].classList += " active";
                }
            });

            // full screen call

            $(document).on("click", ".fullscreen-btn", function(e) {
                if (
                    !document.fullscreenElement && // alternative standard method
                    !document.mozFullScreenElement &&
                    !document.webkitFullscreenElement &&
                    !document.msFullscreenElement
                ) {
                    // current working methods
                    if (document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen();
                    } else if (document.documentElement.msRequestFullscreen) {
                        document.documentElement.msRequestFullscreen();
                    } else if (document.documentElement.mozRequestFullScreen) {
                        document.documentElement.mozRequestFullScreen();
                    } else if (document.documentElement.webkitRequestFullscreen) {
                        document.documentElement.webkitRequestFullscreen(
                            Element.ALLOW_KEYBOARD_INPUT
                        );
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    }
                }
            });

            // setting sidebar

            $(".settingPanelToggle").on("click", function() {
                    $(".settingSidebar").toggleClass("showSettingPanel");
                }),
                $(".page-wrapper").on("click", function() {
                    $(".settingSidebar").removeClass("showSettingPanel");
                });

            // close right sidebar when click outside
            var mouse_is_inside = false;
            $(".settingSidebar").hover(
                function() {
                    mouse_is_inside = true;
                },
                function() {
                    mouse_is_inside = false;
                }
            );

            $("body").mouseup(function() {
                if (!mouse_is_inside) $(".settingSidebar").removeClass("showSettingPanel");
            });

            $(".settingSidebar-body").niceScroll();

            // theme change event color
            // $(".choose-theme li").on("click", function () {
            //   var bodytag = $("body"),
            //     selectedTheme = $(this),
            //     prevTheme = $(".choose-theme li.active").attr("title");

            //   $(".choose-theme li").removeClass("active"),
            //     selectedTheme.addClass("active");
            //   $(".choose-theme li.active").data("theme");

            //   bodytag.removeClass("theme-" + prevTheme);
            //   bodytag.addClass("theme-" + $(this).attr("title"));
            // });
            $(".choose-theme li").on("click", function() {
                alert("click");
                var selectedTheme = $(this).attr("title"); // get selected theme from title attribute

                // Update UI: remove previous active and add to clicked
                $(".choose-theme li").removeClass("active");
                $(this).addClass("active");

                // Update body class for theme
                $("body")
                    .removeClass(
                        "theme-white theme-cyan theme-black theme-purple theme-orange theme-green theme-red"
                    )
                    .addClass("theme-" + selectedTheme);

                // Prepare data to send to backend (you can add more settings if needed)
                var dataToSend = {
                    color_theme: selectedTheme,
                    // add other settings if you want, e.g. layout, sidebar_color, etc.
                };

                // Send AJAX POST to update the setting in the backend
                $.ajax({
                    url: "ajax/update_user_settings.php", // your PHP update URL
                    method: "POST",
                    data: dataToSend,
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            alert("uplodaet");
                            load_setting();
                        } else {
                            alert("Error updating theme: " + response.message);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("AJAX Error: " + textStatus + " - " + errorThrown);
                    },
                });
            });

            // dark light sidebar button setting
            $(".sidebar-color input:radio").change(function() {
                var sidebarColor = $(this).val();

                $.ajax({
                    url: "ajax/update_user_settings.php", // your PHP update script path
                    method: "POST",
                    dataType: "json",
                    data: {
                        sidebar_color: sidebarColor,
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            load_setting();
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("AJAX error: " + textStatus + " - " + errorThrown);
                    },
                });
            });

            // dark light layout button setting
            $(".layout-color input:radio").change(function() {
                var layout = $(this).val();
                $.ajax({
                    url: "ajax/update_user_settings.php", // your PHP update script
                    method: "POST",
                    dataType: "json",
                    data: {
                        layout: layout,
                        sidebar_color: layout,
                    },
                    success: function(response) {
                        if (response.status === "success") {
                            load_setting();
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("AJAX error: " + textStatus + " - " + errorThrown);
                    },
                });
            });

            // restore default to dark theme
            $(".btn-restore-theme").on("click", function() {
                reset_settings();
            });

            //start up class add

            function load_setting() {
                $.ajax({
                    url: "ajax/get_user_settings.php",
                    method: "GET",
                    dataType: "json",
                    success: function(response) {
                        var theme = response.settings.color_theme;
                        var layout = response.settings.layout;
                        var sidebar_color = response.settings.sidebar_color;
                        var mini_sidebar = response.settings.mini_sidebar;
                        var sticky_header = response.settings.sticky_header;

                        $("body").addClass("theme-" + theme);
                        $(".choose-theme li").removeClass("active");
                        $(".choose-theme li[title='" + theme + "']").addClass("active");
                        if (layout == 1) {
                            $("body").removeClass("dark dark-sidebar");
                            $("body").addClass("light");
                            $("body").addClass("light-sidebar");
                            $(".selectgroup-input-radio[value|='1']").prop("checked", true);
                        } else if (layout == 2) {
                            $("body").removeClass("light light-sidebar");
                            $("body").addClass("dark");
                            $("body").addClass("dark-sidebar");
                            $(".selectgroup-input-radio[value|='2']").prop("checked", true);
                        }
                        if (sidebar_color == 1) {
                            $("body").removeClass("dark-sidebar");
                            jQuery("body").addClass("light-sidebar");
                            $(".select-sidebar[value|='1']").prop("checked", true);
                        } else if (sidebar_color == 2) {
                            $("body").removeClass("light-sidebar");
                            jQuery("body").addClass("dark-sidebar");
                            $(".select-sidebar[value|='2']").prop("checked", true);
                        }
                        if (sticky_header == 1) {
                            $("#sticky_header_setting").prop("checked", true);
                            toggle_sticky_header(true);
                        } else if (sticky_header == 0) {
                            $("#sticky_header_setting").prop("checked", false);
                            toggle_sticky_header(false);
                        }
                        if (mini_sidebar == 1) {
                            toggle_sidebar_mini(true);
                            $("#mini_sidebar_setting").prop("checked", true);
                        } else if (mini_sidebar == 0) {
                            toggle_sidebar_mini(false);
                            $("#mini_sidebar_setting").prop("checked", false);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("AJAX Error: " + textStatus + " - " + errorThrown);
                    },
                });
            }

            function reset_settings() {
                $.ajax({
                    url: "ajax/reset_user_settings.php",
                    method: "POST",
                    dataType: "json",
                    success: function(response) {
                        alert(response.message);
                        if (response.status === "success") {
                            load_setting(); // reload settings after reset
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        alert("AJAX Error: " + textStatus + " - " + errorThrown);
                    },
                });
            }

            load_setting();
            // =====================
            // Search Contacts
            // =====================
            $('.chat-search input').on('keyup', function() {
                let keyword = $(this).val();

                if (keyword.length > 1) {
                    $.ajax({
                        url: 'ajax/search_contacts.php',
                        type: 'POST',
                        data: {
                            keyword: keyword
                        },
                        success: function(response) {
                            $('.chat-list').html(response);
                        }
                    });
                } else {
                    loadChatContacts(); // reload all if search box empty
                }
            });

        });
        </script>


</body>

</html>