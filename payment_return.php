<?php
session_start();
require_once 'admin/sass/db_config.php';

// JazzCash credentials
$integrity_salt = "YOUR_INTEGRITY_SALT"; // from JazzCash

// JazzCash sends data back as $_POST
$response_data = $_POST;

// Log callback (for debugging)
file_put_contents("jazzcash_callback_log.txt", date("Y-m-d H:i:s") . " - " . json_encode($response_data) . "\n", FILE_APPEND);

if (empty($response_data)) {
    die("No response received from JazzCash.");
}

// Extract values
$txn_ref   = $response_data["pp_TxnRefNo"] ?? '';
$response_code = $response_data["pp_ResponseCode"] ?? '';
$response_msg  = $response_data["pp_ResponseMessage"] ?? '';
$amount   = $response_data["pp_Amount"] ?? '';
$order_id = $response_data["pp_TxnRefNo"] ?? '';

// ✅ Verify Secure Hash (important for security)
$received_hash = $response_data["pp_SecureHash"] ?? '';
unset($response_data["pp_SecureHash"]);

ksort($response_data);
$hash_string = $integrity_salt . '&' . implode('&', $response_data);
$calculated_hash = hash_hmac('sha256', $hash_string, $integrity_salt);

// Check if response is genuine
if ($received_hash !== $calculated_hash) {
    die("<h2 style='color:red;'>⚠️ Invalid response received (Hash mismatch)</h2>");
}

// ✅ Handle success/failure
if ($response_code === "000") {
    // Payment success
    echo "<h2 style='color:green;'>✅ Payment Successful</h2>";
    echo "<p>Transaction Ref: {$txn_ref}</p>";
    echo "<p>Amount: PKR " . ($amount/100) . "</p>";

    // Update DB (set order status Paid)
    $sql = "UPDATE student_plan_orders SET status = 'Paid' WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();

} else {
    // Payment failed
    echo "<h2 style='color:red;'>❌ Payment Failed</h2>";
    echo "<p>Reason: {$response_msg}</p>";

    // Update DB (set order status Failed)
    $sql = "UPDATE student_plan_orders SET status = 'Failed' WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
}
?>
