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
                <input type="hidden" hidden name="receiver_id" id="receiverId" value="">
                <input type="hidden" hidden name="receiver_designation" id="receiverDesignation" value="">

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
setInterval(loadUnreadMessageCount, 6000);
loadUnreadMessageCount();


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


<script src="assets/bundles/apexcharts/apexcharts.min.js"></script>
<!-- Page Specific JS File -->
<script src="assets/js/page/index.js"></script>

</body>

</html>