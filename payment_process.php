<?php
// payment_process.php (handles API request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method']; // card OR mobile
    $amount = $_POST['amount'];
    $order_id = uniqid("ORD_");

    // Credentials (replace with sandbox from Easypaisa/UBL/JazzCash)
    $merchant_id = "YOUR_MERCHANT_ID";
    $store_id    = "YOUR_STORE_ID";
    $secret_key  = "YOUR_SECRET_KEY";

    // Common data
    $currency    = "PKR";
    $return_url  = "http://yourdomain.com/payment_return.php";

    if ($payment_method === 'card') {
        // Collect card details
        $card_number = $_POST['card_number'];
        $card_expiry = $_POST['card_expiry'];
        $card_cvv    = $_POST['card_cvv'];

        // Prepare payload
        $payload = [
            "merchant_id" => $merchant_id,
            "store_id"    => $store_id,
            "order_id"    => $order_id,
            "amount"      => $amount,
            "currency"    => $currency,
            "return_url"  => $return_url,
            "card_number" => $card_number,
            "card_expiry" => $card_expiry,
            "card_cvv"    => $card_cvv,
        ];

        // Generate signature
        $signature = hash_hmac('sha256', implode("|", $payload), $secret_key);
        $payload["signature"] = $signature;

        // Send to Easypaisa API (sample endpoint)
        $ch = curl_init("https://sandbox.easypaisa.com.pk/api/card-payment");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        echo "Card Payment Response: " . $response;

    } elseif ($payment_method === 'mobile') {
        // Collect mobile wallet details
        $mobile_number = $_POST['mobile_number'];
        $pin           = $_POST['pin']; // sometimes OTP instead

        $payload = [
            "merchant_id"   => $merchant_id,
            "store_id"      => $store_id,
            "order_id"      => $order_id,
            "amount"        => $amount,
            "currency"      => $currency,
            "return_url"    => $return_url,
            "mobile_number" => $mobile_number,
            "pin"           => $pin,
        ];

        $signature = hash_hmac('sha256', implode("|", $payload), $secret_key);
        $payload["signature"] = $signature;

        $ch = curl_init("https://sandbox.easypaisa.com.pk/api/mobile-payment");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        echo "Mobile Payment Response: " . $response;
    }
}
?>