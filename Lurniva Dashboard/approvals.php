<?php
include_once "assets/php/header.php";
require_once "sass/db_config.php";
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("approvals");
    if (el) {
        el.classList.add("active");
    }
});
</script>

<div class="main-content container">
    <section class="section">
        <h1 class="text-center mb-4">School Approval Table</h1>

        <!-- 🔍 Search Bar -->
        <div class="mb-3">
            <input type="text" id="searchBox" class="form-control"
                placeholder="Search by School Name or Registration Number">
        </div>

        <!-- Table -->
        <div id="schoolTable"></div>

        <!-- Modal -->
        <div id="paymentModal" class="modal" style="display:none;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content p-3">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h5 class="mb-3">Enter Payment Details</h5>
                    <form id="paymentForm">
                        <input type="hidden" id="schoolId" name="school_id">

                        <!-- Number of Students -->
                        <div class="form-group">
                            <label>Number of Students:</label><input type="number" id="numstu" name="numstu"
                                class="form-control" placeholder="Enter number of students">

                        </div>

                        <!-- Plan Selection -->
                        <div class="form-group">
                            <label>Plan Duration:</label>
                            <select id="planDuration" name="plan_id" class="form-control">
                                <?php
                                $plans = $conn->query("SELECT id, plan_name, duration_days, price FROM student_payment_plans WHERE status='Active'");
                                while ($p = $plans->fetch_assoc()) {
                                    echo "<option value='{$p['id']}' data-price='{$p['price']}'>
                                            {$p['plan_name']} ({$p['duration_days']} days) - PKR {$p['price']} per student
                                          </option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Total Price -->
                        <div class="form-group">
                            <label>Total Price:</label>
                            <input type="text" id="totalPrice" name="total_price" class="form-control" readonly>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadSchools(query = "", page = 1) {
    $.get("ajax/load_schools.php", {
        search: query,
        page: page
    }, function(data) {
        $("#schoolTable").html(data);
    });
}

// Open modal
function openModal(id, schoolName) {
    $("#paymentModal").show();
    $("#schoolId").val(id);
    $("#numstu").val("");
    $("#totalPrice").val("");
}

// Close modal
function closeModal() {
    $("#paymentModal").hide();
}

// Auto calculate total price
function updateTotal() {
    let numStudents = parseInt($("#numstu").val()) || 0;
    let pricePerStudent = parseInt($("#planDuration option:selected").data("price")) || 0;
    let total = numStudents * pricePerStudent;
    $("#totalPrice").val(total > 0 ? "PKR " + total : "");
}
$("#numstu, #planDuration").on("input change", updateTotal);

// Submit Payment Form
$("#paymentForm").on("submit", function(e) {
    e.preventDefault();
    $.post("ajax/approve_school.php", $(this).serialize(), function(response) {
        alert(response.message);
        if (response.success) {
            closeModal();
            loadSchools();
        }
    }, "json");
});

// Search schools
$("#searchBox").on("keyup", function() {
    loadSchools($(this).val(), 1);
});

// Load schools on page load
$(document).ready(function() {
    loadSchools();
});

// Close modal if clicked outside
window.onclick = function(event) {
    let modal = document.getElementById("paymentModal");
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<?php include_once "assets/php/footer.php"; ?>