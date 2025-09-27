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
            <h1>Transport — Drivers</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addDriverModal">Add Driver</button>
        </div>

        <div class="card">
            <div class="card-body">

                <!-- Search -->
                <form id="searchForm" class="form-inline mb-3">
                    <div class="input-group" style="max-width:600px;">
                        <input type="text" id="search" name="search" class="form-control"
                            placeholder="Search by driver name or license">
                        <div class="input-group-append">
                            <button class="btn btn-success" type="submit">Search</button>
                            <button type="button" id="resetBtn" class="btn btn-light">Reset</button>
                        </div>
                    </div>
                </form>

                <!-- Messages -->
                <div id="msgBox"></div>

                <!-- Drivers Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>License No</th>
                                <th>Phone</th>
                                <th>Assigned Bus</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="driverTableBody">
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

<!-- Add Driver Modal -->
<div class="modal fade" id="addDriverModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="addDriverForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Driver</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label>Full Name</label>
                <input type="text" name="name" class="form-control mb-2" required>
                <label>License No</label>
                <input type="text" name="license_no" class="form-control mb-2" required>
                <label>Phone</label>
                <input type="text" name="phone" class="form-control mb-2">
                <label>Assign Bus</label>
                <select name="bus_id" class="form-control mb-2" id="busDropdown"></select>
                <label>Status</label>
                <select name="status" class="form-control mb-2">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Driver</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Driver Modal -->
<div class="modal fade" id="editDriverModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editDriverForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Driver</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <label>Full Name</label>
                <input type="text" name="name" id="edit_name" class="form-control mb-2" required>
                <label>License No</label>
                <input type="text" name="license_no" id="edit_license_no" class="form-control mb-2" required>
                <label>Phone</label>
                <input type="text" name="phone" id="edit_phone" class="form-control mb-2">
                <label>Assign Bus</label>
                <select name="bus_id" id="edit_bus_id" class="form-control mb-2"></select>
                <label>Status</label>
                <select name="status" id="edit_status" class="form-control mb-2">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Update Driver</button>
            </div>
        </form>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function loadDrivers(page = 1, search = '') {
    $.ajax({
        url: 'ajax/list_drivers.php',
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
                    $.each(res.data, function(i, d) {
                        rows += `
              <tr>
                <td>${d.no}</td>
                <td>${d.name}</td>
                <td>${d.license_no}</td>
                <td>${d.phone}</td>
                <td>${d.bus_number ?? '-'}</td>
                <td>${d.status}</td>
                <td>${d.created_at}</td>
                <td>
                  <button class="btn btn-sm btn-info editBtn" data-id="${d.id}">Edit</button>
                  <button class="btn btn-sm btn-danger deleteBtn" data-id="${d.id}">Delete</button>
                </td>
              </tr>`;
                    });
                } else {
                    rows = `<tr><td colspan="8" class="text-center">No drivers found</td></tr>`;
                }
                $('#driverTableBody').html(rows);

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

// Populate bus dropdown
function loadBusOptions(target) {
    $.getJSON('ajax/list_buses.php', function(res) {
        if (res.status === 'success') {
            let options = '<option value="">-- Select Bus --</option>';
            $.each(res.data, function(i, b) {
                options += `<option value="${b.id}">${b.bus_number}</option>`;
            });
            $(target).html(options);
        }
    });
}

$(document).ready(function() {
    loadDrivers();
    loadBusOptions('#busDropdown');
    loadBusOptions('#edit_bus_id');

    // Search
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        loadDrivers(1, $('#search').val());
    });
    $('#resetBtn').click(function() {
        $('#search').val('');
        loadDrivers();
    });

    // Pagination click
    $(document).on('click', '#pagination .page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        loadDrivers(page, $('#search').val());
    });

    // Add Driver
    $('#addDriverForm').submit(function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/add_driver.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    alert(res.message);
                    $('#addDriverModal').modal('hide');
                    $('#addDriverForm')[0].reset();
                    loadDrivers();
                } else {
                    alert("❌ Error: " + res.message);
                }
            },
            error: function(xhr, status, error) {
                alert("❌ AJAX Error: " + error + "\n\nResponse:\n" + xhr.responseText);
            }
        });
    });


    // Open Edit modal
    $(document).on('click', '.editBtn', function() {
        let id = $(this).data('id');
        $.getJSON('ajax/list_drivers.php', {
            id: id
        }, function(res) {
            if (res.status === 'success') {
                const d = res.data;
                $('#edit_id').val(d.id);
                $('#edit_name').val(d.name);
                $('#edit_license_no').val(d.license_no);
                $('#edit_phone').val(d.phone);
                $('#edit_bus_id').val(d.bus_id);
                $('#edit_status').val(d.status);
                $('#editDriverModal').modal('show');
            }
        });
    });

    // Update Driver
    $('#editDriverForm').submit(function(e) {
        e.preventDefault();
        $.post('ajax/update_driver.php', $(this).serialize(), function(res) {
            $('#msgBox').html(`<div class="alert alert-${res.status}">${res.message}</div>`);
            if (res.status === 'success') {
                $('#editDriverModal').modal('hide');
                loadDrivers();
            }
        }, 'json');
    });

    // Delete Driver
    $(document).on('click', '.deleteBtn', function() {
        if (!confirm('Are you sure you want to delete this driver?')) return;
        let id = $(this).data('id');
        $.post('ajax/delete_driver.php', {
            id: id
        }, function(res) {
            $('#msgBox').html(`<div class="alert alert-${res.status}">${res.message}</div>`);
            if (res.status === 'success') loadDrivers();
        }, 'json');
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>