<?php require_once 'assets/php/header.php'; 
require 'sass/db_config.php';
$feeTypes = $conn->query("SELECT id, fee_name FROM fee_types WHERE school_id = "
. $_SESSION['admin_id']); ?>
<?php
session_start();
include_once('sass/db_config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: logout.php");
    exit;
}

$school_id = $_SESSION['admin_id']; // adjust if using campus_id or student_id

// Fetch school settings
$sql = "SELECT fee_enabled FROM school_settings WHERE person = 'school' AND person_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$settings = $result->fetch_assoc();
$stmt->close();

// 🚨 If fee is disabled
if (!$settings || $settings['fee_enabled'] == 0) {
    echo "<script>alert('Fee module is disabled by school admin.'); window.location.href='logout.php';</script>";
    exit;
}
?>

<style>
#fee_type {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#fee_type svg {
    color: #6777ef !important;
}

#fee_type span {
    color: #6777ef !important;
}

#fee_type ul {
    display: block !important;
}

#fee_Strutter {
    color: #000;
}
</style>
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Add Class Fee Plan</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Class Fee Plan Details</h4>
                </div>

                <div class="card-body">
                    <form id="classFeePlanForm">
                        <input type="hidden" id="school_id" name="school_id" value="<?= $_SESSION['admin_id'] ?>">

                        <div class="form-group">
                            <label>Class</label>
                            <select class="form-control selectric" id="class_grade" name="class_grade" required>
                                <option value="">Loading classes...</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Frequency</label>
                            <select class="form-control" id="frequency" name="frequency" required>
                                <option value="Monthly">Monthly</option>
                                <option value="Yearly">Yearly</option>
                                <option value="One Time">One Time</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Add Fee Type</label>
                            <div class="input-group">
                                <select class="form-control" id="fee_type_id">
                                    <option value="">Select Fee Type</option>
                                    <?php while ($row = $feeTypes->fetch_assoc()): ?>
                                    <option value="<?= $row['id'] ?>"><?= $row['fee_name'] ?></option>
                                    <?php endwhile; ?>
                                </select>
                                <input type="number" class="form-control" id="rate" placeholder="Enter Rate">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-secondary" id="addFeeType">Add</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Total Amount</label>
                            <input type="number" class="form-control" id="total_amount" name="total_amount" readonly>
                        </div>

                        <div class="form-group">
                            <label>Fee Structure</label>
                            <div class="table-responsive">
                                <table class="table table-bordered" id="feeTypeTable">
                                    <thead>
                                        <tr>
                                            <th>Fee Type</th>
                                            <th>Rate</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Class Fee Plan</button>
                    </form>

                    <div id="response" class="mt-3"></div>
                </div>
            </div>
        </div>
    </section>


</div>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script>
// ✅ Load classes on page load via AJAX
$(document).ready(function() {
    $.ajax({
        url: 'ajax/get_classes.php',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            const $classSelect = $("#class_grade");
            $classSelect.empty().append(`<option value="">Select Class</option>`);
            if (res.status === 'success') {
                res.data.forEach(cls => {
                    $classSelect.append(`<option value="${cls}">${cls}</option>`);
                });
            } else {
                $classSelect.append(`<option value="">No classes found</option>`);
            }
        },
        error: function() {
            alert("Failed to load classes");
        }
    });
});

let feeItems = [];

$("#addFeeType").click(function() {
    const feeTypeId = $("#fee_type_id").val();
    const feeTypeText = $("#fee_type_id option:selected").text();
    const rate = parseFloat($("#rate").val());

    if (!feeTypeId || !rate || rate <= 0) {
        alert("Please select fee type and enter valid rate");
        return;
    }

    feeItems.push({
        fee_type_id: feeTypeId,
        fee_name: feeTypeText,
        rate: rate
    });

    renderTable();
    updateTotal();
    $("#fee_type_id").val("");
    $("#rate").val("");
});

function renderTable() {
    const tbody = $("#feeTypeTable tbody");
    tbody.empty();
    feeItems.forEach((item, index) => {
        tbody.append(`<tr>
          <td>${item.fee_name}</td>
          <td>${item.rate}</td>
          <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">Delete</button>
          </td>
        </tr>`);
    });
}

function updateTotal() {
    const total = feeItems.reduce((sum, item) => sum + item.rate, 0);
    $("#total_amount").val(total);
}

function removeItem(index) {
    feeItems.splice(index, 1);
    renderTable();
    updateTotal();
}

$("#classFeePlanForm").on("submit", function(e) {
    e.preventDefault();

    if (feeItems.length === 0) {
        alert("Please add at least one fee type.");
        return;
    }

    const formData = {
        school_id: <?php echo $_SESSION['admin_id']; ?>,
        class_grade: $("#class_grade").val(),
        frequency: $("#frequency").val(),
        status: $("#status").val(),
        total_amount: $("#total_amount").val(),
        fee_items: feeItems
    };

    $.ajax({
        url: "ajax/insert_fee_structure.php",
        type: "POST",
        data: JSON.stringify(formData),
        contentType: "application/json",
        success: function(response) {
            $("#response").html(
                `<div class="alert alert-${response.status === 'success' ? 'success' : 'danger'}">${response.message}</div>`
            );
            if (response.status === 'success') {
                $("#classFeePlanForm")[0].reset();
                feeItems = [];
                renderTable();
                updateTotal();
            }
        },
        error: function() {
            $("#response").html('<div class="alert alert-danger">Error saving data</div>');
        }
    });
});
</script>
<script>
function toggleMeetingType() {
    const type = document.getElementById("meetingType").value;
    const agenda = document.getElementById("agenda");
    if (type === "scheduled") {
        agenda.placeholder = "Include the agenda and the meeting link if applicable.";
    } else {
        agenda.placeholder = "Include the agenda and the room number or location.";
    }
}
</script>

<?php require_once 'assets/php/footer.php'; ?>