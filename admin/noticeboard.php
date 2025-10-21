<?php
require_once 'assets/php/header.php';
?>
<style>
#app_link {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#apps ul {
    display: block !important;
}

#notice_board {
    color: #000;
}
</style>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Digital Notice Board</h1>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="noticeForm" enctype="multipart/form-data">
                            <input type="hidden" name="school_id" value="<?= $_SESSION['admin_id'] ?>">
                            <input type="hidden" name="id" id="noticeId">

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Notice Title</label>
                                    <input type="text" class="form-control" name="title" id="noticeTitle" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Notice Date</label>
                                    <input type="date" class="form-control" name="notice_date" id="noticeDate" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Valid Till</label>
                                    <input type="date" class="form-control" name="expiry_date" id="expiryDate" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Issued By</label>
                                    <input type="text" class="form-control" name="issued_by" id="issuedBy" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Purpose / Description</label>
                                    <textarea class="form-control" name="purpose" id="noticePurpose" rows="3"
                                        required></textarea>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Notice Type</label>
                                    <select class="form-control" name="notice_type" id="noticeType" required>
                                        <option disabled selected>Choose type</option>
                                        <option>Announcement</option>
                                        <option>Exam</option>
                                        <option>Holiday</option>
                                        <option>Event</option>
                                        <option>Policy</option>
                                        <option>General</option>
                                        <option>Emergency</option>
                                        <option>Others</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Audience</label>
                                    <select class="form-control" name="audience" id="noticeAudience" required>
                                        <option disabled selected>Select target group</option>
                                        <option>All Students</option>
                                        <option>Faculty</option>
                                        <option>Everyone</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Attach File (optional)</label>
                                    <input type="file" class="form-control-file" name="file" id="noticeFile"
                                        accept=".pdf,.jpg,.png,.jpeg">
                                </div>
                            </div>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary">Save Notice</button>
                                <button type="button" class="btn btn-secondary" onclick="resetForm()">Clear</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <hr>
        <h3>All Notices</h3>
        <div id="allNotices"></div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    loadNotices();

    $('#noticeForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const actionType = $('#noticeId').val() ? 'update' : 'insert';
        formData.append('action', actionType);

        $.ajax({
            url: 'ajax/notice_crud.php',
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                alert(res);
                $('#noticeForm')[0].reset();
                $('#noticeId').val('');
                loadNotices();
            }
        });
    });

    $(document).on('click', '.deleteNotice', function() {
        if (confirm('Are you sure you want to delete this notice?')) {
            let id = $(this).data('id');
            $.post('ajax/notice_crud.php', {
                action: 'delete',
                id: id
            }, function(res) {
                alert(res);
                loadNotices();
            });
        }
    });

    $(document).on('click', '.editNotice', function() {
        const id = $(this).data('id');
        $.post('ajax/notice_crud.php', {
            action: 'getOne',
            id: id
        }, function(res) {
            const n = JSON.parse(res);
            $('#noticeId').val(n.id);
            $('#noticeTitle').val(n.title);
            $('#noticeDate').val(n.notice_date);
            $('#expiryDate').val(n.expiry_date);
            $('#issuedBy').val(n.issued_by);
            $('#noticePurpose').val(n.purpose);
            $('#noticeType').val(n.notice_type);
            $('#noticeAudience').val(n.audience);
        });
    });

    function loadNotices() {
        $.post('ajax/notice_crud.php', {
            action: 'getAll'
        }, function(data) {
            $('#allNotices').html(data);
        });
    }
});

function resetForm() {
    $('#noticeForm')[0].reset();
    $('#noticeId').val('');
}
</script>

<?php require_once 'assets/php/footer.php'; ?>