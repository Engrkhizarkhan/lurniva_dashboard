<?php require_once 'assets/php/header.php'; ?>

<style>
#app_link {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#apps ul {
    display: block !important;
}

#student_list {
    color: #000;
}
</style>
<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h3>Student QR Code</h3>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <select id="filterType" class="form-control">
                    <option value="full_name">Search by Name</option>
                    <option value="class_grade">Search by Class</option>
                    <option value="roll_number">Search by Roll Number</option>
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" id="searchInput" class="form-control" placeholder="Enter value...">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary w-100" onclick="loadStudentQRTable()">Search</button>
            </div>
        </div>

        <div class="mb-3">
            <button id="downloadQR" class="btn btn-success">Download All QR Codes (PDF)</button>
        </div>

        <div id="studentTable">Loading...</div>
    </section>
</div>

<!-- Migration Modal -->
<div class="modal fade" id="migrationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="migrationForm">
                <div class="modal-header">
                    <h5 class="modal-title">Migrate Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="student_id" id="migrateStudentId">
                    <div class="mb-3">
                        <label>Select School (optional)</label>
                        <select name="school_id" class="form-control">
                            <option value="">-- No School --</option>
                            <?php
              require_once 'sass/db_config.php';
              $schools = $conn->query("SELECT id, school_name FROM schools");
              while($s = $schools->fetch_assoc()){
                echo "<option value='{$s['id']}'>{$s['school_name']}</option>";
              }
              ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Migrate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Promote Modal -->
<div class="modal fade" id="promoteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="promoteForm">
                <div class="modal-header">
                    <h5 class="modal-title">Promote Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="student_id" id="promoteStudentId">
                    <div class="mb-3">
                        <label>Select New Class</label>
                        <select name="class_id" class="form-control" required>
                            <?php
              $classes = $conn->query("SELECT id, class_name, section FROM class_timetable_meta");
              while($c = $classes->fetch_assoc()){
                echo "<option value='{$c['id']}'>{$c['class_name']} - {$c['section']}</option>";
              }
              ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Promote</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
function loadStudentQRTable() {
    const type = $('#filterType').val();
    const value = $('#searchInput').val();

    $.get('ajax/student_qr_list.php', {
        filter_type: type,
        filter_value: value
    }, function(response) {
        $('#studentTable').html(response);
    });
}

$(document).ready(function() {
    loadStudentQRTable();

    $(document).on('change', '#status-select', function() {
        const id = $(this).data('id');
        const newStatus = $(this).val();

        $.post('ajax/update_student_status.php', {
            id: id,
            status: newStatus
        }, function(response) {
            alert(response.message);
            loadStudentQRTable();
        }, 'json');
    });

    $('#downloadQR').on('click', function() {
        window.location.href = 'download_qr_pdf.php';
    });

    // Migration Button
    $(document).on('click', '.migrate-btn', function() {
        $('#migrateStudentId').val($(this).data('id'));
        $('#migrationModal').modal('show');
    });

    // Promote Button
    $(document).on('click', '.promote-btn', function() {
        $('#promoteStudentId').val($(this).data('id'));
        $('#promoteModal').modal('show');
    });

    // Handle Migration Submit
    $('#migrationForm').on('submit', function(e) {
        e.preventDefault();
        $.post('ajax/migrate_student.php', $(this).serialize(), function(response) {
            alert(response.message);
            $('#migrationModal').modal('hide');
            loadStudentQRTable();
        }, 'json');
    });

    // Handle Promote Submit
    $('#promoteForm').on('submit', function(e) {
        e.preventDefault();
        $.post('ajax/promote_student.php', $(this).serialize(), function(response) {
            alert(response.message);
            $('#promoteModal').modal('hide');
            loadStudentQRTable();
        }, 'json');
    });
});
</script>
<?php require_once 'assets/php/footer.php'; ?>