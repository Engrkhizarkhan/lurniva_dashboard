<?php require_once 'assets/php/header.php'; 
include_once('sass/db_config.php');

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
            <h1>Library — Issue / Return</h1>
        </div>

        <div class="card">
            <div class="card-body">

                <!-- Messages -->
                <div id="msgBox"></div>

                <!-- Issue Book Form -->
                <h5>📖 Issue Book</h5>
                <form id="issueForm" class="mb-4">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Student</label>
                            <select name="student_id" id="studentSelect" class="form-control select2"></select>
                        </div>

                        <div class="col-md-6">
                            <label>Faculty</label>
                            <select name="faculty_id" id="facultySelect" class="form-control select2"></select>
                        </div>

                        <div class="col-md-4">
                            <label>Book</label>
                            <select name="book_id" id="bookSelect" class="form-control select2" required></select>
                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Issue Date</label>
                            <input type="date" name="issue_date" class="form-control" required>
                        </div>
                        <div class="col-md-3 mt-2">
                            <label>Due Date</label>
                            <input type="date" name="due_date" class="form-control" required>
                        </div>
                    </div>
                    <button type="button" id="submit" class="btn btn-primary mt-3">Issue Book</button>

                </form>

                <!-- Active Transactions -->
                <h5>📌 Active Issues</h5>

                <!-- Search for Active Issues -->
                <form id="searchTransactionForm" class="form-inline mb-3">
                    <div class="input-group" style="max-width:600px;">
                        <input type="text" id="searchTransaction" name="search" class="form-control"
                            placeholder="Search by book, student or faculty">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary btn-sm " type="submit">Search</button>
                            <button type="button" id="resetTransactionBtn"
                                class="btn btn-outline-danger btn-sm ">Reset</button>
                        </div>
                    </div>
                </form>

                <!-- Active Transactions Table -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Book</th>
                                <th>Student</th>
                                <th>Faculty</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Fine</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="transactionTable"></tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav>
                    <ul id="transactionPagination" class="pagination"></ul>
                </nav>


            </div>
        </div>
    </section>
</div>

<!-- Fine Modal -->
<div class="modal fade" id="fineModal" tabindex="-1" aria-labelledby="fineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="fineForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="fineModalLabel">Late Return Fine</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="fineMessage"></p>
                    <div class="mb-3">
                        <label for="fineAmount" class="form-label">Enter Fine Amount</label>
                        <input type="number" step="0.01" min="0" class="form-control" id="fineAmount" name="fine_amount"
                            required>
                        <input type="hidden" id="fineTransactionId" name="transaction_id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Fine & Return Book</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize all selects with class 'select2'
    $('.select2').select2({
        placeholder: 'Select an option',
        allowClear: true
    });

    // Optional: Populate dynamically via AJAX
    // Example for students
    $.getJSON('ajax/get_students.php', function(res) {
        if (res.status === 'success') {
            let opts = '<option value=""></option>'; // empty option for placeholder
            $.each(res.data, function(i, s) {
                opts +=
                    `<option value="${s.id}">${s.name} (Class: ${s.class} | Roll: ${s.roll})</option>`;
            });
            $('select[name="student_id"]').html(opts).trigger('change');
        } else {
            alert('Error loading students: ' + res.message);
        }
    });

    // Example for faculty
    $.getJSON('ajax/get_faculty.php', function(res) {
        if (res.status === 'success') {
            let opts = '<option value=""></option>';
            $.each(res.data, function(i, f) {
                opts += `<option value="${f.id}">${f.text}</option>`;
            });
            $('select[name="faculty_id"]').html(opts).trigger('change');
        } else {
            alert('Error loading faculty: ' + res.message);
        }
    });

    // Example for books

    function loadAvailableBooks() {
        $.getJSON('ajax/get_available_books.php', function(res) {
            if (res.status === 'success') {
                let opts = '<option value=""></option>'; // empty option for placeholder
                $.each(res.data, function(i, b) {
                    opts += `<option value="${b.id}">${b.title} (${b.available})</option>`;
                });
                $('select[name="book_id"]').html(opts).trigger('change');
            } else {
                alert('Error loading books: ' + res.message);
            }
        }).fail(function(xhr, status, error) {
            alert('AJAX error (loadAvailableBooks): ' + error + '\n' + xhr.responseText);
        });
    }


    // -----------------------------
    // Issue form submit
    // -----------------------------
    $('#submit').click(function(e) {
        e.preventDefault(); // not strictly needed now, but safe
        // alert('click'); // now this will show
        // Get all input values
        var student_id = $('#studentSelect').val();
        var faculty_id = $('#facultySelect').val();
        var book_id = $('#bookSelect').val();
        var issue_date = $('input[name="issue_date"]').val();
        var due_date = $('input[name="due_date"]').val();

        // Optional: validate
        if (!book_id) {
            $('#msgBox').html('<div class="alert alert-danger">Please select a book</div>');
            return;
        }

        // AJAX POST
        $.ajax({
            url: 'ajax/issue_book.php',
            type: 'POST',
            data: {
                student_id: student_id,
                faculty_id: faculty_id,
                book_id: book_id,
                issue_date: issue_date,
                due_date: due_date
            },
            dataType: 'json',
            success: function(res) {
                $('#msgBox').html(
                    `<div class="alert alert-${res.status}">${res.message}</div>`);

                if (res.status === 'success') {
                    // Refresh tables
                    loadAvailableBooks();
                    loadTransactions();

                    // Reset form
                    $('#issueForm')[0].reset();
                    $('#studentSelect').val(null).trigger('change');
                    $('#facultySelect').val(null).trigger('change');
                    $('#bookSelect').val(null).trigger('change');
                }
            },
            error: function(xhr, status, error) {
                alert('AJAX error (issue book): ' + error + '\n' + xhr.responseText);
            }
        });
    });

    // -----------------------------
    // Load active transactions
    // -----------------------------
    function loadTransactions(page = 1, search = '') {
        $.ajax({
            url: 'ajax/list_transactions.php',
            type: 'GET',
            data: {
                page: page,
                search: search
            },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    let rows = '';
                    $.each(res.data, function(i, t) {
                        let rowClass = (t.status === 'Overdue') ? 'table-danger' : '';
                        let fineDisplay = t.fine_amount ?
                            `${t.fine_amount} (${t.paid_status})` : '-';
                        rows += `
                        <tr class="${rowClass}">
                          <td>${t.id}</td>
                          <td>${t.book_title}</td>
                          <td>${t.student_name ?? '-'}</td>
                          <td>${t.faculty_name ?? '-'}</td>
                          <td>${t.issue_date}</td>
                          <td>${t.due_date}</td>
                          <td>${t.status}</td>
                          <td>${fineDisplay}</td>
                          <td>
                            ${t.status === 'Issued' || t.status === 'Overdue' ? 
                                `<button class="btn btn-sm btn-success returnBtn" data-id="${t.id}">Return</button>` : ''}
                          </td>
                        </tr>`;
                    });

                    $('#transactionTable').html(rows);

                    // Pagination
                    let pag = '';
                    for (let p = 1; p <= res.total_pages; p++) {
                        pag += `<li class="page-item ${p==res.page ? 'active' : ''}">
                                <a href="#" class="page-link" data-page="${p}">${p}</a>
                            </li>`;
                    }
                    $('#transactionPagination').html(pag);
                } else {
                    $('#transactionTable').html(
                        `<tr><td colspan="9" class="text-center">No transactions found</td></tr>`
                    );
                }
            }
        });
    }

    // Initial load
    $(document).ready(function() {
        loadTransactions();

        // Search form
        $('#searchTransactionForm').submit(function(e) {
            e.preventDefault();
            loadTransactions(1, $('#searchTransaction').val());
        });

        // Reset search
        $('#resetTransactionBtn').click(function() {
            $('#searchTransaction').val('');
            loadTransactions();
        });

        // Pagination click
        $(document).on('click', '#transactionPagination .page-link', function(e) {
            e.preventDefault();
            let page = $(this).data('page');
            loadTransactions(page, $('#searchTransaction').val());
        });
    });

    // -----------------------------
    // Return book
    // -----------------------------
    $(document).on('click', '.returnBtn', function() {
        let id = $(this).data('id');
        $.post('ajax/return_book.php', {
            id: id
        }, function(res) {
            if (res.status === 'late') {
                // Show Bootstrap modal
                $('#fineTransactionId').val(res.transaction_id);
                $('#fineAmount').val(''); // clear previous input
                $('#fineMessage').text(
                    `Book is late by ${res.days_late} day(s). Please enter the fine amount.`
                );
                var fineModal = new bootstrap.Modal(document.getElementById('fineModal'));
                fineModal.show();
            } else if (res.status === 'success') {
                alert(res.message);
                loadTransactions();
                loadAvailableBooks();
            } else {
                alert(res.message);
            }
        }, 'json');
    });

    $('#fineForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.post('ajax/save_fine.php', formData, function(res) {
            if (res.status === 'success') {


                alert(res.message);
                loadTransactions();
                loadAvailableBooks();
                fineModal.hide();
            } else {
                alert(res.message);
            }
        }, 'json');
    });


    // -----------------------------
    // Initial load
    // -----------------------------
    loadTransactions();
    loadAvailableBooks();



});
</script>

<?php require_once 'assets/php/footer.php'; ?>