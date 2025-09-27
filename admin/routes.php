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
            <h1>Transport — Routes</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addRouteModal">Add Route</button>
        </div>

        <div class="card">
            <div class="card-body">

                <!-- Search -->
                <form id="searchForm" class="form-inline mb-3">
                    <div class="input-group" style="max-width:600px;">
                        <input type="text" id="search" name="search" class="form-control"
                            placeholder="Search by route name or stop">
                        <div class="input-group-append">
                            <button class="btn btn-success" type="submit">Search</button>
                            <button type="button" id="resetBtn" class="btn btn-light">Reset</button>
                        </div>
                    </div>
                </form>

                <!-- Messages -->
                <div id="msgBox"></div>

                <!-- Routes Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Route Name</th>
                                <th>Stops</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="routeTableBody">
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

<!-- Add Route Modal -->
<div class="modal fade" id="addRouteModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="addRouteForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Route</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label>Route Name</label>
                <input type="text" name="route_name" class="form-control mb-2" required>
                <label>Stops (comma separated)</label>
                <textarea name="stops" class="form-control mb-2" rows="3" placeholder="Stop1, Stop2, Stop3"></textarea>
                <label>Status</label>
                <select name="status" class="form-control mb-2">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Route</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Route Modal -->
<div class="modal fade" id="editRouteModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editRouteForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Route</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <label>Route Name</label>
                <input type="text" name="route_name" id="edit_route_name" class="form-control mb-2" required>
                <label>Stops</label>
                <textarea name="stops" id="edit_stops" class="form-control mb-2" rows="3"></textarea>
                <label>Status</label>
                <select name="status" id="edit_status" class="form-control mb-2">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Update Route</button>
            </div>
        </form>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function loadRoutes(page = 1, search = '') {
    $.ajax({
        url: 'ajax/list_routes.php',
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
                    $.each(res.data, function(i, r) {
                        rows += `
              <tr>
                <td>${r.no}</td>
                <td>${r.route_name}</td>
                <td>${r.stops}</td>
                <td>${r.status}</td>
                <td>${r.created_at}</td>
                <td>
                  <button class="btn btn-sm btn-info editBtn" data-id="${r.id}">Edit</button>
                  <button class="btn btn-sm btn-danger deleteBtn" data-id="${r.id}">Delete</button>
                </td>
              </tr>`;
                    });
                } else {
                    rows = `<tr><td colspan="6" class="text-center">No routes found</td></tr>`;
                }
                $('#routeTableBody').html(rows);

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
        },
        error: function(xhr) {
            alert("❌ AJAX Error: " + xhr.responseText);
        }
    });
}

$(document).ready(function() {
    loadRoutes();

    // Search
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        loadRoutes(1, $('#search').val());
    });

    $('#resetBtn').click(function() {
        $('#search').val('');
        loadRoutes();
    });

    // Pagination click
    $(document).on('click', '#pagination .page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        loadRoutes(page, $('#search').val());
    });

    // Add Route
    $('#addRouteForm').submit(function(e) {
        e.preventDefault();
        $.post('ajax/add_route.php', $(this).serialize(), function(res) {
            if (res.status === 'success') {
                alert(res.message);
                $('#addRouteModal').modal('hide');
                $('#addRouteForm')[0].reset();
                loadRoutes();
            } else {
                alert("❌ " + res.message);
            }
        }, 'json').fail(function(xhr) {
            alert("❌ Server Error: " + xhr.responseText);
        });
    });

    // Open Edit modal
    $(document).on('click', '.editBtn', function() {
        let id = $(this).data('id');
        $.getJSON('ajax/list_routes.php', {
            id: id
        }, function(res) {
            if (res.status === 'success') {
                const r = res.data;
                $('#edit_id').val(r.id);
                $('#edit_route_name').val(r.route_name);
                $('#edit_stops').val(r.stops);
                $('#edit_status').val(r.status);
                $('#editRouteModal').modal('show');
            } else {
                alert("❌ " + res.message);
            }
        });
    });

    // Update Route
    $('#editRouteForm').submit(function(e) {
        e.preventDefault();
        $.post('ajax/update_route.php', $(this).serialize(), function(res) {
            if (res.status === 'success') {
                alert(res.message);
                $('#editRouteModal').modal('hide');
                loadRoutes();
            } else {
                alert("❌ " + res.message);
            }
        }, 'json').fail(function(xhr) {
            alert("❌ Server Error: " + xhr.responseText);
        });
    });

    // Delete Route
    $(document).on('click', '.deleteBtn', function() {
        if (!confirm('Are you sure you want to delete this route?')) return;
        let id = $(this).data('id');
        $.post('ajax/delete_route.php', {
            id: id
        }, function(res) {
            if (res.status === 'success') {
                alert(res.message);
                loadRoutes();
            } else {
                alert("❌ " + res.message);
            }
        }, 'json').fail(function(xhr) {
            alert("❌ Server Error: " + xhr.responseText);
        });
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>