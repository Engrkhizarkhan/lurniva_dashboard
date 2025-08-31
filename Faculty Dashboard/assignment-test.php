<?php require_once 'assets/php/header.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("test");
    if (el) {
        el.classList.add("active");
    }
});
</script>


<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Test / Assignment</h2>
        </div>

        <div class="section-body">
            <!-- Test / Assignment Form -->
            <form id="testAssignmentForm" enctype="multipart/form-data">
                <input type="hidden" name="teacher_id" value="<?php echo $_SESSION['admin_id']; ?>">

                <div class="mb-3">
                    <label>Type</label>
                    <select name="type" class="form-control" required>
                        <option value="Assignment">Assignment</option>
                        <option value="Test">Test</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Class</label>
                    <select name="class_id" id="classSelect" class="form-control" required>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Subject</label>
                    <select name="subject_id" id="subjectSelect" class="form-control" required>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" placeholder="Enter title" required>
                </div>

                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Enter details"
                        required></textarea>
                </div>

                <div class="mb-3">
                    <label>Due Date</label>
                    <input type="date" name="due_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Total Marks</label>
                    <input type="number" name="total_marks" class="form-control" placeholder="e.g. 100" required>
                </div>

                <div class="mb-3">
                    <label>Attachment (Optional)</label>
                    <input type="file" name="attachment" class="form-control">
                </div>

                <div class="mb-3">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="Active">Active</option>
                        <option value="Draft">Draft</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Save</button>
            </form>

            <div id="result" class="mt-3"></div>
            <hr>
            <h3>All Tests / Assignments</h3>
            <div id="allTasks"></div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {

    var teacherId = $("input[name=teacher_id]").val();

    // Load classes for teacher
    function loadClasses() {
        $.post("ajax/teacher_classes.php", {
            teacher_id: teacherId
        }, function(data) {
            $("#classSelect").html('<option value="">Select Class</option>' + data);
        });
    }

    loadClasses();

    // When class changes, load subjects
    $("#classSelect").change(function() {
        var classId = $(this).val();
        if (classId) {
            $.post("ajax/get_class_subjects.php", {
                teacher_id: teacherId,
                class_id: classId
            }, function(data) {
                $("#subjectSelect").html('<option value="">Select Subject</option>' + data);
            });
        } else {
            $("#subjectSelect").html('<option value="">Select Subject</option>');
        }
    });

    // Submit Test/Assignment form
    $("#testAssignmentForm").on("submit", function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var updateId = $("button[type=submit]").data("update-id");
        formData.append("action", updateId ? "update" : "insert");
        if (updateId) formData.append("id", updateId);

        $.ajax({
            url: "ajax/test_assignment_crud.php",
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function(data) {
                $("#result").html(data);

                $("#testAssignmentForm")[0].reset();
                $("button[type=submit]").text("Save").removeData("update-id");
                loadTasks();
            }
        });
    });

    // Load all tasks
    function loadTasks() {
        $.post("ajax/test_assignment_crud.php", {
            action: "getAll"
        }, function(data) {
            $("#allTasks").html(data);
        });
    }

    loadTasks();

    // Edit Task
    $(document).on("click", ".editAssignment", function() {
        var id = $(this).data("id");
        $.post("ajax/test_assignment_crud.php", {
            id: id,
            action: "getOne"
        }, function(data) {
            var t = JSON.parse(data);
            $("select[name=type]").val(t.type);
            $("select[name=class_id]").val(t.class_meta_id).change();
            setTimeout(function() {
                $("select[name=subject_id]").val(t.subject);
            }, 300);
            $("input[name=title]").val(t.title);
            $("textarea[name=description]").val(t.description);
            $("input[name=due_date]").val(t.due_date);
            $("input[name=total_marks]").val(t.total_marks);
            $("select[name=status]").val(t.status);
            $("button[type=submit]").text("Update").data("update-id", id);
        });
    });

    // Delete Task
    $(document).on("click", ".deleteTask", function() {
        var id = $(this).data("id");
        if (confirm("Are you sure to delete?")) {
            $.post("ajax/test_assignment_crud.php", {
                id: id,
                action: "delete"
            }, function(data) {
                $("#result").html(data);
                loadTasks();
            });
        }
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>