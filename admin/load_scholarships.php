<?php 
require_once 'assets/php/header.php'; 

?>

<style>
#fee_type {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#fee_type svg {
    color: #6777ef !important;
}

#fee_type span {
    color: #6777ef !important;
}

#fee_type ul {
    display: block !important;
}

#load_scholarships {
    color: #000;
}
</style>
<div class="main-content">
    <div class="container">
        <h2>Scholarships</h2>
        <div id="scholarship_list"></div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editScholarshipModal" tabindex="-1" aria-labelledby="editScholarshipLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="update_scholarship_form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editScholarshipLabel">Edit Scholarship</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" id="edit_id" name="id">
                        <input type="hidden" id="edit_school_id" name="school_id" value="<?= $school_id; ?>">

                        <div class="mb-3">
                            <label>Type</label>
                            <select class="form-control" id="edit_type" name="type" required>
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Amount (<span id="amount_type_label">%</span>)</label>
                            <input type="number" class="form-control" id="edit_amount" name="amount" required>
                        </div>

                        <div class="mb-3">
                            <label>Reason</label>
                            <textarea class="form-control" id="edit_reason" name="reason" required></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Load all scholarships
    function loadScholarships() {
        $.ajax({
            url: 'ajax/load_scholarships.php',
            method: 'GET',
            success: function(data) {
                $('#scholarship_list').html(data);
            }
        });
    }

    loadScholarships();

    // Show % or amount label based on type
    $('#edit_type').on('change', function() {
        $('#amount_type_label').text($(this).val() === 'percentage' ? '%' : 'Amount');
    });

    // Handle delete
    $(document).on('click', '.delete-btn', function() {
        if (!confirm('Are you sure you want to delete this scholarship?')) return;
        const id = $(this).data('id');
        $.post('ajax/delete_scholarship.php', {
            id
        }, function(res) {
            if (res.status === 'success') loadScholarships();
            else alert('Error: ' + res.message);
        }, 'json');
    });

    // Load data into modal
    $(document).on('click', '.edit-btn', function() {
        const id = $(this).data('id');
        $.getJSON('ajax/get_scholarship.php', {
            id
        }, function(data) {
            $('#edit_id').val(data.id);
            $('#edit_type').val(data.type).trigger('change');
            $('#edit_amount').val(data.amount);
            $('#edit_reason').val(data.reason);
            new bootstrap.Modal(document.getElementById('editScholarshipModal')).show();
        });
    });

    // Submit update form
    $('#update_scholarship_form').submit(function(e) {
        e.preventDefault();
        $.post('ajax/update_scholarship.php', $(this).serialize(), function(res) {
            if (res.status === 'success') {
                loadScholarships();
                bootstrap.Modal.getInstance(document.getElementById('editScholarshipModal'))
                    .hide();
            } else {
                alert('Error: ' + res.message);
            }
        }, 'json').fail(function(xhr, status, error) {
            console.error("AJAX Error:", xhr.responseText);
        });
    });
});
</script>
<?php require_once 'assets/php/footer.php'; ?>