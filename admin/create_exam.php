<?php require_once 'assets/php/header.php'; ?>
<style>
#timetable {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#timetable ul {
    display: block !important;
}

#createCE {
    color: #000;
}
</style>
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h4>Manage Exams</h4>
        </div>

        <!-- Form -->
        <form id="examForm">
            <input type="hidden" name="id" id="exam_id">
            <div class="form-group">
                <label>Exam Name</label>
                <input type="text" name="exam_name" id="exam_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Total Marks</label>
                <input type="number" name="total_marks" id="total_marks" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Save</button>
        </form>

        <hr>

        <!-- Table -->
        <h5>Exam List</h5>
        <table class="table table-bordered" id="examTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Exam Name</th>
                    <th>Total Marks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    // Load exams
    function loadExams() {
        $.get("ajax/exams_crud.php", {
            action: "read"
        }, function(data) {
            $("#examTable tbody").html(data);
        });
    }
    loadExams();

    // Save exam (insert or update)
    $("#examForm").submit(function(e) {
        e.preventDefault();
        $.post("ajax/exams_crud.php", $(this).serialize() + "&action=save", function(res) {
            alert(res.message);
            if (res.status == "success") {
                $("#examForm")[0].reset();
                $("#exam_id").val("");
                loadExams();
            }
        }, "json");
    });

    // Edit exam
    $(document).on("click", ".editBtn", function() {
        let id = $(this).data("id");
        $.get("ajax/exams_crud.php", {
            action: "get",
            id: id
        }, function(res) {
            if (res.status == "success") {
                $("#exam_id").val(res.data.id);
                $("#exam_name").val(res.data.exam_name);
                $("#total_marks").val(res.data.total_marks);
            }
        }, "json");
    });

    // Delete exam
    $(document).on("click", ".deleteBtn", function() {
        if (confirm("Delete this exam?")) {
            let id = $(this).data("id");
            $.post("ajax/exams_crud.php", {
                action: "delete",
                id: id
            }, function(res) {
                alert(res.message);
                if (res.status == "success") loadExams();
            }, "json");
        }
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>