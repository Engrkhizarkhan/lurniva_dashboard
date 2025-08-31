<?php require_once 'assets/php/header.php'; ?>
<style>
#dairyFormContainer {
    padding-left: 20px;
    position: relative;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#dairyFormContainer svg {
    color: #6777ef !important;
}

#dairyFormContainer span {
    color: #6777ef !important;
}
</style>
<div class="main-content">
    <section class="section">

        <!-- Diary Form -->
        <div id="dairyFormContainer">
            <h2>Diary Writing</h2>
            <form id="dairyForm">
                <div class="mb-3">
                    <label>Class</label>
                    <select name="class_id" id="class_id" class="form-control" required>
                        <option value="">Select Class</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Subject</label>
                    <select name="subject" id="subject_id" class="form-control" required>
                        <option value="">Select Subject</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Teacher</label>
                    <input type="text" name="teacher_name" id="teacher_name" class="form-control" readonly
                        value="<?php echo $_SESSION['admin_name'] ?? ''; ?>">
                </div>

                <div class="mb-3">
                    <label>Topic</label>
                    <input type="text" name="topic" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4" required></textarea>
                </div>

                <div class="mb-3">
                    <label>Student Selection</label>
                    <select name="student_option" id="student_option" class="form-control">
                        <option value="all">All Students</option>
                        <option value="specific">Specific Students</option>
                    </select>
                </div>

                <div class="mb-3" id="studentSelectContainer" style="display:none;">
                    <label>Select Student</label>
                    <select id="student_select" class="form-control">
                        <option value="">Select Student</option>
                    </select>
                </div>

                <div class="mb-3" id="studentTableContainer" style="display:none;">
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

                <div class="mb-3">
                    <label>Attachment (Optional)</label>
                    <input type="file" name="file" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Deadline</label>
                    <input type="date" name="deadline" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Parent Acceptation Required?</label>
                    <select name="parent_approval" class="form-control" required>
                        <option value="yes">Yes</option>
                        <option value="no">No</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Submit Diary</button>
            </form>
        </div>

        <!-- Diary List -->
        <div id="allDiariesContainer">
            <h3>All Diary Entries</h3>
            <div id="allDiaries"></div>
        </div>

        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script>
        $(document).ready(function() {

            // Load teacher classes
            function loadClasses(selectedClass = '') {
                $.post('ajax/teacher_classes.php', {}, function(data) {
                    $('#class_id').html('<option value="">Select Class</option>' + data);
                    if (selectedClass) $('#class_id').val(selectedClass).trigger('change');
                });
            }
            loadClasses();
            // Load subjects when class changes
            function loadSubjects(class_id, selectedSubject = '') {
                if (!class_id) {
                    $('#subject_id').html('<option value="">Select Subject</option>');
                    return;
                }
                $.post('ajax/get_class_subjects.php', {
                    class_id: class_id
                }, function(data) {
                    $('#subject_id').html('<option value="">Select Subject</option>' + data);
                    if (selectedSubject) $('#subject_id').val(selectedSubject);
                });
            }

            // Load students for specific selection
            function loadClassStudents(class_id) {
                if (!class_id) return;
                $.post('ajax/class_students.php', {
                    class_id: class_id
                }, function(data) {
                    let students = JSON.parse(data);
                    let options = '<option value="">Select Student</option>';
                    students.forEach(function(s) {
                        options +=
                            `<option value="${s.id}" data-name="${s.full_name}" data-roll="${s.roll_number}">${s.full_name} (${s.roll_number})</option>`;
                    });
                    $('#student_select').html(options);
                });
            }

            // Show/hide student selection
            $('#student_option').change(function() {
                if ($(this).val() == 'specific') {
                    $('#studentSelectContainer, #studentTableContainer').show();
                    loadClassStudents($('#class_id').val());
                } else {
                    $('#studentSelectContainer, #studentTableContainer').hide();
                    $('#studentTable tbody').empty();
                }
            });

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

            // Remove student from table
            $(document).on('click', '.removeStudent', function() {
                $(this).closest('tr').remove();
            });

            // Load all diaries
            function loadAllDiaries() {
                $.post('ajax/dairy_crud.php', {
                    action: 'getAll'
                }, function(data) {
                    $('#allDiaries').html(data);
                });
            }
            loadAllDiaries();

            // Submit or update diary
            $('#dairyForm').submit(function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let updateId = $('button[type=submit]').data('update-id');
                if (updateId) formData.append('id', updateId);

                if ($('#student_option').val() == 'specific') {
                    let students = [];
                    $('#studentTable tbody tr').each(function() {
                        students.push($(this).data('id'));
                    });
                    formData.append('students', JSON.stringify(students));
                }

                formData.append('action', updateId ? 'update' : 'insert');

                $.ajax({
                    url: 'ajax/dairy_crud.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(resp) {
                        alert(resp);
                        $('#dairyForm')[0].reset();
                        $('#studentTable tbody').empty();
                        $('#studentSelectContainer, #studentTableContainer').hide();
                        $('button[type=submit]').text('Submit Diary').removeData(
                            'update-id');
                        loadAllDiaries();
                    }
                });
            });

            // Edit diary
            $(document).on('click', '.editDiary', function() {
                let id = $(this).data('id');
                $.post('ajax/dairy_crud.php', {
                    id: id,
                    action: 'getOne'
                }, function(data) {
                    let diary = JSON.parse(data);

                    // Set class and subject after AJAX
                    loadClasses(diary.class_meta_id);
                    setTimeout(() => {
                        loadSubjects(diary.class_meta_id, diary.subject);
                    }, 300);

                    $('input[name=topic]').val(diary.topic);
                    $('textarea[name=description]').val(diary.description);
                    $('input[name=deadline]').val(diary.deadline);
                    $('select[name=parent_approval]').val(diary.parent_approval_required);
                    $('button[type=submit]').text('Update Diary').data('update-id', diary.id);

                    if (diary.student_option == 'specific' && diary.students.length) {
                        $('#student_option').val('specific').trigger('change');
                        setTimeout(() => {
                            diary.students.forEach(sid => {
                                let student = $(
                                    '#student_select option[value="' + sid +
                                    '"]');
                                if (student.length) {
                                    student.prop('selected', true);
                                    $('#student_select').trigger('change');
                                }
                            });
                        }, 500);
                    } else {
                        $('#student_option').val('all').trigger('change');
                    }
                });
            });

            // Delete diary
            $(document).on('click', '.deleteDiary', function() {
                let id = $(this).data('id');
                if (!confirm('Are you sure?')) return;
                $.post('ajax/dairy_crud.php', {
                    id: id,
                    action: 'delete'
                }, function(resp) {
                    alert(resp);
                    loadAllDiaries();
                });
            });

        });
        </script>

    </section>
</div>

<?php require_once 'assets/php/footer.php'; ?>