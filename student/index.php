<script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("dashboard");
    if (el) {
        el.classList.add("active");
    }
});
</script>
<style>
/* Notification bar container */
#notificationBar {
    position: fixed;
    right: 20px;
    bottom: 20px;
    width: 300px;
    display: flex;
    flex-direction: column-reverse;
    /* New notifications appear at the bottom */
    gap: 10px;
    z-index: 9999;
}

/* Notification style */
.notification {
    background: #4caf50;
    /* green */
    color: white;
    padding: 12px 16px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    opacity: 0;
    transform: translateY(20px);
    animation: slideIn 0.3s forwards;
}

/* Animation: bottom to top */
@keyframes slideIn {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.news-bar {
    height: 40px;
    /* Height of one item */
    overflow: hidden;
    position: relative;
    background: #f5f5f5;
    border: 1px solid #ddd;
    padding-left: 10px;
}

.news-bar ul {
    list-style: none;
    padding: 0;
    margin: 0;
    position: absolute;
    width: 100%;
    animation: scrollNews 50s linear infinite;
}

.news-bar li {
    height: 40px;
    line-height: 40px;
    font-size: 14px;
    color: #333;
}

/* Animation from bottom → top */
@keyframes scrollNews {
    0% {
        top: 100%;
    }

    100% {
        top: -400%;
    }

    /* Adjust based on total items */
}
</style>

<?php require_once 'assets/php/header.php'; ?>

<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h4>Profile </h4>
        </div>

        <div class="row">
            <div class="col-md-8" id="studentInfo"></div>
            <div class="col-md-4 pb-4">
                <div class="" id="studentInfo">
                    <div class="news-bar card shadow p-4 mb-4 h-100">
                        <ul id="newsList"></ul>
                    </div>
                </div>

                <style>
                .news-bar {
                    height: 40px;
                    overflow: hidden;
                    position: relative;
                    background: #f5f5f5;
                    border: 1px solid #ddd;
                }

                .news-bar ul {
                    position: absolute;
                    margin: 0;
                    padding: 0;
                    list-style: none;
                    animation: scrollUp 40s linear infinite;
                }

                .news-bar li {
                    height: 40px;
                    line-height: 40px;
                    padding-left: 10px;
                }

                .news-bar a {
                    text-decoration: none;
                    color: #333;
                }

                /* Animation bottom → top */
                @keyframes scrollUp {
                    0% {
                        top: 100%;
                    }

                    100% {
                        top: -400%;
                    }

                    /* adjust based on number of <li> */
                }

                /* Pause on hover */
                .news-bar:hover ul {
                    animation-play-state: paused;
                }
                </style>
                <script>
                function getNotificationLink(module) {
                    const map = {
                        notice: "student_notice_board.php",
                        library: "student_library.php",
                        behavior: "StudentBehavior.php",
                        dairy: "Dairy.php",
                        exam: "student_exam_results.php",
                        assignment: "assigment-result.php",
                        test: "assigment-result.php",
                        meeting: "student_meetings.php"
                    };
                    return map[(module || "").trim().toLowerCase()] || "#";
                }

                function loadNewsBar() {
                    $.getJSON("ajax/get_news.php", res => {
                        if (res.status !== "success") return;

                        let html = "";
                        res.data.forEach(n => {
                            const link = getNotificationLink(n.module);
                            html += `<li><a href="${link}">${n.title}</a></li>`;
                        });
                        $("#newsList").html(html);
                    });
                }

                loadNewsBar();
                setInterval(loadNewsBar, 60000); // refresh every 1 min
                </script>
            </div>
        </div>


        <!-- Notification bar -->
        <div id="notificationBar"></div>

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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let shownNotifications = new Set(); // Keep track of already shown notifications

function playNotificationSound() {
    const sound = document.getElementById("notificationSound");
    sound.currentTime = 0; // reset
    sound.play();
}

function showNotification(message, type = "success") {
    const bar = document.getElementById("notificationBar");

    // Create notification element
    const note = document.createElement("div");
    note.classList.add("notification");

    // Different colors based on type
    if (type === "error") {
        note.style.background = "#f44336"; // red
    } else if (type === "warning") {
        note.style.background = "#ff9800"; // orange
    }

    note.innerText = message;
    bar.appendChild(note);

    // Auto remove after 5 seconds
    setTimeout(() => {
        note.style.opacity = "0";
        note.style.transform = "translateY(-20px)";
        setTimeout(() => note.remove(), 300);
    }, 5000);
}

// Load notifications with AJAX
function loadNotifications() {
    $.ajax({
        url: "ajax/notifications.php",
        method: "GET",
        dataType: "json",
        success: function(response) {
            if (response.success) {
                let newNotifications = 0;

                response.notifications.forEach(n => {
                    if (!shownNotifications.has(n.id)) {
                        showNotification(n.title, "success");
                        shownNotifications.add(n.id);
                        newNotifications++;
                    }
                });

                // Play sound if new notifications arrived
                if (newNotifications > 0) {
                    playNotificationSound();
                }
            }
        }
    });
}

// Load notifications on page load
loadNotifications();

// Refresh every 30 seconds
setInterval(loadNotifications, 30000);
</script>


<script>
$(function() {
    // ✅ Get student ID from POST
    let studentId = <?= $_SESSION['student_id'] ?? 0 ?>;

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