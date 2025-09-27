<?php require_once 'assets/php/header.php'; ?>
<?php
require_once 'sass/db_config.php';

$teacher_id = $_SESSION['admin_id'] ?? 0;
$school_id  = $_SESSION['campus_id'] ?? 0;


$studentCount = 0;

if ($teacher_id && $school_id) {
    $sql = "
        SELECT COUNT(DISTINCT s.id) as total_students
        FROM students s
        JOIN class_timetable_meta ctm 
            ON s.class_grade = ctm.class_name 
           AND s.section = ctm.section
        JOIN class_timetable_details ctd 
            ON ctm.id = ctd.timing_meta_id
        WHERE ctd.teacher_id = ? AND s.school_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $teacher_id, $school_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $studentCount = $res['total_students'] ?? 0;
    
// Query to count assignments
$sql = "SELECT COUNT(*) AS total_tasks FROM teacher_assignments  WHERE teacher_id =$teacher_id  AND school_id = $school_id";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

$total_tasks = $row['total_tasks']; // number of tasks

}
?>

<style>
#dashboard {
    padding-left: 20px;
    position: relative;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#dashboard svg {
    color: #6777ef !important;
}

#dashboard span {
    color: #6777ef !important;
}
</style>
<!-- Main Content -->
<div class="main-content">
    <section class="section">


        <style>
        .card:hover {
            transform: scale(1.03);
            transition: transform 0.3s ease;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            cursor: pointer;
        }
        </style>

        <div class="row">
            <!-- Card 1 -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card">
                    <div class="card-statistic-4">
                        <div class="align-items-center justify-content-between">
                            <div class="row">
                                <div class="col-lg-6 pr-0 pt-3">
                                    <div class="card-content">
                                        <h5 class="font-15">My Students</h5>
                                        <h2 class="mb-3 font-18">
                                            <?php echo $studentCount; ?>
                                        </h2>
                                    </div>
                                </div>
                                <div class="col-lg-6 pl-0">
                                    <div class="banner-img">
                                        <img src="assets/img/student.png" alt="student">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Card 2 -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <a href="students_list.php" style="text-decoration: none; color: inherit;">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row">
                                    <div class="col-lg-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15">Student Profile</h5>
                                            <h2 class="mb-3 font-18">View</h2>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 pl-0">
                                        <div class="banner-img">
                                            <img src="assets/img/stu.png" alt="profile">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Card 3 -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <a href="assignment-test.php" style="text-decoration: none; color: inherit;">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row">
                                    <div class="col-lg-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15">Assignments</h5>
                                            <h2 class="mb-3 font-18"><?php echo $total_tasks; ?> Tasks</h2>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 pl-0">
                                        <div class="banner-img">
                                            <img src="assets/img/task icon.png" alt="assignment">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>


            <!-- Card 4 -->
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <a href="Dairy.php" style="text-decoration: none; color: inherit;">
                    <div class="card">
                        <div class="card-statistic-4">
                            <div class="align-items-center justify-content-between">
                                <div class="row">
                                    <div class="col-lg-6 pr-0 pt-3">
                                        <div class="card-content">
                                            <h5 class="font-15">Diary</h5>
                                            <h2 class="mb-3 font-18">View</h2>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 pl-0">
                                        <div class="banner-img">
                                            <img src="assets/img/diary.jpg" alt="diary">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Student Progress (Attendance / Exams / Tests)</h4>
                    </div>
                    <div class="card-body">
                        <div id="TeacherProgressChart" style="min-height:350px;"></div>

                        <div class="row mt-4">
                            <div class="col-md-3">
                                <div class="list-inline text-center">
                                    <div class="list-inline-item p-r-30">
                                        <h6 class="m-b-0">Coverage</h6>
                                        <small class="text-muted">Classes taught</small>
                                        <div id="tp_classes" class="text-big">-</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="list-inline text-center">
                                    <div class="list-inline-item p-r-30">
                                        <h6 class="m-b-0">Students</h6>
                                        <small class="text-muted">Unique taught</small>
                                        <div id="tp_students" class="text-big">-</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="list-inline text-center">
                                    <div class="list-inline-item p-r-30">
                                        <h6 class="m-b-0">Current Year</h6>
                                        <small class="text-muted">Progress period</small>
                                        <div id="tp_year" class="text-big">-</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="list-inline text-center">
                                    <div class="list-inline-item p-r-30">
                                        <h6 class="m-b-0">Last Update</h6>
                                        <small class="text-muted">Server time</small>
                                        <div id="tp_updated" class="text-big">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <style>
            .news-bar {
                height: 40px;
                overflow: hidden;
                position: relative;
                background: #f9f9f9;
                border: 1px solid #ddd;
            }

            .news-bar ul {
                position: absolute;
                margin: 0;
                padding: 0;
                list-style: none;
                animation: scrollUp 30s linear infinite;
            }

            .news-bar li {
                height: 40px;
                line-height: 40px;
                padding-left: 10px;
                white-space: nowrap;
            }

            .news-bar a {
                text-decoration: none;
                color: #333;
            }

            .news-bar:hover ul {
                animation-play-state: paused;
            }

            @keyframes scrollUp {
                0% {
                    top: 100%;
                }

                100% {
                    top: -500%;
                }

                /* adjust depending on # of items */
            }
            </style>
            <div class="col-sm-12 col-md-4 col-lg-4 card shadow p-4 mb-4" id="facultyNews">
                <div class="news-bar h-100">
                    <ul id="newsList"></ul>
                </div>


                <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
                <script>
                function loadFacultyNews() {
                    $.getJSON("ajax/get_faculty_news.php", res => {
                        if (res.status !== "success") return;
                        let html = "";
                        res.data.forEach(n => {
                            html += `<li>
                                    ${n.link ? `<a href="${n.link}">${n.title}</a>` : n.title}
                                </li>`;
                        });
                        $("#newsList").html(html);
                    });
                }
                loadFacultyNews();
                setInterval(loadFacultyNews, 60000); // refresh every 1 min
                </script>

            </div>
        </div>

        <!-- jQuery (required for $.ajax) -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- ApexCharts -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <script>
        $(document).ready(function() {
            $.ajax({
                url: "ajax/get_teacher_progress.php",
                method: "GET",
                dataType: "json",
                success: function(res) {
                    if (res.status !== "success") {
                        console.error(res);
                        alert(res.message || "Failed to load teacher progress.");
                        return;
                    }

                    // Fill meta info
                    $("#tp_classes").text(res.meta.classes_count);
                    $("#tp_students").text(res.meta.students_count);
                    $("#tp_year").text(res.meta.year);
                    $("#tp_updated").text(res.meta.generated_at);

                    // Clear old chart if exists
                    $("#TeacherProgressChart").html("");

                    // Chart options
                    var options = {
                        chart: {
                            type: "line",
                            height: 350,
                            toolbar: {
                                show: false
                            }
                        },
                        stroke: {
                            curve: "smooth",
                            width: 3
                        },
                        dataLabels: {
                            enabled: false
                        },
                        series: [{
                                name: "Attendance %",
                                data: res.series.attendance
                            },
                            {
                                name: "Exam Avg %",
                                data: res.series.exams
                            },
                            {
                                name: "Test/Assignment Avg %",
                                data: res.series.tests
                            }
                        ],
                        xaxis: {
                            categories: res.categories,
                            labels: {
                                style: {
                                    colors: "#9aa0ac"
                                }
                            }
                        },
                        yaxis: {
                            min: 0,
                            max: 100,
                            tickAmount: 5,
                            title: {
                                text: "Percentage (%)"
                            },
                            labels: {
                                style: {
                                    colors: "#9aa0ac"
                                }
                            }
                        },
                        legend: {
                            position: "top",
                            horizontalAlign: "right",
                            offsetY: -5
                        },
                        tooltip: {
                            y: {
                                formatter: function(v) {
                                    return v.toFixed(2) + "%"
                                }
                            }
                        },
                        colors: ["#00b894", "#0984e3", "#e17055"]
                    };

                    var chart = new ApexCharts(document.querySelector("#TeacherProgressChart"),
                        options);
                    chart.render();
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert("Error fetching teacher progress.");
                }
            });
        });
        </script>



        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Assign Task Table</h4>
                        <div class="card-header-form">
                            <form>
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search">
                                    <div class="input-group-btn">
                                        <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Task Name</th>
                                        <th>Members</th>
                                        <th>Task Status</th>
                                        <th>Assign Date</th>
                                        <th>Due Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="tasksTableBody"></tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
        $(document).ready(function() {
            loadTasks();
        });

        function loadTasks() {
            $.ajax({
                url: 'ajax/fetch_tasks.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        let html = '';
                        response.data.forEach(function(task) {
                            html += `
                        <tr>
                            <td>${task.task_title}</td>
                            <td class="text-truncate">${task.members_html}</td>
                            <td>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: ${task.task_completed_percent}%"></div>
                                </div>
                            </td>
                            <td>${task.created_at}</td>
                            <td>${task.due_date}</td>
                            <td><form action="show_task.php" method="post" style="display:inline;" id="taskDetailForm-${task.id}">
  <input type="hidden" name="id" value="${task.id}">
  <button type="submit" class="btn btn-outline-primary">Detail</button>
</form>
</td>
                        </tr>
                    `;
                        });
                        $('#tasksTableBody').php(html);
                    } else {
                        alert(response.message);
                    }
                },
                error: function(xhr, status, error) {
                    alert("Error: " + error);
                }
            });
        }
        </script>
    </section>
    <?php require_once 'assets/php/footer.php'; ?>