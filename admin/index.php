<?php require_once 'assets/php/header.php'; ?>

<style>
#dashboard {
    padding-left: 20px;
    position: relative;
    color: #6777ef !important;
    background-color: #f0f3ff;
}

#dashboard i {
    color: #6777ef !important;
}
</style>
<!-- Main Content -->
<div class="main-content">
    <section class="section">

        <div class="row ">
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="card">
                    <div class="card-statistic-4">
                        <div class="align-items-center justify-content-between">
                            <div class="row ">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                    <div class="card-content">
                                        <h5 class="font-15">Total No. of Students</h5>
                                        <h2 class="mb-3 font-18"></h2>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                    <div class="banner-img">
                                        <img src="assets/img/student logo.png" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="card">
                    <div class="card-statistic-4">
                        <div class="align-items-center justify-content-between">
                            <div class="row ">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                    <div class="card-content">
                                        <h5 class="font-15"> Total No. of Teachers</h5>
                                        <h2 class="mb-3 font-18">29</h2>


                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                    <div class="banner-img">
                                        <img src="assets/img/teacher logo.png" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="card">
                    <div class="card-statistic-4">
                        <div class="align-items-center justify-content-between">
                            <div class="row ">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                    <div class="card-content">
                                        <h5 class="font-15">Assignment / Tasks</h5>
                                        <h2 class="mb-3 font-18">58</h2>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                    <div class="banner-img">
                                        <img src="assets/img/task icon.png" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <div class="card">
                    <div class="card-statistic-4">
                        <div class="align-items-center justify-content-between">
                            <div class="row ">
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pr-0 pt-3">
                                    <div class="card-content">
                                        <h5 class="font-15">Revenue</h5>
                                        <h2 class="mb-3 font-18"></h2>
                                        <p class="mb-0"><span class="current-month"></span></p>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6 pl-0">
                                    <div class="banner-img">
                                        <img src="assets/img/revenue icon.png" alt="">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
        $(document).ready(function() {
            $.ajax({
                url: 'ajax/get_dashboard_stats.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    $('.card:contains("Total No. of Students") h2').text(data.total_students);
                    $('.card:contains("Total No. of Teachers") h2').text(data.total_teachers);
                    $('.card:contains("Assignment / Tasks") h2').text(data.total_tasks);
                    $('.card:contains("Revenue") h2').text(data.total_revenue);
                    $('.current-month').text('Month of ' + data.current_month);
                },


                error: function() {
                    alert('Failed to fetch dashboard data.');
                }
            });
        });
        </script>

        <div class="row">
            <div class="col-8 col-sm-12 col-lg-8">
                <div class="card ">
                    <div class="card-header">
                        <h4>Revenue chart</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-9">
                                <div id="RevenueChart"></div>
                                <div class="row mb-0">
                                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                                        <div class="list-inline text-center">
                                            <div class="list-inline-item p-r-30">
                                                <i data-feather="arrow-up-circle" class="col-green"></i>
                                                <h5 class="m-b-0" id="monthlyRevenue">- PKR</h5>
                                                <p class="text-muted font-14 m-b-0">Monthly</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                                        <div class="list-inline text-center">
                                            <div class="list-inline-item p-r-30">
                                                <i data-feather="arrow-up-circle" class="col-green"></i>
                                                <h5 class="mb-0 m-b-0" id="yearlyRevenue">- PKR</h5>
                                                <p class="text-muted font-14 m-b-0">Yearly</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="row mt-5">
                                    <div class="col-7 col-xl-7 mb-3">Total Students</div>
                                    <div class="col-5 col-xl-5 mb-3">
                                        <span class="text-big" id="totalStudents">-</span>
                                    </div>
                                    <div class="col-7 col-xl-7 mb-3">Total Income</div>
                                    <div class="col-5 col-xl-5 mb-3">
                                        <span class="text-big" id="totalIncome">- PKR</span>
                                    </div>
                                    <div class="col-7 col-xl-7 mb-3">Total Teachers</div>
                                    <div class="col-5 col-xl-5 mb-3">
                                        <span class="text-big" id="totalTeachers">-</span>
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
                background: #f5f5f5;
                border: 1px solid #ddd;
            }

            .news-bar ul {
                position: absolute;
                margin: 0;
                padding: 0;
                list-style: none;
                animation: scrollUp 120s linear infinite;
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

            @keyframes scrollUp {
                0% {
                    top: 100%;
                }

                100% {
                    top: -500%;
                }

                /* adjust for li count */
            }

            .news-bar:hover ul {
                animation-play-state: paused;
            }
            </style>
            <div class="col-sm-12 col-md-4 col-lg-4 card shadow p-4 mb-4" id="adminInfo">
                <div class="news-bar  h-100">
                    <ul id="newsList"></ul>
                </div>
            </div>


        </div>

        <script>
        function loadAdminNews() {
            $.getJSON("ajax/get_admin_news.php", res => {
                if (res.status !== "success") return;

                let html = "";
                res.data.forEach(n => {
                    if (n.link) {
                        html += `<li><a href="${n.link}" style="${n.style || ''}">${n.title}</a></li>`;
                    } else {
                        // No link (expiry warning)
                        html += `<li style="${n.style || ''}">${n.title}</li>`;
                    }
                });

                $("#newsList").html(html);

                // auto adjust animation speed depending on items
                let itemCount = res.data.length || 1;
                let duration = itemCount * 5; // 5s per item
                $(".news-bar ul").css("animation-duration", duration + "s");
            });
        }

        loadAdminNews();
        setInterval(loadAdminNews, 60000); // refresh every 1 min


        $(function() {
            // Initialize feather icons
            feather.replace();

            $.ajax({
                url: 'ajax/get_revenue_dashboard.php', // Adjust path as needed
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    // Update text values
                    $('#totalStudents').text(data.total_students);
                    $('#totalIncome').text(data.total_income + " PKR");
                    $('#totalTeachers').text(data.total_teachers);
                    $('#monthlyRevenue').text(data.monthly_revenue + " PKR");
                    $('#yearlyRevenue').text(data.yearly_revenue + " PKR");

                    // Setup ApexCharts options with dynamic data
                    var options = {
                        chart: {
                            height: 230,
                            type: "line",
                            shadow: {
                                enabled: true,
                                color: "#000",
                                top: 18,
                                left: 7,
                                blur: 10,
                                opacity: 1,
                            },
                            toolbar: {
                                show: false,
                            },
                        },
                        colors: ["#786BED"],
                        dataLabels: {
                            enabled: true,
                        },
                        stroke: {
                            curve: "smooth",
                        },
                        series: [{
                            name: "Revenue " + data.current_year,
                            data: data.monthly_revenue_data
                        }],
                        grid: {
                            borderColor: "#e7e7e7",
                            row: {
                                colors: ["#f3f3f3", "transparent"],
                                opacity: 0.0,
                            },
                        },
                        markers: {
                            size: 6,
                        },
                        xaxis: {
                            categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug",
                                "Sep", "Oct", "Nov", "Dec"
                            ],
                            labels: {
                                style: {
                                    colors: "#9aa0ac",
                                },
                            },
                        },
                        yaxis: {
                            title: {
                                text: "Income (PKR)",
                            },
                            labels: {
                                style: {
                                    color: "#9aa0ac",
                                },
                            },
                            min: 0,
                        },
                        legend: {
                            position: "top",
                            horizontalAlign: "right",
                            floating: true,
                            offsetY: -25,
                            offsetX: -5,
                        },
                    };

                    var chart = new ApexCharts(document.querySelector("#RevenueChart"), options);
                    chart.render();
                },
                error: function() {
                    alert("Failed to fetch dashboard data.");
                }
            });
        });
        </script>

        <!-- 
        <div class="row">
            <div class="col-12 col-sm-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Chart</h4>
                    </div>
                    <div class="card-body">
                        <div id="chart4" class="chartsh"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Chart</h4>
                    </div>
                    <div class="card-body">
                        <div class="summary">
                            <div class="summary-chart active" data-tab-group="summary-tab" id="summary-chart">
                                <div id="chart3" class="chartsh"></div>
                            </div>
                            <div data-tab-group="summary-tab" id="summary-text">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Chart</h4>
                    </div>
                    <div class="card-body">
                        <div id="chart2" class="chartsh"></div>
                    </div>
                </div>
            </div>
        </div> -->
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
                        $('#tasksTableBody').html(html);
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