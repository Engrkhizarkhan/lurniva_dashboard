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

#FeeSlip {
    color: #000;
}

@media print {
    .row {
        display: flex !important;
        flex-wrap: wrap !important;
        margin-right: -15px;
        margin-left: -15px;
    }

    .col-md-3 {
        flex: 0 0 25% !important;
        max-width: 25% !important;
        padding: 0 15px;
        box-sizing: border-box;
    }

    .col-md-4 {
        flex: 0 0 33.3333% !important;
        max-width: 33.3333% !important;
    }

    .col-md-6 {
        flex: 0 0 50% !important;
        max-width: 50% !important;
    }

    .col-md-12 {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }

    .bg-primary {
        background-color: #6777ef !important;
        /* Otika's primary */
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color: white !important;
    }

    .text-white {
        color: white !important;
    }

    .card-header {
        padding: 10px;
        text-align: center;
    }


    body * {
        visibility: hidden !important;
    }

    #printArea,
    #printArea * {
        visibility: visible !important;
    }

    #printArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        padding: 0;
        margin: 0;
    }

    #feePeriodSelect,
    #classSelect,
    #studentSelect,
    #print,
    .sidebar,
    .navbar,
    .footer {
        display: none !important;
    }

    /* Page size optimization */
    @page {
        size: A4 portrait;
        margin: 1cm;
    }

    html,
    body {
        height: auto !important;
        font-size: 11pt;
        line-height: 1.3;
    }

    table {
        page-break-inside: avoid !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }

    .card-body,
    .card-header {
        padding: 10px !important;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        margin: 0;
        padding: 0;
    }
}
</style>

<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Fee Type Form</h2>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label>Fee Period</label>
                <select id="feePeriodSelect" class="form-control">
                    <option value="">-- Select Period --</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Class</label>
                <select id="classSelect" class="form-control">
                    <option value="">-- Select Class --</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Student</label>
                <select id="studentSelect" class="form-control">
                    <option value="">-- Select Student --</option>
                </select>
            </div>
        </div>

        <button id="print" class="btn btn-success mb-3">🖨️ Print All Fee Slips</button>

        <div id="printArea">
            <div id="unsubmittedFeeSlips"></div>
        </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // Load periods
    $.get("ajax/get_fee_periods.php", function(data) {
        $('#feePeriodSelect').append(data);
    });

    // Load classes
    $.getJSON("ajax/get_classes.php", function(res) {
        if (res.status === 'success') {
            res.data.forEach(cls => {
                $('#classSelect').append(`<option value="${cls}">${cls}</option>`);
            });
        }
    });

    // Load students
    $.get("ajax/get_students.php", function(response) {
        if (response.status === 'success') {
            let select = $('#studentSelect');
            select.html('<option value="">-- Select Student --</option>');
            response.data.forEach(s => {
                select.append(
                    `<option value="${s.id}">${s.name} (${s.class} - Roll #${s.roll})</option>`
                );
            });
        }
    });

    // Load slips on filter change
    $('#feePeriodSelect, #classSelect, #studentSelect').on('change', function() {
        let period = $('#feePeriodSelect').val();
        let cls = $('#classSelect').val();
        let student = $('#studentSelect').val();

        $.post("ajax/get_unsubmitted_fees.php", {
            period_id: period,
            class_name: cls,
            student_id: student
        }, function(response) {
            $('#unsubmittedFeeSlips').html(response);
        });
    });

    // Print
    document.getElementById('print').addEventListener('click', function() {
        window.print();
    });
});
</script>
<?php require_once 'assets/php/footer.php'; ?>