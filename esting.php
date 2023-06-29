<!DOCTYPE html>
<html lang="en">
<?php
include("connection/connect.php");
include_once 'product-action.php';
error_reporting(0);
session_start();

// Content Security Policy (CSP) header
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");

// Generate and store CSRF token in the session
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Verify CSRF token on form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        // CSRF token mismatch, handle the error or redirect to an error page
        exit("Invalid CSRF token!");
    }
}

include("connection/connect.php");
include_once 'product-action.php';
error_reporting(0);

function function_alert()
{
    echo "<script>alert('Thank you. Your Order has been placed!');</script>";
    echo "<script>window.location.replace('your_orders.php');</script>";
}

if (empty($_SESSION["user_id"])) {
    header('location:login.php');
} else {
    foreach ($_SESSION["cart_item"] as $item) {
        $item_total += ($item["price"] * $item["quantity"]);

        if ($_POST['submit']) {
            $encrypted_card_number = encrypt($_POST['card-number']);
            $encrypted_card_holder_name = encrypt($_POST['card-holder-name']);
            $encrypted_expiry_month = encrypt($_POST['expiry-month']);
            $encrypted_expiry_year = encrypt($_POST['expiry-year']);
            $encrypted_cvv = encrypt($_POST['cvv']);

            $SQL = "INSERT INTO users_orders(u_id, title, quantity, price, card_number, card_holder_name, expiry_month, expiry_year, cvv) VALUES('" . $_SESSION["user_id"] . "','" . $item["title"] . "','" . $item["quantity"] . "','" . $item["price"] . "','" . $encrypted_card_number . "','" . $encrypted_card_holder_name . "','" . $encrypted_expiry_month . "','" . $encrypted_expiry_year . "','" . $encrypted_cvv . "')";
            mysqli_query($db, $SQL);

            unset($_SESSION["cart_item"]);
            unset($item["title"]);
            unset($item["quantity"]);
            unset($item["price"]);
            $success = "Thank you. Your order has been placed!";
            function_alert();
        }
    }
}

// Function to encrypt data using AES
function encrypt($data)
{
    $key = bin2hex(random_bytes(32)); // Replace with your own encryption key
    $cipher = "aes-256-cbc";
    $options = OPENSSL_RAW_DATA;
    $iv_length = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($iv_length);
    $encrypted = openssl_encrypt($data, $cipher, $key, $options, $iv);
    $result = base64_encode($iv . $encrypted);
    return $result;
}

// Function to decrypt data using AES
function decrypt($data)
{
    $key = bin2hex(random_bytes(32)); // Replace with your own encryption key
    $cipher = "aes-256-cbc";
    $options = OPENSSL_RAW_DATA;
    $iv_length = openssl_cipher_iv_length($cipher);
    $decoded = base64_decode($data);
    $iv = substr($decoded, 0, $iv_length);
    $encrypted_payload = substr($decoded, $iv_length);
    $result = openssl_decrypt($encrypted_payload, $cipher, $key, $options, $iv);
    return $result;
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include('includes/header.php'); ?>

    <div class="container">
        <h1>Checkout</h1>
        <div class="row">
            <div class="col-md-6">
                <h4>Billing Details</h4>
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="form-group">
                        <label for="card-number">Card Number</label>
                        <input type="text" class="form-control" name="card-number" id="card-number" required>
                    </div>
                    <div class="form-group">
                        <label for="card-holder-name">Card Holder Name</label>
                        <input type="text" class="form-control" name="card-holder-name" id="card-holder-name" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="expiry-month">Expiry Month</label>
                            <input type="text" class="form-control" name="expiry-month" id="expiry-month" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="expiry-year">Expiry Year</label>
                            <input type="text" class="form-control" name="expiry-year" id="expiry-year" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="cvv">CVV</label>
                        <input type="text" class="form-control" name="cvv" id="cvv" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="submit">Place Order</button>
                </form>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>

    <script src="js/bootstrap.min.js"></script>
</body>

</html>
