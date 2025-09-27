<?php require_once 'assets/php/header.php'; ?>
<div class="main-content">
  <section class="section">
      
      <form id="settingsForm" class="card p-4">
          <div class="section-body">
            <h2 class="section-title">System Settings</h2>
            <p class="section-lead">Update your school’s appearance and feature settings.</p>

        <!-- Layout -->
        <div class="form-group">
          <label>Layout</label>
          <select name="layout" id="layout" class="form-control">
            <option value="1">Light</option>
            <option value="2">Dark</option>
          </select>
        </div>

        <!-- Sidebar -->
        <div class="form-group">
          <label>Sidebar Color</label>
          <select name="sidebar_color" id="sidebar_color" class="form-control">
            <option value="1">Light</option>
            <option value="2">Dark</option>
          </select>
        </div>

        <!-- Theme -->
        <div class="form-group">
          <label>Theme Color</label>
          <select name="color_theme" id="color_theme" class="form-control">
            <option value="white">White</option>
            <option value="cyan">Cyan</option>
            <option value="black">Black</option>
            <option value="purple">Purple</option>
            <option value="orange">Orange</option>
            <option value="green">Green</option>
            <option value="red">Red</option>
          </select>
        </div>

        <!-- Toggles -->
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="mini_sidebar" name="mini_sidebar">
          <label class="form-check-label">Mini Sidebar</label>
        </div>

        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="sticky_header" name="sticky_header">
          <label class="form-check-label">Sticky Header</label>
        </div>

        <hr>
        <h5>Features</h5>
        <div class="row">
          <div class="col-md-4"><input type="checkbox" name="attendance_enabled" id="attendance_enabled"> Attendance</div>
          <div class="col-md-4"><input type="checkbox" name="behavior_enabled" id="behavior_enabled"> Behavior</div>
          <div class="col-md-4"><input type="checkbox" name="chat_enabled" id="chat_enabled"> Chat</div>
          <div class="col-md-4"><input type="checkbox" name="dairy_enabled" id="dairy_enabled"> Dairy</div>
          <div class="col-md-4"><input type="checkbox" name="exam_enabled" id="exam_enabled"> Exam</div>
          <div class="col-md-4"><input type="checkbox" name="fee_enabled" id="fee_enabled"> Fee</div>
          <div class="col-md-4"><input type="checkbox" name="library_enabled" id="library_enabled"> Library</div>
          <div class="col-md-4"><input type="checkbox" name="meeting_enabled" id="meeting_enabled"> Meeting</div>
          <div class="col-md-4"><input type="checkbox" name="notice_board_enabled" id="notice_board_enabled"> Notice Board</div>
          <div class="col-md-4"><input type="checkbox" name="assign_task_enabled" id="assign_task_enabled"> Assign Task</div>
          <div class="col-md-4"><input type="checkbox" name="tests_assignments_enabled" id="tests_assignments_enabled"> Tests & Assignments</div>
          <div class="col-md-4"><input type="checkbox" name="timetable_enabled" id="timetable_enabled"> Timetable</div>
          <div class="col-md-4"><input type="checkbox" name="transport_enabled" id="transport_enabled"> Transport</div>
        </div>

        <div class="mt-4">
          <button type="submit" class="btn btn-primary">Save Settings</button>
        </div>
      </form>
    </div>
  </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
  // Load settings
  $.get('ajax/get_settings.php', function(res) {
    if(res.status === 'success') {
      let s = res.data;
      $('#layout').val(s.layout);
      $('#sidebar_color').val(s.sidebar_color);
      $('#color_theme').val(s.color_theme);
      $('#mini_sidebar').prop('checked', s.mini_sidebar == 1);
      $('#sticky_header').prop('checked', s.sticky_header == 1);

      $('#attendance_enabled').prop('checked', s.attendance_enabled == 1);
      $('#behavior_enabled').prop('checked', s.behavior_enabled == 1);
      $('#chat_enabled').prop('checked', s.chat_enabled == 1);
      $('#dairy_enabled').prop('checked', s.dairy_enabled == 1);
      $('#exam_enabled').prop('checked', s.exam_enabled == 1);
      $('#fee_enabled').prop('checked', s.fee_enabled == 1);
      $('#library_enabled').prop('checked', s.library_enabled == 1);
      $('#meeting_enabled').prop('checked', s.meeting_enabled == 1);
      $('#notice_board_enabled').prop('checked', s.notice_board_enabled == 1);
      $('#assign_task_enabled').prop('checked', s.assign_task_enabled == 1);
      $('#tests_assignments_enabled').prop('checked', s.tests_assignments_enabled == 1);
      $('#timetable_enabled').prop('checked', s.timetable_enabled == 1);
      $('#transport_enabled').prop('checked', s.transport_enabled == 1);
    } else {
      alert(res.message);
    }
  }, 'json');

  // Save settings
  $('#settingsForm').submit(function(e) {
    e.preventDefault();
    $.post('ajax/update_settings.php', $(this).serialize(), function(res) {
      if(res.status === 'success') {
        alert('Settings saved successfully!');
      } else {
        alert(res.message);
      }
    }, 'json');
  });
});
</script>
<?php require_once 'assets/php/footer.php'; ?>
