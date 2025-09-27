<?php require_once 'assets/php/header.php'; ?>
<div class="main-content">
  <section class="section">
    <div class="section-header">
      <h2>Attendance Report</h2>
    </div>

    <div class="section-body">
      <div class="form-group">
        <label for="classSelect">Select Class:</label>
        <select id="classSelect" class="form-control"></select>
      </div>

      <div class="form-group">
        <label for="studentSelect">Select Student (optional):</label>
        <select id="studentSelect" class="form-control">
          <option value="">-- All Students --</option>
        </select>
      </div>

      <div id="attendanceResult"></div>
    </div>
  </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function(){
    // Load classes
    $.get("ajax/get_classes.php", function(res){
        $("#classSelect").html(res);
    });

    // When class selected, load students
    $("#classSelect").change(function(){
        let class_id = $(this).val();
        alert(class_id)
        if(class_id){
            $.get("ajax/get_class_students_list.php",{class_id}, function(res){
                $("#studentSelect").html('<option value="">-- All Students --</option>'+res);
            });

            // Load class-wise attendance
            loadAttendance(class_id,"");
        }
    });

    // When student selected
    $("#studentSelect").change(function(){
        let class_id = $("#classSelect").val();
        let student_id = $(this).val();
        loadAttendance(class_id, student_id);
    });

    function loadAttendance(class_id, student_id){
        $.get("ajax/get_attendance_report.php",{class_id, student_id}, function(res){
            $("#attendanceResult").html(res);
        });
    }
});
</script>
<?php require_once 'assets/php/footer.php'; ?>
