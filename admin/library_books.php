<?php require_once 'assets/php/header.php'; 
include_once('sass/db_config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: logout.php");
    exit;
}

$admin_id = $_SESSION['admin_id']; // admin ID

// Fetch admin settings
$sql = "SELECT library_enabled FROM school_settings WHERE person='admin' AND person_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

// 🚨 If Library module is disabled
if (!$settings || $settings['library_enabled'] == 0) {
    echo "<script>alert('Library module is disabled by admin settings.'); window.location.href='logout.php';</script>";
    exit;
}
?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("library");
    if (el) {
        el.classList.add("active");
    }
});
</script>
<div class="main-content">
    <section class="section">
        <div class="section-header d-flex justify-content-between align-items-center">
            <h1>Library — Books</h1>
            <button class="btn btn-primary" data-toggle="modal" data-target="#addBookModal">Add Book</button>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Search -->
                <form id="searchForm" class="form-inline mb-3">
                    <div class="input-group" style="max-width:600px;">
                        <input type="text" id="search" name="search" class="form-control"
                            placeholder="Search by title, author, ISBN or category">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">Search</button>
                            <button type="button" id="resetBtn" class="btn btn-primary">Reset</button>
                        </div>
                    </div>
                </form>

                <!-- Messages -->
                <div id="msgBox"></div>

                <!-- Books Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Publisher</th>
                                <th>ISBN</th>
                                <th>Category</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Available</th>
                                <th>Added At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="bookTableBody">
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

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="addBookForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Book</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="text" name="title" class="form-control mb-2" placeholder="Title" required>
                <input type="text" name="author" class="form-control mb-2" placeholder="Author">
                <input type="text" name="publisher" class="form-control mb-2" placeholder="Publisher">
                <input type="text" name="isbn" class="form-control mb-2" placeholder="ISBN">
                <input type="text" name="category" class="form-control mb-2" placeholder="Category">
                <input type="number" name="quantity" class="form-control mb-2" placeholder="Quantity" min="1" value="1"
                    required>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Book Modal -->
<div class="modal fade" id="editBookModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editBookForm" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Book</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <input type="text" name="title" id="edit_title" class="form-control mb-2" placeholder="Title" required>
                <input type="text" name="author" id="edit_author" class="form-control mb-2" placeholder="Author">
                <input type="text" name="publisher" id="edit_publisher" class="form-control mb-2"
                    placeholder="Publisher">
                <input type="text" name="isbn" id="edit_isbn" class="form-control mb-2" placeholder="ISBN">
                <input type="text" name="category" id="edit_category" class="form-control mb-2" placeholder="Category">
                <input type="number" name="quantity" id="edit_quantity" class="form-control mb-2" placeholder="Quantity"
                    min="1">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Update</button>
            </div>
        </form>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadBooks(page = 1, search = '') {
    $.ajax({
        url: 'ajax/list_books.php',
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
                    $.each(res.data, function(i, book) {
                        rows += `
              <tr>
                <td>${book.no}</td>
                <td>${book.title}</td>
                <td>${book.author}</td>
                <td>${book.publisher}</td>
                <td>${book.isbn}</td>
                <td>${book.category}</td>
                <td class="text-center">${book.quantity}</td>
                <td class="text-center">${book.available}</td>
                <td>${book.added_at}</td>
                <td>
                  <button class=" btn-sm btn btn-primary editBtn" data-id="${book.id}">Edit</button>
                  <button class="btn btn-danger btn-sm  deleteBtn" data-id="${book.id}">Delete</button>
                </td>
              </tr>`;
                    });
                } else {
                    rows = `<tr><td colspan="10" class="text-center">No books found</td></tr>`;
                }
                $('#bookTableBody').html(rows);

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

// Init load
$(document).ready(function() {
    loadBooks();

    // Search
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        loadBooks(1, $('#search').val());
    });

    // Reset
    $('#resetBtn').click(function() {
        $('#search').val('');
        loadBooks();
    });

    // Pagination click
    $(document).on('click', '#pagination .page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        loadBooks(page, $('#search').val());
    });

    // Add Book
    $('#addBookForm').submit(function(e) {
        e.preventDefault();
        $.post('ajax/add_book.php', $(this).serialize(), function(res) {
            if (res.status === 'success') {
                $('#addBookModal').modal('hide');
                loadBooks();
            }
            $('#msgBox').html(`<div class="alert alert-${res.status}">${res.message}</div>`);
        }, 'json');
    });

    // Open Edit Modal
    $(document).on('click', '.editBtn', function() {
        let id = $(this).data('id');
        $.getJSON('ajax/list_books.php', {
            id: id
        }, function(res) {
            if (res.status === 'success') {
                let b = res.data;
                $('#edit_id').val(b.id);
                $('#edit_title').val(b.title);
                $('#edit_author').val(b.author);
                $('#edit_publisher').val(b.publisher);
                $('#edit_isbn').val(b.isbn);
                $('#edit_category').val(b.category);
                $('#edit_quantity').val(b.quantity);
                $('#editBookModal').modal('show');
            }
        });
    });

    // Update Book
    $('#editBookForm').submit(function(e) {
        e.preventDefault();
        $.post('ajax/update_book.php', $(this).serialize(), function(res) {
            if (res.status === 'success') {
                $('#editBookModal').modal('hide');
                loadBooks();
            }
            $('#msgBox').html(`<div class="alert alert-${res.status}">${res.message}</div>`);
        }, 'json');
    });

    // Delete Book
    $(document).on('click', '.deleteBtn', function() {
        if (confirm("Are you sure?")) {
            let id = $(this).data('id');
            $.post('ajax/delete_book.php', {
                id: id
            }, function(res) {
                if (res.status === 'success') loadBooks();
                $('#msgBox').html(
                    `<div class="alert alert-${res.status}">${res.message}</div>`);
            }, 'json');
        }
    });
});
</script>
<?php require_once 'assets/php/footer.php'; ?>