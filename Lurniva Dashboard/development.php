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
        <div class="main-content">
            <section class="section">
                <div class="text-center">
                    <h1>Lurniva Development</h1>
                    <p class="text-muted">Yearly Month-wise Progress</p>
                </div>

                <div class="card">
                    <div class="card-header">Progress Overview</div>
                    <div class="card-body">
                        <canvas id="progressChart" height="100"></canvas>
                    </div>
                </div>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
$(function() {
    $.ajax({
        url: 'ajax/get_lurniva_progress.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            const ctx = document.getElementById('progressChart').getContext('2d');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [
                        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                    ],
                    datasets: [{
                        label: 'Lurniva Progress (' + data.year + ')',
                        data: data.progress,
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 3,
                        pointBackgroundColor: '#1cc88a',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#4e73df'
                            }
                        },
                        title: {
                            display: true,
                            text: 'Lurniva Development Progress (Monthly - ' + data.year +
                                ')',
                            color: '#1cc88a',
                            font: {
                                size: 16
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: '#e0e0e0'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#e0e0e0'
                            }
                        }
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error("Error loading progress data:", error);
        }
    });
});
        </script>


        </section>
        <?php include_once "assets/php/footer.php"?>