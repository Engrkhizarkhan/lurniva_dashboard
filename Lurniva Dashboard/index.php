        <?php include_once "assets/php/header.php"?>
        <script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("dashboard");
    if (el) {
        el.classList.add("active");
    }
});
        </script>

        <!-- Main Content -->
        <div class="main-content">
            <section class="section">
                <!-- Search + Scanner -->
                <!-- <div class="row mb-4">
                    <div class="col-md-10 col-sm-9 col-12 mb-2">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search any ID or Code">
                    </div>
                    <div class="col-md-2 col-sm-3 col-12">
                        <button class="btn btn-block" onclick="openScannerModal()"
                            onmouseout="this.style.transform='scale(1)'">
                            <i class="fas fa-qrcode"></i> Scan
                        </button>
                    </div>

                </div> -->

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
                        <a href="schools-list.html" style="text-decoration: none; color: inherit;">
                            <div class="card">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row">
                                            <div class="col-lg-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Total No. Schools</h5>
                                                    <h2 class="mb-3 font-18">45</h2>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pl-0">
                                                <div class="banner-img">
                                                    <img src="assets/img/school-icon.png" alt="student">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Card 2 -->
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <a href="users.html" style="text-decoration: none; color: inherit;">
                            <div class="card">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row">
                                            <div class="col-lg-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">No. of Ussers</h5>
                                                    <h2 class="mb-3 font-18">1,490</h2>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pl-0">
                                                <div class="banner-img">
                                                    <img src="assets/img/user_icon.jpg" alt="profile">
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
                        <a href="pending-request.html" style="text-decoration: none; color: inherit;">
                            <div class="card">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row">
                                            <div class="col-lg-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Pending requests</h5>
                                                    <h2 class="mb-3 font-18">54</h2>
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
                        <a href="revenue.html" style="text-decoration: none; color: inherit;">
                            <div class="card">
                                <div class="card-statistic-4">
                                    <div class="align-items-center justify-content-between">
                                        <div class="row">
                                            <div class="col-lg-6 pr-0 pt-3">
                                                <div class="card-content">
                                                    <h5 class="font-15">Revenue Record</h5>
                                                    <h2 class="mb-3 font-18">View</h2>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 pl-0">
                                                <div class="banner-img">
                                                    <img src="assets/img/revenue icon.png" alt="diary">
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
                    <div class="col-12 col-sm-12 col-lg-12">
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
                                            <div class="col-7 col-xl-7 mb-3">Total User</div>
                                            <div class="col-5 col-xl-5 mb-3">
                                                <span class="text-big" id="totalUser">-</span>
                                            </div>
                                            <div class="col-7 col-xl-7 mb-3">Total Income</div>
                                            <div class="col-5 col-xl-5 mb-3">
                                                <span class="text-big" id="totalIncome">- PKR</span>
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
                $(function() {
                    // Initialize feather icons
                    feather.replace();

                    $.ajax({
                        url: 'ajax/get_revenue_dashboard.php',
                        method: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            console.log("Revenue API Data:", data); // ✅ Debugging

                            if (data.error) {
                                alert(data.error);
                                return;
                            }

                            // ✅ Update text values with correct IDs
                            $('#totalUser').text(data.total_users);
                            $('#totalIncome').text(data.total_income + " PKR");
                            $('#monthlyRevenue').text(data.monthly_revenue + " PKR");
                            $('#yearlyRevenue').text(data.yearly_revenue + " PKR");

                            // ✅ ApexCharts setup
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
                                    categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
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

                            var chart = new ApexCharts(document.querySelector("#RevenueChart"),
                                options);
                            chart.render();
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error:", error);
                            alert("Failed to fetch dashboard data.");
                        }
                    });
                });
                </script>

                <div class="row">
                    <!-- Chart 1 -->
                    <div class="col-12 col-sm-12 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Schools per Month</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-schools"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 2 -->
                    <div class="col-12 col-sm-12 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Users Growth</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-users"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 3 -->
                    <div class="col-12 col-sm-12 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Lurniva Development</h4>
                            </div>
                            <div class="card-body">
                                <div id="chart-dev"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                $(function() {
                    feather.replace();

                    $.ajax({
                        url: 'ajax/get_growth_charts.php',
                        method: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            console.log("Growth Data:", data);

                            // --- Chart 1: Schools per Month ---
                            new ApexCharts(document.querySelector("#chart-schools"), {
                                chart: {
                                    type: "bar",
                                    height: 250
                                },
                                series: [{
                                    name: "Schools",
                                    data: data.schools_per_month
                                }],
                                xaxis: {
                                    categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                                    ],
                                    labels: {
                                        rotate: -90, // tilt labels
                                        rotateAlways: true, // ✅ force rotation even if space available
                                        style: {
                                            fontSize: "11px", // optional: smaller font for clarity
                                        }
                                    }
                                },
                                colors: ["#6777ef"]
                            }).render();

                            // --- Chart 2: Users Growth ---
                            new ApexCharts(document.querySelector("#chart-users"), {
                                chart: {
                                    type: "line",
                                    height: 250
                                },
                                series: [{
                                        name: "Schools",
                                        data: data.schools_per_month
                                    },
                                    {
                                        name: "Students",
                                        data: data.students_per_month
                                    },
                                    {
                                        name: "Faculty",
                                        data: data.faculty_per_month
                                    }
                                ],
                                xaxis: {
                                    categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                                    ],
                                    labels: {
                                        rotate: -90, // tilt labels
                                        rotateAlways: true, // ✅ force rotation even if space available
                                        style: {
                                            fontSize: "11px", // optional: smaller font for clarity
                                        }
                                    }
                                },
                                colors: ["#28c76f", "#ff9f43", "#ea5455"]
                            }).render();

                            // --- Chart 3: Lurniva Development (Total Revenue Growth this year) ---
                            new ApexCharts(document.querySelector("#chart-dev"), {
                                chart: {
                                    type: "area",
                                    height: 250
                                },
                                series: [{
                                    name: "Revenue",
                                    data: data.revenue_per_month
                                }],
                                xaxis: {
                                    categories: ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                                        "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                                    ],
                                    labels: {
                                        rotate: -90, // tilt labels
                                        rotateAlways: true, // ✅ force rotation even if space available
                                        style: {
                                            fontSize: "11px", // optional: smaller font for clarity
                                        }
                                    }
                                },
                                colors: ["#00cfe8"],
                                fill: {
                                    type: "gradient",
                                    gradient: {
                                        shadeIntensity: 1,
                                        opacityFrom: 0.4,
                                        opacityTo: 0.1,
                                        stops: [0, 90, 100]
                                    }
                                }
                            }).render();
                        },
                        error: function(xhr, status, error) {
                            console.error("Error:", error);
                        }
                    });
                });
                </script>


            </section>
            <?php include_once "assets/php/footer.php"?>