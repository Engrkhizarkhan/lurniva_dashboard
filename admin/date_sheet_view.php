<?php require_once 'assets/php/header.php'; 
include_once('sass/db_config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: logout.php");
    exit;
}

$school_id = $_SESSION['admin_id'];

$sql = "SELECT exam_enabled FROM school_settings WHERE person='admin' AND person_id=? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();
$settings = $result->fetch_assoc();
$stmt->close();

if (!$settings || $settings['exam_enabled'] == 0) {
    echo "<script>alert('Exam module is disabled by school admin.'); window.location.href='logout.php';</script>";
    exit;
}
?>

<style>
#timetable {
    padding-left: 20px;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#timetable ul {
    display: block !important;
}

#seeDSV {
    color: #000;
}

@media print {
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
    }

    #examSelect,
    #classSelect,
    #print,
    .sidebar,
    .navbar,
    .footer {
        display: none !important;
    }

    @page {
        size: A4 portrait;
        margin: 1cm;
    }

    table {
        page-break-inside: avoid !important;
    }
}
</style>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h2>Exam Date Sheet</h2>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label>Exam</label>
                <select id="examSelect" class="form-control">
                    <option value="">-- Select Exam --</option>
                </select>
            </div>
            <div class="col-md-4">
                <label>Class</label>
                <select id="classSelect" class="form-control">
                    <option value="">-- Select Class --</option>
                </select>
            </div>
        </div>

        <button id="print" class="btn btn-success mb-3">🖨️ Print Date Sheet</button>

        <div id="printArea">
            <div id="dateSheetContainer"></div>
        </div>
    </section>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    function loadExamsDropdown() {
        $.get("ajax/get_exams_school.php", function(res) {
            if (res.status == "success") {
                $("#examSelect").empty().append("<option value=''>Select Exam</option>");
                res.data.forEach(exam => {
                    $("#examSelect").append(
                        `<option value="${exam.id}">${exam.exam_name} (Total: ${exam.total_marks})</option>`
                    );
                });
            }
        }, "json");
    }

    // call on page load
    loadExamsDropdown();



    // ✅ Load Classes
    $.getJSON("ajax/get_classes.php", function(res) {
        if (res.status === 'success') {
            res.data.forEach(cls => {
                $('#classSelect').append(`<option value="${cls}">${cls}</option>`);
            });
        }
    });

    // ✅ Load Date Sheet on filter change
    $('#examSelect, #classSelect').on('change', function() {
        let exam = $('#examSelect').val();
        let cls = $('#classSelect').val();

        if (exam && cls) {
            $.post("ajax/get_date_sheet.php", {
                exam_name: exam,
                class_name: cls
            }, function(response) {
                $('#dateSheetContainer').html(response);
            });
        }
    });

    // ✅ Print
    $('#print').on('click', function() {
        window.print();
    });
});
</script>
<?php require_once 'assets/php/footer.php'; ?>