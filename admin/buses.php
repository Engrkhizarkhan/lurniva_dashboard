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
            <h1>Transport — Buses</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addBusModal">Add Bus</button>
        </div>

        <div class="card">
            <div class="card-body">

                <!-- Search -->
                <form id="searchForm" class="form-inline mb-3">
                    <div class="input-group" style="max-width:600px;">
                        <input type="text" id="search" name="search" class="form-control"
                            placeholder="Search by bus number or status">
                        <div class="input-group-append">
                            <button class="btn btn-success" type="submit">Search</button>
                            <button type="button" id="resetBtn" class="btn btn-light">Reset</button>
                        </div>
                    </div>
                </form>

                <!-- Messages -->
                <div id="msgBox"></div>

                <!-- Buses Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Bus Number</th>
                                <th class="text-center">Capacity</th>
                                <th class="text-center">Status</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="busTableBody">
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

<!-- Add Bus Modal -->
<div class="modal fade" id="addBusModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="addBusForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Bus</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <label>Bus Number</label>
                <input type="text" name="bus_number" class="form-control mb-2" placeholder="e.g. BUS-101" required>
                <label>Capacity</label>
                <input type="number" name="capacity" class="form-control mb-2" placeholder="Number of seats" min="1"
                    value="20" required>
                <label>Status</label>
                <select name="status" class="form-control mb-2">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Bus</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Bus Modal -->
<div class="modal fade" id="editBusModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editBusForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Bus</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <label>Bus Number</label>
                <input type="text" name="bus_number" id="edit_bus_number" class="form-control mb-2" required>
                <label>Capacity</label>
                <input type="number" name="capacity" id="edit_capacity" class="form-control mb-2" min="1" required>
                <label>Status</label>
                <select name="status" id="edit_status" class="form-control mb-2">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Update Bus</button>
            </div>
        </form>
    </div>
</div>


<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function loadBuses(page = 1, search = '') {
    $.ajax({
        url: 'ajax/list_buses.php',
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
                    $.each(res.data, function(i, b) {
                        rows += `
              <tr>
                <td>${b.no}</td>
                <td>${b.bus_number}</td>
                <td class="text-center">${b.capacity}</td>
                <td class="text-center">${b.status}</td>
                <td>${b.created_at}</td>
                <td>
                  <button class="btn btn-sm btn-info editBtn" data-id="${b.id}">Edit</button>
                  <button class="btn btn-sm btn-danger deleteBtn" data-id="${b.id}">Delete</button>
                </td>
              </tr>`;
                    });
                } else {
                    rows = `<tr><td colspan="6" class="text-center">No buses found</td></tr>`;
                }
                $('#busTableBody').html(rows);

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
            $('#msgBox').html(`<div class="alert alert-danger">Error: ${xhr.responseText}</div>`);
        }
    });
}

$(document).ready(function() {
    loadBuses();

    // Search
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        loadBuses(1, $('#search').val());
    });

    $('#resetBtn').click(function() {
        $('#search').val('');
        loadBuses();
    });

    // Pagination click
    $(document).on('click', '#pagination .page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        loadBuses(page, $('#search').val());
    });

    // Add Bus
    $('#addBusForm').submit(function(e) {
        e.preventDefault();
        $.post('ajax/add_bus.php', $(this).serialize(), function(res) {
            $('#msgBox').html(`<div class="alert alert-${res.status}">${res.message}</div>`);
            if (res.status === 'success') {
                $('#addBusModal').modal('hide');
                $('#addBusForm')[0].reset();
                loadBuses();
            }
        }, 'json').fail(function(xhr) {
            $('#msgBox').html(
                `<div class="alert alert-danger">Error: ${xhr.responseText}</div>`);
        });
    });

    // Open Edit modal
    $(document).on('click', '.editBtn', function() {
        let id = $(this).data('id');
        $.getJSON('ajax/list_buses.php', {
            id: id
        }, function(res) {
            if (res.status === 'success') {
                const b = res.data;
                $('#edit_id').val(b.id);
                $('#edit_bus_number').val(b.bus_number);
                $('#edit_capacity').val(b.capacity);
                $('#edit_status').val(b.status);
                $('#editBusModal').modal('show');
            } else {
                $('#msgBox').html(`<div class="alert alert-danger">${res.message}</div>`);
            }
        }).fail(function(xhr) {
            $('#msgBox').html(
                `<div class="alert alert-danger">Error: ${xhr.responseText}</div>`);
        });
    });

    // Update Bus
    $('#editBusForm').submit(function(e) {
        e.preventDefault();
        $.post('ajax/update_bus.php', $(this).serialize(), function(res) {
            $('#msgBox').html(`<div class="alert alert-${res.status}">${res.message}</div>`);
            if (res.status === 'success') {
                $('#editBusModal').modal('hide');
                loadBuses();
            }
        }, 'json').fail(function(xhr) {
            $('#msgBox').html(
                `<div class="alert alert-danger">Error: ${xhr.responseText}</div>`);
        });
    });

    // Delete Bus
    $(document).on('click', '.deleteBtn', function() {
        if (!confirm('Are you sure you want to delete this bus?')) return;
        let id = $(this).data('id');
        $.post('ajax/delete_bus.php', {
            id: id
        }, function(res) {
            $('#msgBox').html(`<div class="alert alert-${res.status}">${res.message}</div>`);
            if (res.status === 'success') loadBuses();
        }, 'json').fail(function(xhr) {
            $('#msgBox').html(
                `<div class="alert alert-danger">Error: ${xhr.responseText}</div>`);
        });
    });
});
</script>
<?php require_once 'assets/php/footer.php'; ?>