        <?php include_once "assets/php/header.php"?>
        <script>
document.addEventListener("DOMContentLoaded", function() {
    let el = document.getElementById("chart");
    if (el) {
        el.classList.add("active");
    }
});
        </script>

        <!-- Main Content -->
        <div class="main-content container">
            <section class="section">
                <div class="section-header text-center mb-4">
                    <h1>Revenue Record</h1>
                    <p class="text-muted">Month-wise Revenue Gained by Lurniva</p>
                </div>

                <div class="row">
                    <!-- Users Revenue -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Users</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="usersChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Schools Revenue -->
                    <div class="col-lg-6 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Schools</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="schoolsChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
        </div>

        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
$(function() {
    $.ajax({
        url: 'ajax/get_revenue_record.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
            ];

            // --- Users Chart ---
            const usersCtx = document.getElementById("usersChart").getContext("2d");
            new Chart(usersCtx, {
                type: "line",
                data: {
                    labels: months,
                    datasets: [{
                        label: "Users Count (" + data.year + ")",
                        data: data.users_per_month,
                        backgroundColor: "rgba(78, 115, 223, 0.2)",
                        borderColor: "#4e73df",
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointBackgroundColor: "#4e73df"
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // --- Schools Revenue Chart ---
            const schoolsCtx = document.getElementById("schoolsChart").getContext("2d");
            new Chart(schoolsCtx, {
                type: "bar",
                data: {
                    labels: months,
                    datasets: [{
                        label: "Schools Revenue (PKR)",
                        data: data.schools_revenue,
                        backgroundColor: "rgba(28, 200, 138, 0.6)",
                        borderColor: "#1cc88a",
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return "PKR " + value;
                                }
                            }
                        }
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Error loading data:", error);
        }
    });
});
        </script>

        <?php include_once "assets/php/footer.php"?>