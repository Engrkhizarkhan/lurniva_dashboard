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

#submit_student_fee {
    color: #000;
}
</style>
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Submit Student Fee</h1>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Fee Payment Form</h4>
                </div>
                <div class="card-body">
                    <form id="feeSubmitForm">
                        <div class="form-group">
                            <label for="fee_period_id">Fee Period</label>
                            <select class="form-control selectric" name="fee_period_id" id="fee_period_id" required>
                                <option value="">-- Select Period --</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="student_id">Student</label>
                            <select class="form-control selectric" name="student_id" id="student_id" required>
                                <option value="">-- Select Student --</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="amount_paid">Amount Paid</label>
                            <input type="number" class="form-control" name="amount_paid" id="amount_paid" required
                                min="1" placeholder="Enter amount paid">
                        </div>

                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select class="form-control selectric" name="payment_method" id="payment_method" required>
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Online">Online</option>
                            </select>
                        </div>

                        <div class="form-group text-end">
                            <button type="submit" class="btn btn-primary">Submit Fee</button>
                        </div>

                        <div id="response" class="mt-3 text-success fw-bold"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // On student change → fetch net fee
    $('#student_id').on('change', function() {
        const studentId = $(this).val();
        const periodId = $('#fee_period_id').val();

        if (studentId) {
            $.post('ajax/get_net_payable.php', {
                student_id: studentId,
                fee_period_id: periodId
            }, function(res) {
                if (res.status === 'success') {
                    $('#amount_paid').val(res.net_amount);
                    $('#netFeeText').html(
                        `Total: <strong>Rs. ${res.total}</strong> — Scholarship: <strong>Rs. ${res.scholarship}</strong>`
                    );
                } else {
                    $('#amount_paid').val('');
                    $('#netFeeText').text(res.message);
                }
            }, 'json');
        } else {
            $('#amount_paid').val('');
            $('#netFeeText').text('');
        }
    });

    // Load Fee Periods
    $.get("ajax/get_fee_periods.php", function(data) {
        $('#fee_period_id').append(data);
    });

    // Load Students
    $.getJSON("ajax/get_students.php", function(res) {
        if (res.status === "success") {
            res.data.forEach(s => {
                $('#student_id').append(
                    `<option value="${s.id}">${s.name} (${s.class} - Roll #${s.roll})</option>`
                );
            });
        }
    });

    // Submit Form
    $('#feeSubmitForm').on('submit', function(e) {
        e.preventDefault();
        $.post('ajax/submit_fee.php', $(this).serialize(), function(res) {
            $('#response')
                .removeClass('text-danger text-success')
                .addClass(res.status === 'success' ? 'text-success' : 'text-danger')
                .text(res.message);
            if (res.status === 'success') $('#feeSubmitForm')[0].reset();
        }, 'json');
    });
});
</script>

<?php require_once 'assets/php/footer.php'; ?>