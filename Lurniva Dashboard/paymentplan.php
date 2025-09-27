<?php include_once "assets/php/header.php"; ?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("paymentplan");
    if (el) {
        el.classList.add("active");
    }
});
</script>

<div class="main-content container">
    <section class="section">
        <h1 class="mb-4">Manage Student Payment Plans</h1>

        <!-- Add / Edit Form -->
        <div class="card mb-4">
            <div class="card-header">Add / Edit Plan</div>
            <div class="card-body">
                <form id="planForm">
                    <input type="hidden" name="id" id="planId">

                    <div class="form-group">
                        <label>Plan Name</label>
                        <input type="text" name="plan_name" id="planName" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="description" class="form-control"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Price (per student)</label>
                        <input type="number" name="price" id="price" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Duration (days)</label>
                        <input type="number" name="duration_days" id="durationDays" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Plan</button>
                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Reset</button>
                </form>
            </div>
        </div>

        <!-- Plans Table -->
        <div class="card">
            <div class="card-header">All Payment Plans</div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Plan Name</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="plansTable"></tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadPlans() {
    $.get("ajax/plans_crud.php", {
        action: "read"
    }, function(data) {
        $("#plansTable").html(data);
    });
}

$("#planForm").on("submit", function(e) {
    e.preventDefault();
    $.post("ajax/plans_crud.php", $(this).serialize() + "&action=save", function(response) {
        alert(response.message);
        if (response.success) {
            resetForm();
            loadPlans();
        }
    }, "json");
});

function editPlan(id) {
    $.get("ajax/plans_crud.php", {
        action: "get",
        id: id
    }, function(data) {
        $("#planId").val(data.id);
        $("#planName").val(data.plan_name);
        $("#description").val(data.description);
        $("#price").val(data.price);
        $("#durationDays").val(data.duration_days);
        $("#status").val(data.status);
    }, "json");
}

function deletePlan(id) {
    if (!confirm("Are you sure you want to delete this plan?")) return;
    $.post("ajax/plans_crud.php", {
        action: "delete",
        id: id
    }, function(response) {
        alert(response.message);
        if (response.success) {
            loadPlans();
        }
    }, "json");
}

function resetForm() {
    $("#planId").val("");
    $("#planForm")[0].reset();
}

// Load on page ready
$(document).ready(function() {
    loadPlans();
});
</script>
<?php include_once "assets/php/footer.php"; ?>