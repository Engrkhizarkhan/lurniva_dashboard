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
                <select id="filterType" class="form-select">
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

    $(document).on('change', '.status-select', function() {
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


});
</script>
<?php require_once 'assets/php/footer.php'; ?>