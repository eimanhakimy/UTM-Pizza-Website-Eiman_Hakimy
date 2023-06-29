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

function function_alert()
{
    echo "<script>alert('Thank you. Your Order has been placed!');</script>";
    echo "<script>window.location.replace('your_orders.php');</script>";
}

if (empty($_SESSION["user_id"])) {
    header('location:login.php');
} else {
    $item_total = 0;

    foreach ($_SESSION["cart_item"] as $item) {
        $item_total += ($item["price"] * $item["quantity"]);
    }

    if (isset($_POST['submit'])) {
        // Get the credit card information from the form
        $card_number = $_POST["card_number"];
        $card_holder = $_POST["card_holder"];
        $expiry_date = $_POST["expiry_date"];
        $cvv = $_POST["cvv"];

        // Encrypt the credit card information
        $encrypted_card_number = encrypt($card_number);
        $encrypted_card_holder = encrypt($card_holder);
        $encrypted_expiry_date = encrypt($expiry_date);
        $encrypted_cvv = encrypt($cvv);

        // Insert the encrypted credit card information into the database
        $query = "INSERT INTO credit_cards (user_id, card_number, card_holder, expiry_date, cvv) 
                  VALUES ('" . $_SESSION["user_id"] . "', 
                          '" . $encrypted_card_number . "', 
                          '" . $encrypted_card_holder . "', 
                          '" . $encrypted_expiry_date . "', 
                          '" . $encrypted_cvv . "')";

        if (mysqli_query($db, $query)) {
            unset($_SESSION["cart_item"]);
            $success = "Thank you. Your order has been placed!";
            function_alert();
        } else {
            echo "Error: " . $query . "<br>" . mysqli_error($db);
        }
    }
}
?>


<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="#">
    <title>Checkout || Online Food Ordering System - Code Camp BD</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="css/animsition.min.css" rel="stylesheet">
    <link href="css/animate.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <div class="site-wrapper">
        <header id="header" class="header-scroll top-header headrom">
            <!-- Navbar content goes here -->
            <nav class="navbar navbar-dark">
                <div class="container">
                    <button class="navbar-toggler hidden-lg-up" type="button" data-toggle="collapse" data-target="#mainNavbarCollapse">&#9776;</button>
                    <a class="navbar-brand" href="index.php"> <img class="img-rounded" src="images\UTM-LOGO.png" alt="" width="7%"> </a>
                    <div class="collapse navbar-toggleable-md  float-lg-right" id="mainNavbarCollapse">
                        <ul class="nav navbar-nav">
                            <li class="nav-item"> <a class="nav-link active" href="index.php">Home <span class="sr-only">(current)</span></a> </li>
                            <li class="nav-item"> <a class="nav-link active" href="restaurants.php">Restaurants <span class="sr-only"></span></a> </li>

                            <?php
						if(empty($_SESSION["user_id"]))
							{
								echo '<li class="nav-item"><a href="login.php" class="nav-link active">Login</a> </li>
							  <li class="nav-item"><a href="registration.php" class="nav-link active">Register</a> </li>';
							}
						else
							{
									
									
										echo  '<li class="nav-item"><a href="your_orders.php" class="nav-link active">My Orders</a> </li>';
									echo  '<li class="nav-item"><a href="logout.php" class="nav-link active">Logout</a> </li>';
							}

						?>
                        
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <div class="page-wrapper">
            <!-- Top links and other content goes here -->
            <div class="top-links">
                <div class="container">
                    <ul class="row links">

                        <li class="col-xs-12 col-sm-4 link-item"><span>1</span><a href="restaurants.php">Choose Restaurant</a></li>
                        <li class="col-xs-12 col-sm-4 link-item "><span>2</span><a href="#">Pick Your favorite food</a></li>
                        <li class="col-xs-12 col-sm-4 link-item active"><span>3</span><a href="checkout.php">Order and Pay</a></li>
                    </ul>
                </div>
            </div>

            <div class="container">
                <span style="color:green;">
                    <?php echo $success; ?>
                </span>
            </div>

            <div class="container m-t-30">
                <form action="" method="post">
                    <div class="widget clearfix">
                        <div class="widget-body">
                            <form method="post" action="#">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="cart-totals margin-b-20">
                                            <!-- Cart summary content goes here -->
                                            <div class="cart-totals margin-b-20">
                                            <div class="cart-totals-title">
                                                <h4>Cart Summary</h4>
                                            </div>
                                            <div class="cart-totals-fields">

                                                <table class="table">
                                                    <tbody>

                                                      

                                                        <tr>
                                                            <td>Cart Subtotal</td>
                                                            <td> <?php echo "$".$item_total; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Delivery Charges</td>
                                                            <td>Free</td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-color"><strong>Total</strong></td>
                                                            <td class="text-color"><strong> <?php echo "$".$item_total; ?></strong></td>
                                                        </tr>
                                                    </tbody>



                                                </table>
                                            </div>
                                        </div>
                                        <div class="payment-option">
                                                <ul class="list-unstyled">
                                                    <li>
                                                        <label class="custom-control custom-radio m-b-20">
                                                            <input name="mod" id="radioStacked1" checked value="COD" type="radio" class="custom-control-input">
                                                            <span class="custom-control-indicator"></span>
                                                            <span class="custom-control-description">Cash on Delivery</span>
                                                        </label>
                                                    </li>
                                                    <li>
                                                        <label class="custom-control custom-radio m-b-10">
                                                            <input name="mod" id="radioStacked2" value="paypal" type="radio" class="custom-control-input">
                                                            <span class="custom-control-indicator"></span>
                                                            <span class="custom-control-description">Pay By Credit Card</span>
                                                        </label>
                                                    </li>
                                                </ul>
                                                <div id="paypal-form" style="display: none;">
                                                    <div class="form-group">
                                                        <label for="card-number">Card Credit Number</label>
                                                        <input type="text" class="form-control" id="card-number" name="card-number" placeholder="Enter card credit number">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="card-holder-name">Card Holder Name</label>
                                                        <input type="text" class="form-control" id="card-holder-name" name="card-holder-name" placeholder="Enter card holder name">
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="expiry-month">Expiry Month</label>
                                                        <select class="form-control" id="expiry-month" name="expiry-month">
                                                            <option value="">-- Select Month --</option>
                                                            <option value="01">January</option>
                                                            <option value="02">February</option>
                                                            <option value="03">Mac</option>
                                                            <option value="04">April</option>
                                                            <option value="05">May</option>
                                                            <option value="06">June</option>
                                                            <option value="07">July</option>
                                                            <option value="08">August</option>
                                                            <option value="09">September</option>
                                                            <option value="10">October</option>
                                                            <option value="11">November</option>
                                                            <option value="12">Disember</option>
                                                            <!-- Add more options for other months -->
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="expiry-year">Expiry Year</label>
                                                        <select class="form-control" id="expiry-year" name="expiry-year">
                                                            <option value="">-- Select Year --</option>
                                                            <option value="2023">2023</option>
                                                            <option value="2024">2024</option>
                                                            <option value="2025">2025</option>
                                                            <option value="2026">2026</option>
                                                            <option value="2027">2027</option>
                                                            <option value="2028">2028</option>
                                                            <option value="2029">2029</option>
                                                            <option value="2030">2030</option>
                                                            <option value="2031">2031</option>
                                                            <!-- Add more options for future years -->
                                                        </select>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="cvv">CVV</label>
                                                        <input type="text" class="form-control" id="cvv" name="cvv" placeholder="Enter CVV">
                                                    </div>
                                                </div>
                                                <p class="text-xs-center">
                                                    <input type="submit" onclick="return validateForm();" name="submit" class="btn btn-success btn-block" value="Order Now">
                                                </p>
                                            </div>

                                            <script>
                                                function validateForm() {
                                                    if (document.getElementById("radioStacked2").checked) {
                                                        var cardNumber = document.getElementById("card-number").value;
                                                        var cardHolderName = document.getElementById("card-holder-name").value;
                                                        var expiryMonth = document.getElementById("expiry-month").value;
                                                        var expiryYear = document.getElementById("expiry-year").value;
                                                        var cvv = document.getElementById("cvv").value;

                                                        if (cardNumber === "" || cardHolderName === "" || expiryMonth === "" || expiryYear === "" || cvv === "") {
                                                            alert("Please fill in all the credit card details.");
                                                            return false;
                                                        }
                                                    }

                                                    return confirm("Do you want to confirm the order?");
                                                }
                                            </script>

                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include "include/footer.php" ?>

    <script src="js/jquery.min.js"></script>
    <script src="js/tether.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/animsition.min.js"></script>
    <script src="js/bootstrap-slider.min.js"></script>
    <script src="js/jquery.isotope.min.js"></script>
    <script src="js/headroom.js"></script>
    <script src="js/foodpicky.min.js"></script>
    <script>
        $(document).ready(function() {
            $('input[name="mod"]').change(function() {
                if ($(this).val() === 'paypal') {
                    $('#paypal-form').show();
                } else {
                    $('#paypal-form').hide();
                }
            });
        });
    </script>
</body>

</html>
