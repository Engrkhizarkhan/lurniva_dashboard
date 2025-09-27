<?php require_once 'assets/php/header.php'; ?>
<?php
session_start();
include_once('sass/db_config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: logout.php");
    exit;
}

$school_id = $_SESSION['admin_id']; // adjust if using campus_id or student_id

// Fetch school settings
$sql = "SELECT fee_enabled FROM school_settings WHERE person = 'school' AND person_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

$settings = $result->fetch_assoc();
$stmt->close();

// 🚨 If fee is disabled
if (!$settings || $settings['fee_enabled'] == 0) {
    echo "<script>alert('Fee module is disabled by school admin.'); window.location.href='logout.php';</script>";
    exit;
}
?>

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

#feePeriodForm {
    color: #000;
}
</style>
<!-- Main Content -->
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Manage Fee Period</h2>
        </div>

        <div class="section-body">
            <form id="feePeriodForm">
                <input type="hidden" name="id" id="id">
                <input type="hidden" name="school_id" value="<?= $_SESSION['admin_id']?>">

                <div class="mb-3">
                    <label>Period Name</label>
                    <input type="text" name="period_name" id="period_name" class="form-control" required
                        placeholder="e.g. August 2025">
                </div>

                <div class="mb-3">
                    <label>Period Type</label>
                    <select name="period_type" id="period_type" class="form-control" required>
                        <option value="monthly">Monthly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="term">Term</option>
                        <option value="custom">Custom</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label>Start Date</label>
                    <input type="date" name="start_date" id="start_date" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label>End Date</label>
                    <input type="date" name="end_date" id="end_date" class="form-control" required>
                </div>

                <button type="button" id="submit" class="btn btn-primary">Save Fee Period</button>
                <div id="response" class="mt-3"></div>
            </form>

            <hr>
            <h5>All Fee Periods</h5>
            <div id="feePeriodTable"></div>
        </div>

        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script>
        // initial table load
        loadTable();

        // Save (insert/update) via explicit variables
        $('#submit').on('click', function(e) {
            e.preventDefault();

            // ✅ read values from inputs
            const id = $('#id').val().trim(); // empty => INSERT, value => UPDATE
            const school_id = $('input[name="school_id"]').val().trim(); // hidden field from PHP session
            const period_name = $('#period_name').val().trim();
            const period_type = $('#period_type').val();
            const start_date = $('#start_date').val();
            const end_date = $('#end_date').val();
            const status = 'active'; // or read from a select if you add one

            // quick front-end validation
            if (!school_id || !period_name || !period_type || !start_date || !end_date) {
                $('#response').html(`<div class="alert alert-danger">All fields are required.</div>`);
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'ajax/save_fee_period.php',
                data: {
                    id,
                    school_id,
                    period_name,
                    period_type,
                    start_date,
                    end_date,
                    status
                },
                dataType: 'json',
                success: function(res) {
                    $('#response').html(
                        `<div class="alert alert-${res.status}">${res.message}</div>`);
                    if (res.status === 'success') {
                        loadTable();
                        $('#feePeriodForm')[0].reset();
                        $('#id').val('');
                    }
                },
                error: function(xhr, status, error) {
                    $('#response').html(
                        `<div class="alert alert-danger">AJAX error: ${error}</div>`);
                    console.error('AJAX error:', xhr.responseText || error);
                }
            });
        });

        function loadTable() {
            $.get('ajax/fetch_fee_periods.php', function(data) {
                $('#feePeriodTable').html(data);
            }).fail(function(xhr, status, error) {
                console.error('Load table error:', error);
            });
        }

        // keep your edit/delete helpers
        window.editPeriod = function(id) {
            $.get('ajax/fetch_fee_periods.php?id=' + id, function(data) {
                const p = JSON.parse(data);
                $('#id').val(p.id);
                $('#period_name').val(p.period_name);
                $('#period_type').val(p.period_type);
                $('#start_date').val(p.start_date);
                $('#end_date').val(p.end_date);
            });
        };

        window.deletePeriod = function(id) {
            if (confirm('Delete this period?')) {
                $.post('ajax/delete_fee_period.php', {
                    id
                }, function(res) {
                    alert(res.message);
                    loadTable();
                }, 'json');
            }
        };
        </script>

        <?php require_once 'assets/php/footer.php'; ?>