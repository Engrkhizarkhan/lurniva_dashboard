<?php require_once 'assets/php/header.php'; ?>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h4>Student Profile </h4>
        </div>

        <div id="studentInfo"></div>

        <div class="section-header">
            <h4>Subjects</h4>
        </div>
        <div class="row" id="subjectsContainer"></div>

        <div class="section-header">
            <h4>Performance</h4>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 col-6" id="lineChartContainer" style="display:none;">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Line Chart</h4>
                    <button class="btn btn-danger btn-sm" onclick="$('#lineChartContainer').hide();">×</button>
                </div>
                <div class="card-body">
                    <div id="lineChart" style="height:400px;"></div>
                </div>
            </div>
        </div>

        <div class="card shadow p-4 mb-4">
            <div id="barChart" style="height:400px"></div>
        </div>
        <div class="card shadow p-4 mb-4">
            <h4>Attendance Report (Line Chart)</h4>
            <div id="attendanceLineChart" style="height:400px"></div>
        </div>
    </section>
</div>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.amcharts.com/lib/4/core.js"></script>
<script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
<script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

<script>
$(function() {
    // ✅ Get student ID from POST
    let studentId = <?= $_POST['id'] ?? 0 ?>;

    if (studentId == 0) {
        alert("No student selected!");
        return;
    }

    // ✅ Fetch profile data via AJAX
    $.ajax({
        url: "ajax/student_profile.php",
        type: "POST",
        data: {
            id: studentId
        },
        dataType: "json",
        success: function(res) {
            if (res.status === "success") {
                renderStudent(res.student, res.class, res.teacher);
                renderSubjects(res.subjects);
                renderChart(res.performance);

                // ✅ Load attendance line chart after profile
                loadAttendanceLineChart(studentId);
            } else {
                alert(res.message);
            }
        },
        error: function() {
            alert("AJAX request failed.");
        }
    });
});

// ✅ Render student info
function renderStudent(s, c, teacher) {
    $("#studentInfo").html(`
        <div class="card shadow p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <img src="uploads/profile/${s.profile_photo || 'assets/img/default.png'}"
                        class="img-fluid rounded-circle mb-3" 
                        style="max-width:150px;border:4px solid #ddd;">
                </div>
                <div class="col-md-9">
                    <p><strong>Full Name:</strong> ${s.full_name}</p>
                    <p><strong>Father's Name:</strong> ${s.parent_name}</p>
                    <p><strong>Roll No:</strong> ${s.roll_number}</p>
                    <p><strong>CNIC:</strong> ${s.cnic_formb}</p>
                    <p><strong>Phone:</strong> ${s.phone}</p>
                    <p><strong>City:</strong> ${s.city}</p>
                    <p><strong>Address:</strong> ${s.address}</p>
                    <p><strong>Class:</strong> ${c.class_grade} - ${c.section}</p>
                    <p><strong>Class Teacher:</strong> ${teacher}</p>
                </div>
            </div>
        </div>
    `);
}

// ✅ Render subjects
function renderSubjects(subjects) {
    let colors = ['l-bg-green', 'l-bg-cyan', 'l-bg-orange', 'l-bg-purple', 'l-bg-red'];
    $("#subjectsContainer").empty();

    subjects.forEach((sub, i) => {
        let color = colors[i % colors.length];
        $("#subjectsContainer").append(`
            <div class="col-xl-3 col-lg-6">
                <a href="javascript:void(0)" 
                   class="card ${color} subject-card d-block p-3 mb-3"
                   data-id="${sub.id}" 
                   data-name="${sub.period_name}" 
                   data-teacher="${sub.teacher_name}">
                    <h4>${sub.period_name}</h4>
                    <p>${sub.teacher_name}</p>
                    <div class="text-warning">
                        ${'★'.repeat(sub.rating)}${'☆'.repeat(5 - sub.rating)}
                    </div>
                </a>
            </div>
        `);
    });

    // ✅ Subject card click event
    $(document).on("click", ".subject-card", function() {
        let subject = $(this).data("name");
        let studentId = <?= $_POST['id'] ?? 0 ?>;

        $.ajax({
            url: "ajax/get_subject_results.php",
            type: "POST",
            data: {
                student_id: studentId,
                subject: subject
            },
            success: function(response) {
                let chartData = JSON.parse(response);
                $("#lineChartContainer").show();
                lineChart(chartData);
            }
        });
    });
}

// ✅ Render line chart (per subject performance)
function lineChart(chartData) {
    am4core.useTheme(am4themes_animated);
    var chart = am4core.create("lineChart", am4charts.XYChart);

    chart.data = chartData;

    var dateAxis = chart.xAxes.push(new am4charts.DateAxis());
    var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());

    var series = chart.series.push(new am4charts.LineSeries());
    series.dataFields.valueY = "value";
    series.dataFields.dateX = "date";
    series.tooltipText = "{title}: {value}";
    series.strokeWidth = 2;

    chart.cursor = new am4charts.XYCursor();
    chart.scrollbarX = new am4core.Scrollbar();
}

// ✅ Render performance chart (bar)
function renderChart(data) {
    am4core.useTheme(am4themes_animated);
    var chart = am4core.create("barChart", am4charts.XYChart);

    chart.data = data.map(p => ({
        subject: p.subject,
        marks: parseFloat(p.marks)
    }));

    var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "subject";

    var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.min = 0;
    valueAxis.max = 100;

    var series = chart.series.push(new am4charts.ColumnSeries());
    series.dataFields.valueY = "marks";
    series.dataFields.categoryX = "subject";
    series.tooltipText = "[{categoryX}]: [bold]{valueY}%[/]";

    chart.cursor = new am4charts.XYCursor();
}

// ✅ Load attendance line chart (by fee periods)
function loadAttendanceLineChart(studentId) {
    $.ajax({
        url: "ajax/get_attendance.php", // <-- PHP must return fee periods
        type: "POST",
        data: {
            studentId: studentId
        },
        dataType: "json",
        success: function(res) {
            if (!res || res.length === 0) {
                alert("No attendance data found.");
                return;
            }
            drawAttendanceLineChart(res);
        },
        error: function(xhr, status, error) {
            alert("Error: " + error + "\nResponse: " + xhr.responseText);
        }
    });
}

function drawAttendanceLineChart(data) {
    am4core.useTheme(am4themes_animated);
    var chart = am4core.create("attendanceLineChart", am4charts.XYChart);

    chart.data = data;

    var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
    categoryAxis.dataFields.category = "period"; // ✅ use fee period
    categoryAxis.title.text = "Fee Periods";

    var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
    valueAxis.title.text = "Days";

    function createSeries(field, name, color) {
        var series = chart.series.push(new am4charts.LineSeries());
        series.dataFields.valueY = field;
        series.dataFields.categoryX = "period";
        series.name = name;
        series.strokeWidth = 2;
        series.tooltipText = "{name}: [bold]{valueY}[/]";
        series.stroke = am4core.color(color);
        series.bullets.push(new am4charts.CircleBullet());
    }

    createSeries("present", "Present", "#28a745"); // green
    createSeries("absent", "Absent", "#dc3545"); // red
    createSeries("leave", "Leave", "#17a2b8"); // blue
    createSeries("missing", "Missing", "#ffc107"); // yellow

    chart.legend = new am4charts.Legend();
    chart.cursor = new am4charts.XYCursor();
}
</script>

<?php require_once 'assets/php/footer.php'; ?>