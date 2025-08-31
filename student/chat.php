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
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("apps");
    if (el) {
        el.classList.add("active");
    }
});
</script>

<head>
    <title>Teacher Dashboard</title>

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
                                src="uploads/profile/<?php echo htmlspecialchars($school['photo']); ?>"
                                class="user-img-radious-style"> <span class="d-sm-none d-lg-inline-block"></span></a>
                        <div class="dropdown-menu dropdown-menu-right pullDown">
                            <div class="dropdown-title">Hello Admin</div>
                            <a href="profile.php" class="dropdown-item has-icon"> <i class="far
										fa-user"></i> Profile
                            </a>
                            <a href="profile.php?#settings" class="dropdown-item has-icon"> <i class="fas fa-cog"></i>
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
            <style>
            #app {
                padding-left: 20px;
                color: #6777ef !important;
                background-color: #f0f3ff;
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
        <script src="assets/js/scripts.js"></script>
        <!-- Custom JS File -->
        <script src="assets/js/custom.js"></script>


</body>

</html>