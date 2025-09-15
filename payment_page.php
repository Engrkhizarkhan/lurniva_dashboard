<?php
session_start();
require_once 'admin/sass/db_config.php';

// Check if plan ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid plan selected.");
}

$plan_id = intval($_GET['id']);

// Fetch plan details
$sql = "SELECT id, plan_name, description, price, duration_days, status 
        FROM student_payment_plans WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $plan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Plan not found.");
}
$plan = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Payment Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    function togglePayment(method) {
        document.getElementById('card-fields').style.display = (method === 'card') ? 'block' : 'none';
        document.getElementById('mobile-fields').style.display = (method === 'mobile') ? 'block' : 'none';
    }
    </script>
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow p-4">
            <h4><?= htmlspecialchars($plan['plan_name']) ?></h4>
            <p><strong>Price per student:</strong> Rs<?= number_format($plan['price'], 2) ?></p>
            <p><strong>Duration:</strong> <?= $plan['duration_days'] ?> days</p>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <h3 class="mb-4">💳 Make a Payment</h3>

            <form method="post" action="payment_process.php">
                <div class="mb-3">
                    <label for="num_students" class="form-label">Number of Students</label>
                    <input type="number" class="form-control" id="num_students" name="num_students" min="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount (PKR)</label>
                    <input type="text" class="form-control" name="amount" id="total_price" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Select Payment Method</label>
                    <select class="form-select" name="payment_method" onchange="togglePayment(this.value)" required>
                        <option value="">-- Choose --</option>
                        <option value="card">Credit / Debit Card</option>
                        <option value="mobile">Easypaisa / JazzCash Wallet</option>
                    </select>
                </div>

                <!-- Card Fields -->
                <div id="card-fields" style="display:none;">
                    <div class="mb-3">
                        <label class="form-label">Card Number</label>
                        <input type="text" class="form-control" name="card_number" placeholder="4111 1111 1111 1111">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expiry (MM/YY)</label>
                        <input type="text" class="form-control" name="card_expiry" placeholder="12/25">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">CVV</label>
                        <input type="password" class="form-control" name="card_cvv" placeholder="123">
                    </div>
                </div>

                <!-- Mobile Fields -->
                <div id="mobile-fields" style="display:none;">
                    <div class="mb-3">
                        <label class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" name="mobile_number" placeholder="03XXXXXXXXX">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">PIN / OTP</label>
                        <input type="password" class="form-control" name="pin" placeholder="****">
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100">Proceed to Pay</button>
            </form>
        </div>
    </div>

    <script>
    // Auto calculate total price
    const pricePerStudent = <?= $plan['price'] ?>;
    document.getElementById('num_students').addEventListener('input', function() {
        const num = this.value;
        document.getElementById('total_price').value = num > 0 ? "Rs " + (num * pricePerStudent).toFixed(2) :
            "";
    });
    </script>
</body>

</html>