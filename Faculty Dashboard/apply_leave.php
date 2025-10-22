<?php require_once 'assets/php/header.php'; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("apps");
    if (el) {
        el.classList.add("active");
        el.classList.add("apply_leave");
    }
});
</script>

<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Leave Request</h2>
        </div>

        <div class="section-body">
            <!-- Leave Request Form -->
            <form id="leaveForm">
                <div class="mb-3">
                    <label>Leave Type</label>
                    <input type="text" name="leave_type" class="form-control" placeholder="e.g., Sick Leave" required>
                </div>

                <div class="mb-3">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>Reason</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason"
                        required></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Submit</button>
            </form>

            <div id="result" class="mt-3"></div>
            <hr>

            <h3>All Leave Requests</h3>
            <div id="allLeaves"></div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // Load leaves when page loads
    loadLeaves();

    // Submit form (insert/update)
    $("#leaveForm").on("submit", function(e) {
        e.preventDefault();

        var updateId = $("button[type=submit]").data("update-id");
        var action = updateId ? "update" : "insert";

        $.ajax({
            url: "ajax/leave_crud.php",
            method: "POST",
            data: $(this).serialize() + "&action=" + action + (updateId ? "&id=" + updateId :
                ""),
            success: function(data) {
                $("#result").html(data);
                $("#leaveForm")[0].reset();
                $("button[type=submit]").text("Submit Leave").removeData("update-id");
                loadLeaves();
            }
        });
    });

    // Load all leaves
    function loadLeaves() {
        $.post("ajax/leave_crud.php", {
            action: "getAll"
        }, function(data) {
            $("#allLeaves").html(data);
        });
    }

    // Edit leave
    $(document).on("click", ".editLeave", function() {
        var id = $(this).data("id");
        $.post("ajax/leave_crud.php", {
            id: id,
            action: "getOne"
        }, function(data) {
            var l = JSON.parse(data);
            $("input[name=leave_type]").val(l.leave_type);
            $("input[name=start_date]").val(l.start_date);
            $("input[name=end_date]").val(l.end_date);
            $("textarea[name=reason]").val(l.reason);
            $("button[type=submit]").text("Update Leave").data("update-id", id);
        });
    });

    // Delete leave
    $(document).on("click", ".deleteLeave", function() {
        var id = $(this).data("id");
        if (confirm("Are you sure you want to delete this leave?")) {
            $.post("ajax/leave_crud.php", {
                id: id,
                action: "delete"
            }, function(data) {
                $("#result").html(data);
                loadLeaves();
            });
        }
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>