
<?php require_once 'assets/php/header.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("behaviorFormContainer");
    if (el) {
        el.classList.add("active");
    }
});
</script>
<div class="main-content">
    <section class="section">

        <!-- Behavior Form -->
        <div id="behaviorFormContainer">
            <h2>Student Behavior Report</h2>
            <form id="behaviorForm" enctype="multipart/form-data">
                
                <!-- Class -->
                <div class="mb-3">
                    <label>Class</label>
                    <select name="class_id" id="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                    </select>
                </div>

                <!-- Teacher -->
                <div class="mb-3">
                    <label>Teacher</label>
                    <input type="text" name="teacher_name" id="teacher_name" class="form-control" readonly
                        value="<?php echo $_SESSION['admin_name'] ?? ''; ?>">
                </div>

                <!-- Topic -->
                <div class="mb-3">
                    <label>Topic</label>
                    <input type="text" name="topic" class="form-control" required>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>

                <!-- Student Selection -->
                <div class="mb-3">
                    <label>Select Students</label>
                    <select id="student_select" class="form-control">
                        <option value="">Select Student</option>
                    </select>
                </div>

                <!-- Selected Students Table -->
                <div class="mb-3" id="studentTableContainer">
                    <table class="table table-bordered" id="studentTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Roll No</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- Attachment -->
                <div class="mb-3">
                    <label>Attachment (Optional)</label>
                    <input type="file" name="file" class="form-control">
                </div>

                <!-- Deadline -->
                <div class="mb-3">
                    <label>Deadline</label>
                    <input type="date" name="deadline" class="form-control" required>
                </div>

                <!-- Parent Approval -->
                <div class="mb-3">
                    <label>Parent Acceptation Required?</label>
                    <select name="parent_approval" class="form-control" required>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Submit Behavior Report</button>
            </form>
        </div>

        <!-- Behavior List -->
        <div id="allBehaviorContainer" class="mt-5">
            <h3>All Behavior Reports</h3>
            <div id="allBehavior"></div>
        </div>

    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {

    // Load Classes
    function loadClasses(selectedClass = '') {
        $.post('ajax/teacher_classes.php', {}, function(data) {
            $('#class_id').html('<option value="">Select Class</option>' + data);
            if (selectedClass) $('#class_id').val(selectedClass).trigger('change');
        });
    }
    loadClasses();

    // Load Students when class changes
    function loadClassStudents(class_id, selectedStudents = []) {
        if (!class_id) return;
        $.post('ajax/class_students.php', { class_id: class_id }, function(data) {
            let students = JSON.parse(data);
            let options = '<option value="">Select Student</option>';
            students.forEach(function(s) {
                options += `<option value="${s.id}" data-name="${s.full_name}" data-roll="${s.roll_number}">
                                ${s.full_name} (${s.roll_number})
                            </option>`;
            });
            $('#student_select').html(options);

            // ✅ Pre-select students if updating
            selectedStudents.forEach(function(sid) {
                let student = $('#student_select option[value="' + sid + '"]');
                if (student.length) {
                    student.prop('selected', true);
                    $('#student_select').trigger('change');
                }
            });
        });
    }

    // Add student to table
    $('#student_select').change(function() {
        let selected = $(this).find(':selected');
        let id = selected.val();
        let name = selected.data('name');
        let roll = selected.data('roll');

        if (!id || $('#studentTable tbody tr[data-id="' + id + '"]').length) return;

        let row = `<tr data-id="${id}">
            <td>${name}</td>
            <td>${roll}</td>
            <td><button type="button" class="btn btn-danger btn-sm removeStudent">Delete</button></td>
        </tr>`;
        $('#studentTable tbody').append(row);
    });

    // Remove student
    $(document).on('click', '.removeStudent', function() {
        $(this).closest('tr').remove();
    });

    // Load all behavior reports
    function loadAllBehavior() {
        $.post('ajax/behavior_crud.php', { action: 'getAll' }, function(data) {
            $('#allBehavior').html(data);
        });
    }
    loadAllBehavior();

    // Submit form (insert / update)
    $('#behaviorForm').submit(function(e) {
        e.preventDefault();
        let formData = new FormData(this);

        let students = [];
        $('#studentTable tbody tr').each(function() {
            students.push($(this).data('id'));
        });
        formData.append('students', JSON.stringify(students));

        let updateId = $('button[type=submit]').data('update-id');
        formData.append('action', updateId ? 'update' : 'insert');
        if (updateId) formData.append('id', updateId);

        $.ajax({
            url: 'ajax/behavior_crud.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(resp) {
                alert(resp);
                $('#behaviorForm')[0].reset();
                $('#studentTable tbody').empty();
                $('button[type=submit]').text('Submit Behavior Report').removeData('update-id');
                loadAllBehavior();
            }
        });
    });

    // ✅ Edit Behavior
    $(document).on('click', '.editBehavior', function() {
        let id = $(this).data('id');
        $.post('ajax/behavior_crud.php', { action: 'getOne', id: id }, function(res) {
            let d = JSON.parse(res);

            // Fill form fields
            loadClasses(d.class_id); 
            $('input[name=topic]').val(d.topic);
            $('textarea[name=description]').val(d.description);
            $('input[name=deadline]').val(d.deadline);
            $('select[name=parent_approval]').val(d.parent_approval);

            // ✅ Load students for this class & select this student
            setTimeout(() => {
                loadClassStudents(d.class_id, [d.student_id]);
            }, 300);

            // Change form to update mode
            $('button[type=submit]').text('Update Behavior Report').data('update-id', d.id);
        });
    });

    // ✅ Delete Behavior
    $(document).on('click', '.deleteBehavior', function() {
        if (!confirm("Are you sure?")) return;
        let id = $(this).data('id');
        $.post('ajax/behavior_crud.php', { action: 'delete', id: id }, function(resp) {
            alert(resp);
            loadAllBehavior();
        });
    });

});

</script>
<?php require_once 'assets/php/footer.php'; ?>
