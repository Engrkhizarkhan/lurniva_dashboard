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
        <div class="section-header d-flex justify-content-between align-items-center">
            <h1>Transport — Student Route Assignments</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addAssignModal">Assign Student</button>
        </div>

        <div class="card">
            <div class="card-body">

                <!-- Search -->
                <form id="searchForm" class="form-inline mb-3">
                    <div class="input-group" style="max-width:600px;">
                        <input type="text" id="search" name="search" class="form-control"
                            placeholder="Search by student name or route">
                        <div class="input-group-append">
                            <button class="btn btn-success" type="submit">Search</button>
                            <button type="button" id="resetBtn" class="btn btn-light">Reset</button>
                        </div>
                    </div>
                </form>

                <!-- Messages -->
                <div id="msgBox"></div>

                <!-- Assignments Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Route</th>
                                <th>Assigned At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="assignTableBody">
                            <!-- Rows injected via AJAX -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav>
                    <ul id="pagination" class="pagination"></ul>
                </nav>
            </div>
        </div>
    </section>
</div>

<!-- Add Assignment Modal -->
<div class="modal fade" id="addAssignModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="addAssignForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Student to Route</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label>Student</label>
                <select name="student_id" class="form-control mb-2" id="studentDropdown" required></select>
                <label>Route</label>
                <select name="route_id" class="form-control mb-2" id="routeDropdown" required></select>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Assign</button>
            </div>
        </form>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function loadAssignments(page = 1, search = '') {
    $.ajax({
        url: 'ajax/list_student_routes.php',
        type: 'GET',
        data: {
            page: page,
            search: search
        },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                let rows = '';
                if (res.data.length > 0) {
                    $.each(res.data, function(i, a) {
                        rows += `
              <tr>
                <td>${a.no}</td>
                <td>${a.student_name}</td>
                <td>${a.route_name}</td>
                <td>${a.assigned_at}</td>
                <td>
                  <button class="btn btn-sm btn-danger deleteBtn" data-id="${a.id}">Remove</button>
                </td>
              </tr>`;
                    });
                } else {
                    rows = `<tr><td colspan="5" class="text-center">No assignments found</td></tr>`;
                }
                $('#assignTableBody').html(rows);

                // Pagination
                let pag = '';
                for (let p = 1; p <= res.total_pages; p++) {
                    pag += `<li class="page-item ${p == res.page ? 'active' : ''}">
                    <a href="#" class="page-link" data-page="${p}">${p}</a>
                  </li>`;
                }
                $('#pagination').html(pag);
            } else {
                $('#msgBox').html(`<div class="alert alert-danger">${res.message}</div>`);
            }
        }
    });
}

// Populate dropdowns
function loadDropdowns() {
    $.getJSON('ajax/list_students.php', function(res) {
        if (res.status === 'success') {
            let options = '<option value="">-- Select Student --</option>';
            $.each(res.data, function(i, s) {
                options += `<option value="${s.id}">${s.name}</option>`;
            });
            $('#studentDropdown').html(options);
        }
    });

    $.getJSON('ajax/list_routes.php', function(res) {
        if (res.status === 'success') {
            let options = '<option value="">-- Select Route --</option>';
            $.each(res.data, function(i, r) {
                options += `<option value="${r.id}">${r.route_name}</option>`;
            });
            $('#routeDropdown').html(options);
        }
    });
}

$(document).ready(function() {
    loadAssignments();
    loadDropdowns();

    // Search
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        loadAssignments(1, $('#search').val());
    });
    $('#resetBtn').click(function() {
        $('#search').val('');
        loadAssignments();
    });

    // Pagination click
    $(document).on('click', '#pagination .page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        loadAssignments(page, $('#search').val());
    });

    // Add Assignment
    $('#addAssignForm').submit(function(e) {
        e.preventDefault();
        $.post('ajax/add_student_route.php', $(this).serialize(), function(res) {
            $('#msgBox').html(`<div class="alert alert-${res.status}">${res.message}</div>`);
            if (res.status === 'success') {
                $('#addAssignModal').modal('hide');
                $('#addAssignForm')[0].reset();
                loadAssignments();
            }
        }, 'json');
    });

    // Delete Assignment
    $(document).on('click', '.deleteBtn', function() {
        if (!confirm('Remove this student from route?')) return;
        let id = $(this).data('id');
        $.post('ajax/delete_student_route.php', {
            id: id
        }, function(res) {
            $('#msgBox').html(`<div class="alert alert-${res.status}">${res.message}</div>`);
            if (res.status === 'success') loadAssignments();
        }, 'json');
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>