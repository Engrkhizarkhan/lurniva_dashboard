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
                        <input type="radio" name="value" value="1" class="selectgroup-input-radio select-layout">
                        <span class="selectgroup-button">Light</span>
                    </label>
                    <label class="selectgroup-item">
                        <input type="radio" name="value" value="2" class="selectgroup-input-radio select-layout">
                        <span class="selectgroup-button">Dark</span>
                    </label>
                </div>
            </div>
            <div class="p-15 border-bottom">
                <h6 class="font-medium m-b-10">Sidebar Color</h6>
                <div class="selectgroup selectgroup-pills sidebar-color">
                    <label class="selectgroup-item">
                        <input type="radio" name="icon-input" value="1" class="selectgroup-input select-sidebar">
                        <span class="selectgroup-button selectgroup-button-icon" data-toggle="tooltip"
                            data-original-title="Light Sidebar"><i class="fas fa-sun"></i></span>
                    </label>
                    <label class="selectgroup-item">
                        <input type="radio" name="icon-input" value="2" class="selectgroup-input select-sidebar"
                            checked>
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
<!-- Floating Chat Box -->
<div id="floatingChatBox" style="display: none;">
    <div class="card" style="height: 100%; border-radius: 0;">
        <!-- Chat Header -->
        <div class="chat-header" style="padding: 15px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img id="chatImage" src="assets/img/default-avatar.png" alt="avatar"
                    style="height: 45px; width: 45px; border-radius: 50%;">
                <div class="chat-about">
                    <div class="chat-with" id="chatWithName" style="font-size: 14px;">Chat with ...</div>
                    <div class="chat-num-messages" id="chatStatus" style="font-size: 12px;">Active Now</div>
                </div>
            </div>
            <button onclick="$('#floatingChatBox').hide();">&times;</button>
        </div>
        <!-- Chat Body -->
        <div class="chat-body" id="chatContent">
            <!-- Messages load here dynamically -->
        </div>
        <!-- Chat Footer -->
        <div class="chat-footer chat-form">
            <form id="chatForm" enctype="multipart/form-data">
                <input type="hidden" name="receiver_id" id="receiverId" value="">
                <input type="hidden" name="receiver_designation" id="receiverDesignation" value="">

                <input type="text" id="chatInput" name="message" placeholder="Type your message...">
                <input type="file" id="fileUpload" name="file_attachment" style="display:none;">
                <button type="button" id="attachBtn">📎</button>

                <button type="button" id="startRecordingBtn"><i class="mic-icon">🎤</i></button>
                <button type="submit">Send</button>
            </form>

            <div id="chatBox"></div>

        </div>
    </div>
</div>
<style>
.recording {
    color: red !important;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0% {
        opacity: 1;
    }

    50% {
        opacity: 0.4;
    }

    100% {
        opacity: 1;
    }
}

#floatingChatBox {
    position: fixed;
    bottom: 0;
    right: 20px;
    width: 350px;
    height: 450px;
    background: #fff;
    border-radius: 10px 10px 0 0;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    border: 1px solid #ccc;
}

.chat-header {
    background: #6777ef;
    color: #fff;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-body {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
    background-color: #f5f5f5;
}

.chat-footer {
    border-top: 1px solid #ccc;
    padding: 10px;
    background: #fff;
}

.chat-footer input {
    flex: 1;
    border: none;
    padding: 10px;
    outline: none;
    background: #f1f1f1;
    border-radius: 5px;
}

.chat-footer button {
    border: none;
    background: #007bff;
    color: #fff;
    padding: 10px 12px;
    border-radius: 5px;
    margin-left: 5px;
}
</style>

<footer class="main-footer">


    <footer class="footer d-flex justify-content-center align-items-center" style="height: 20px;">
        <div class="text-center">
            All Rights Reserved to <strong>Lurniva</strong> @developed by <strong>SUIT Incubation</strong>
        </div>
    </footer>


    <div class="footer-right">
    </div>
</footer>
</div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
let mediaRecorder;
let audioChunks = [];
let currentSenderId = null;
let currentSenderDesignation = null;

// =====================
// Open Chat Box
// =====================
$(document).on('click', '.open-chat', function(e) {
    e.preventDefault();

    currentSenderId = $(this).data('sender-id');
    currentSenderDesignation = $(this).data('sender-designation');
    const senderName = $(this).data('sender-name');
    const senderImage = $(this).data('sender-image') || 'assets/img/default-avatar.png';
    const activeStatus = $(this).data('active') || 'Active now';

    $('#chatWithName').text('Chat with ' + senderName);
    $('#chatImage').attr('src', senderImage);
    $('#chatStatus').text(activeStatus);
    $('#receiverId').val(currentSenderId);
    $('#receiverDesignation').val(currentSenderDesignation);

    $('#floatingChatBox').show();
    $('#chatInput').val('');
    $('#chatContent').html('<p>Loading messages...</p>');

    $.post('ajax/update_message_status.php', {
        sender_id: currentSenderId,
        sender_designation: currentSenderDesignation
    });

    loadChat(currentSenderId, currentSenderDesignation);
});

// =====================
// Load Chat History
// =====================
function loadChat(senderId, senderDesignation) {
    $.ajax({
        url: 'ajax/load_chat.php',
        method: 'POST',
        data: {
            sender_id: senderId,
            sender_designation: senderDesignation
        },
        success: function(response) {
            $('#chatContent').html(response);
            $('#chatContent').scrollTop($('#chatContent')[0].scrollHeight);
        }
    });
}

// =====================
// Handle File Attachment
// =====================
$('#fileUpload').on('change', function() {
    const file = this.files[0];
    if (file) {
        $('#chatInput').val(file.name);
    } else {
        $('#chatInput').val('');
    }
});

// =====================
// Submit Chat Form (text + file)
// =====================
$('#chatForm').on('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const message = formData.get('message').trim();
    const file = $('#fileUpload')[0].files[0];

    // Prevent empty send
    if (!message && !file) {
        alert("Please enter a message or attach a file.");
        return;
    }

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
                loadChat(currentSenderId, currentSenderDesignation);
                loadChatMessages(sender_id, sender_designation);
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
$('#startRecordingBtn').on('click', function() {
    if (!mediaRecorder || mediaRecorder.state === 'inactive') {
        startRecording();
    } else if (mediaRecorder.state === 'recording') {
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
    formData.append('receiver_id', $('#receiverId').val());
    formData.append('receiver_designation', $('#receiverDesignation').val());

    $.ajax({
        url: 'ajax/send_message.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.trim() === 'success') {
                loadChatMessages(sender_id, sender_designation);
                loadChat(currentSenderId, currentSenderDesignation);
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
// Close Chat Box
// =====================
$('#closeChatBox').on('click', function() {
    $('#floatingChatBox').hide();
});

// =====================
// Unread Message Count
// =====================
function loadUnreadMessageCount() {
    $.ajax({
        url: "ajax/get_unread_count.php",
        method: "GET",
        success: function(data) {
            $('.headerBadge1').text(data);
        },
        error: function() {
            $('.headerBadge1').text('0');
        }
    });
}
setInterval(loadUnreadMessageCount, 60000);
loadUnreadMessageCount();

// =====================
// Load Messages Dropdown
// =====================
$(document).ready(function() {
    function loadMessages() {
        $.ajax({
            url: 'ajax/fetch_messages.php',
            method: 'GET',
            success: function(response) {
                $('.dropdown-list-content.dropdown-list-message').html(response);
            }
        });
    }

    $('.message-toggle').on('click', function() {
        loadMessages();
    });
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script src="assets/bundles/apexcharts/apexcharts.min.js"></script>
<!-- Page Specific JS File -->
<script src="assets/js/page/index.js"></script>
<!-- JS Libraies -->
<script src="assets/bundles/amcharts4/core.js"></script>
<script src="assets/bundles/amcharts4/charts.js"></script>
<script src="assets/bundles/amcharts4/animated.js"></script>
<script src="assets/bundles/amcharts4/worldLow.js"></script>
<script src="assets/bundles/amcharts4/maps.js"></script>
<!-- Page Specific JS File -->
<!-- <script src="assets/js/page/chart-amchart.js"></script> -->
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
                        '<li class="dropdown-title pt-3">' + me.find("> a").text() + "</li>"
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
});
</script>
</body>

</html>