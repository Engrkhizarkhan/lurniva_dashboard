<?php require_once 'assets/php/header.php'; 
include_once('sass/db_config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: logout.php");
    exit;
}

$admin_id = $_SESSION['admin_id']; // admin ID

// Fetch admin settings
$sql = "SELECT transport_enabled FROM school_settings WHERE person='admin' AND person_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

// 🚨 If Transport module is disabled
if (!$settings || $settings['transport_enabled'] == 0) {
    echo "<script>alert('Transport module is disabled by admin settings.'); window.location.href='logout.php';</script>";
    exit;
}
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("transport");
    if (el) {
        el.classList.add("active");
    }
});
</script>
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>🚍 Transport Dashboard</h1>
        </div>

        <div class="row" id="dashboardCards">
            <!-- Cards injected by AJAX -->
        </div>

        <!-- Report Bus Problem Form -->
        <div class="card mt-4">
            <div class="card-header bg-danger text-white">
                <h5>🚨 Report Bus Problem</h5>
            </div>
            <div class="card-body">
                <form id="busProblemForm">
                    <div class="form-group mb-3">
                        <label for="bus_id">Select Bus</label>
                        <select class="form-control" id="bus_id" name="bus_id" required>
                            <option value="">-- Select Bus --</option>
                            <?php
              $school_id = $_SESSION['admin_id'];
              $buses = mysqli_query($conn, "SELECT id, bus_number FROM buses WHERE status='Active' AND school_id=$school_id");
              while ($b = mysqli_fetch_assoc($buses)) {
                  echo "<option value='{$b['id']}'>Bus #{$b['bus_number']}</option>";
              }
              ?>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="problem">Problem Description</label>
                        <textarea class="form-control" id="problem" name="problem" rows="3"
                            placeholder="Describe the issue..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger">Submit Report</button>
                </form>
            </div>
        </div>

        <!-- Open Bus Problems Table -->
        <div class="card mt-4">
            <div class="card-header bg-warning text-dark">
                <h5>📝 Open Bus Problems</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped" id="openBusProblemsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Bus Number</th>
                            <th>Problem</th>
                            <th>Status</th>
                            <th>Reported At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- AJAX-loaded content -->
                    </tbody>
                </table>
            </div>
        </div>

    </section>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {

    // Load Dashboard Cards
    function loadDashboardCards() {
        $.getJSON('ajax/transport_dashboard.php', function(res) {
            if (res.status === 'success') {
                let html = `
                  <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card card-statistic-1">
                      <div class="card-icon bg-primary"><i class="fas fa-bus"></i></div>
                      <div class="card-wrap">
                        <div class="card-header"><h4>Total Buses</h4></div>
                        <div class="card-body">${res.buses}</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card card-statistic-1">
                      <div class="card-icon bg-success"><i class="fas fa-user-tie"></i></div>
                      <div class="card-wrap">
                        <div class="card-header"><h4>Total Drivers</h4></div>
                        <div class="card-body">${res.drivers}</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card card-statistic-1">
                      <div class="card-icon bg-info"><i class="fas fa-user-graduate"></i></div>
                      <div class="card-wrap">
                        <div class="card-header"><h4>Assigned Students</h4></div>
                        <div class="card-body">${res.assigned_students}</div>
                      </div>
                    </div>
                  </div>
                `;
                $('#dashboardCards').html(html);
            } else {
                $('#dashboardCards').html(`<div class="alert alert-danger">${res.message}</div>`);
            }
        });
    }

    // Load Open Bus Problems
    function loadOpenBusProblems() {
        $.getJSON('ajax/get_open_bus_problems.php', function(res) {
            if (res.status === 'success') {
                let tbody = '';
                res.data.forEach((row, index) => {
                    tbody += `<tr>
                        <td>${index+1}</td>
                        <td>Bus #${row.bus_number}</td>
                        <td>${row.problem}</td>
                        <td>${row.status}</td>
                        <td>${row.created_at}</td>
                        <td>
                          <button class="btn btn-success btn-sm mark-solved" data-id="${row.id}">Mark as Solved</button>
                        </td>
                    </tr>`;
                });
                $('#openBusProblemsTable tbody').html(tbody);
            }
        });
    }

    // Initial Load
    loadDashboardCards();
    loadOpenBusProblems();

    // Handle Bus Problem Form Submission
    $("#busProblemForm").on("submit", function(e) {
        e.preventDefault();
        $.post("ajax/report_bus_problem.php", $(this).serialize(), function(res) {
            if (res.status === "success") {
                alert(res.message);
                $("#busProblemForm")[0].reset();
                loadOpenBusProblems(); // refresh table
            } else {
                alert("❌ " + res.message);
            }
        }, "json");
    });

    // Handle Mark as Solved button click
    $(document).on("click", ".mark-solved", function() {
        let problemId = $(this).data("id");
        $.post("ajax/mark_bus_problem_solved.php", {
            id: problemId
        }, function(res) {
            if (res.status === "success") {
                alert(res.message);
                loadOpenBusProblems(); // refresh table
            } else {
                alert("❌ " + res.message);
            }
        }, "json");
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>