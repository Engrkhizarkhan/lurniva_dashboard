<?php require_once 'assets/php/header.php'; ?>
<style>
#fee_type {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#fee_type ul {
    display: block !important;
}

#fee_type svg {
    color: #6777ef !important;
}

#fee_type span {
    color: #6777ef !important;
}

#Installments {
    color: #000;
}
</style>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Create Fee Installments</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Installment Setup</h4>
                </div>
                <div class="card-body">
                    <form id="installmentForm">
                        <div class="form-group">
                            <label for="student_id">Select Student</label>
                            <select class="form-control" name="student_id" id="student_id" required>
                                <option value="">-- Select Student --</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fee_period_id">Select Fee Period</label>
                            <select class="form-control" name="fee_period_id" id="fee_period_id" required>
                                <option value="">-- Select Period --</option>
                            </select>
                        </div>

                        <button type="button" id="createInstallments" class="btn btn-info">
                            ➕ Create Fee Installments
                        </button>

                        <div id="installmentInputs" class="mt-4"></div>

                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary">Save Installments</button>
                        </div>
                        <div id="response" class="mt-2 fw-bold"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // Load Students
    $.getJSON("ajax/get_students.php", function(res) {
        if (res.status === "success") {
            res.data.forEach(s => {
                $('#student_id').append(
                    `<option value="${s.id}">${s.name} (${s.class})</option>`);
            });
        }
    });

    // Load Fee Periods
    $.get("ajax/get_fee_periods.php", function(data) {
        $('#fee_period_id').append(data);
    });

    // When Create Installments button is clicked
    $('#createInstallments').on('click', function() {
        let html = `
            <h5>Define Installments</h5>
            <div class="installment-group mb-3">
                <label>Amount</label>
                <input type="number" step="0.01" name="amount[]" class="form-control" required>
                <label>Due Date</label>
                <input type="date" name="due_date[]" class="form-control" required>
            </div>
            <button type="button" id="addMore" class="btn btn-sm btn-secondary">➕ Add Another Installment</button>
        `;
        $('#installmentInputs').html(html);
    });

    // Add more installments
    $(document).on('click', '#addMore', function() {
        $('#installmentInputs').append(`
            <div class="installment-group mb-3">
                <label>Amount</label>
                <input type="number" step="0.01" name="amount[]" class="form-control" required>
                <label>Due Date</label>
                <input type="date" name="due_date[]" class="form-control" required>
            </div>
        `);
    });

    // Submit installments
    $('#installmentForm').on('submit', function(e) {
        e.preventDefault();
        $.post("ajax/save_installments.php", $(this).serialize(), function(res) {
            $('#response')
                .removeClass('text-danger text-success')
                .addClass(res.status === 'success' ? 'text-success' : 'text-danger')
                .text(res.message);
            if (res.status === 'success') $('#installmentForm')[0].reset();
        }, 'json');
    });
});
</script>
<?php require_once 'assets/php/footer.php'; ?>