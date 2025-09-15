<?php
// DB connection
session_start();
require_once 'admin/sass/db_config.php';

// Fetch all payment plans
$sql = "SELECT id, plan_name, description, price, duration_days, status FROM student_payment_plans ORDER BY price ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Student Payment Plans</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    body {
        background-color: #f8f9fa;
    }

    .pricing-card {
        border-radius: 10px;
        border: 2px solid #e5e5e5;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .pricing-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
    }

    .pricing-price {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .pricing-duration {
        font-size: 0.9rem;
        color: #6c757d;
    }

    .pricing-features {
        text-align: left;
        margin: 15px 0;
    }

    .pricing-features li {
        margin-bottom: 6px;
    }

    .btn-plan {
        width: 100%;
        border-radius: 25px;
        padding: 10px;
        font-weight: bold;
    }

    .active-plan {
        border-color: #0d6efd;
    }
    </style>
</head>

<body>
    <div class="container py-5">
        <h2 class="text-center mb-5">💳 Student Payment Plans</h2>
        <div class="row g-4 justify-content-center">
            <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card pricing-card <?= $row['status'] === 'Active' ? 'active-plan' : '' ?>">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= htmlspecialchars($row['plan_name']) ?></h5>
                        <div class="pricing-price">Rs<?= number_format($row['price'], 2) ?></div>
                        <div class="pricing-duration">for <?= $row['duration_days'] ?> day(s)</div>

                        <ul class="pricing-features list-unstyled">
                            <?php if ($row['description']): ?>
                            <?php foreach (explode("\n", $row['description']) as $line): ?>
                            <li>✅ <?= htmlspecialchars(trim($line)) ?></li>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <li>✅ Standard benefits</li>
                            <?php endif; ?>
                        </ul>

                        <a href="payment_page.php?id=<?= $row['id'] ?>" class="btn btn-plan btn-primary">
                            Choose Plan
                        </a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
            <?php else: ?>
            <div class="col-12 text-center text-muted">No payment plans available.</div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
<?php $conn->close(); ?>