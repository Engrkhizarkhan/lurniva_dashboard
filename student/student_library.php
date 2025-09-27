<?php
require_once 'sass/db_config.php';
require_once 'assets/php/header.php';

$student_id = $_SESSION['student_id']; // make sure student is logged in
?>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("student_library");
    if (el) {
        el.classList.add("active");
    }
});
</script>
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>My Library</h1>
        </div>

        <div class="section-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text" id="search" class="form-control" placeholder="Search your books...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="libraryTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Ajax-loaded content -->
                    </tbody>
                </table>
            </div>

            <nav>
                <ul class="pagination" id="pagination">
                    <!-- Ajax pagination -->
                </ul>
            </nav>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadLibrary(page = 1, search = '') {
    $.ajax({
        url: 'ajax/student_library_data.php',
        type: 'GET',
        data: {
            page: page,
            search: search
        },
        dataType: 'json',
        success: function(res) {
            if (res.status === 'success') {
                let tbody = '';
                res.data.forEach((row) => {
                    tbody += `<tr>
                        <td>${row.no}</td>
                        <td>${row.book_title}</td>
                        <td>${row.author}</td>
                        <td>${row.category}</td>
                        <td>${row.issue_date || '-'}</td>
                        <td>${row.due_date || '-'}</td>
                        <td>${row.status}</td>
                    </tr>`;
                });
                $('#libraryTable tbody').html(tbody);

                // Pagination
                let pages = '';
                for (let i = 1; i <= res.total_pages; i++) {
                    pages += `<li class="page-item ${i===res.page?'active':''}">
                                <a class="page-link page-btn" href="#" data-page="${i}">${i}</a>
                              </li>`;
                }
                $('#pagination').html(pages);
            }
        }
    });
}

$(document).ready(function() {
    loadLibrary();

    $('#search').on('keyup', function() {
        loadLibrary(1, $(this).val());
    });

    $(document).on('click', '.page-btn', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        let search = $('#search').val();
        loadLibrary(page, search);
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>