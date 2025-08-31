<?php require_once 'assets/php/header.php'; ?>
<style>
#leaveFormWrap {
    padding: 20px;
    background: #f7f8fc;
    border-radius: 10px;
    margin-bottom: 24px
}

.badge {
    padding: .35rem .5rem;
    border-radius: .5rem
}

.badge.pending {
    background: #fff3cd;
    color: #856404
}

.badge.approved {
    background: #d4edda;
    color: #155724
}

.badge.rejected {
    background: #f8d7da;
    color: #721c24
}

tr.rejected-row {
    background: #fff0f0
}
</style>

<div class="main-content">
    <section class="section">
        <div id="leaveFormWrap">
            <h2>Student Leave Request</h2>
            <form id="studentLeaveForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="insert">
                <input type="hidden" name="id" id="edit_id" value="">

                <div class="form-group">
                    <label>Teacher (to submit to)</label>
                    <select class="form-control" name="teacher_id" id="teacher_id" required>
                        <option value="">Loading teachers…</option>
                    </select>
                    <small class="text-muted">Only teachers who teach your class/section appear here.</small>
                </div>

                <div class="form-group">
                    <label>Leave Type</label>
                    <input type="text" class="form-control" name="leave_type" id="leave_type"
                        placeholder="e.g., Sick Leave" required>
                </div>

                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" class="form-control" name="start_date" id="start_date" required>
                </div>

                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" class="form-control" name="end_date" id="end_date" required>
                </div>

                <div class="form-group">
                    <label>Reason</label>
                    <textarea class="form-control" name="reason" id="reason" rows="3" required></textarea>
                </div>

                <button type="submit" class="btn btn-primary" id="saveBtn">Submit Leave</button>
            </form>
        </div>

        <h3>My Leave Requests</h3>
        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="leavesTable">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th style="width:140px">Actions</th>
                    </tr>
                </thead>
                <tbody id="leavesBody"></tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("apps");
    if (el) {
        el.classList.add("active");
    }
});

$(function() {

    // Load teacher list for this student
    function loadTeachers() {
        $.post('ajax/get_teachers_for_student.php', {}, function(html) {
            $('#teacher_id').html('<option value="">Select Teacher</option>' + html);
        });
    }
    loadTeachers();

    // Load all my leaves
    function loadLeaves() {
        $.post('ajax/student_leave_crud.php', {
            action: 'getAll'
        }, function(html) {
            $('#leavesBody').html(html);
            // enable bootstrap-like tooltips if you use Bootstrap, otherwise simple title works
            $('[data-toggle="tooltip"]').each(function() {
                /* noop; title attribute already works */
            });
        });
    }
    loadLeaves();

    // Form submit (insert or update)
    $('#studentLeaveForm').on('submit', function(e) {
        e.preventDefault();
        const fd = new FormData(this);
        const isEditing = $('#edit_id').val() !== '';
        fd.set('action', isEditing ? 'update' : 'insert');

        $.ajax({
            url: 'ajax/student_leave_crud.php',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function(resp) {
                alert(resp);
                $('#studentLeaveForm')[0].reset();
                $('#edit_id').val('');
                $('#saveBtn').text('Submit Leave');
                loadLeaves();
            }
        });
    });

    // Edit (only pending rows render edit buttons)
    $(document).on('click', '.editLeave', function() {
        const id = $(this).data('id');
        $.post('ajax/student_leave_crud.php', {
            action: 'getOne',
            id
        }, function(json) {
            const l = JSON.parse(json || '{}');
            if (!l.id) {
                alert('Not found or not editable.');
                return;
            }
            $('#edit_id').val(l.id);
            $('#teacher_id').val(l.teacher_id);
            $('#leave_type').val(l.leave_type);
            $('#start_date').val(l.start_date);
            $('#end_date').val(l.end_date);
            $('#reason').val(l.reason);
            $('#saveBtn').text('Update Leave');
            $('html,body').animate({
                scrollTop: 0
            }, 200);
        });
    });

    // Delete (only pending rows render delete buttons)
    $(document).on('click', '.deleteLeave', function() {
        if (!confirm('Delete this leave request?')) return;
        const id = $(this).data('id');
        $.post('ajax/student_leave_crud.php', {
            action: 'delete',
            id
        }, function(resp) {
            alert(resp);
            loadLeaves();
        });
    });

});
</script>

<?php require_once 'assets/php/footer.php'; ?>