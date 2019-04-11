<?php
if(!isset($_GET['code']) || !isset($_GET['email']))
	exit;

include 'includes/dbh.inc.php';
include 'includes/webinfo.inc.php';

$query = $mysqli->prepare("SELECT `email`, `code` FROM `activation` WHERE `code` COLLATE utf8_general_ci LIKE ? AND `email` COLLATE utf8_general_ci LIKE ?");
$query->bind_param("ss", $_GET['code'], $_GET['email']);
$query->execute();
$result = $query->get_result();

if($result->num_rows == 0)
{
	echo "UNAUTHORIZED";
	exit;
}

//get logged in user ID from sesion
$code = $_GET['code'];

//PayPal variables
$successURL    = 'http://www.oxdmacro.site.nfoservers.com/paypalsuccess.php';
$cancelURL     = 'http://www.oxdmacro.site.nfoservers.com/paypalcancel.php';
$notifyURL     = 'http://www.oxdmacro.site.nfoservers.com/paypal_ipn.php';

$itemName = '0xD\'s Fortnite Macro';
$itemNumber = uniqid();

//subscription price for one month
$itemPrice = $EasyEditPrice;
?>

<html>
<head>
	<title>Buy</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="js/paypalbuy.js"></script>
</head>
<body>
<p>Redirecting you to PayPal..</p>
<form action="<?php echo $PaypalUrl; ?>" method="post">
    <!-- identify your business so that you can collect the payments -->
    <input type="hidden" name="business" value="<?php echo $PaypalEmail; ?>">
    <!-- specify a subscriptions button. -->
    <input type="hidden" name="cmd" value="_xclick">
    <!-- specify details about the subscription that buyers will purchase -->
    <input type="hidden" name="item_name" value="<?php echo $itemName; ?>">
    <input type="hidden" name="item_number" value="<?php echo $itemNumber; ?>">
    <input type="hidden" name="currency_code" value="USD">
    <input type="hidden" name="amount" value="<?php echo $itemPrice; ?>">
    <input type="hidden" name="p3" id="paypalValid" value="1">
    <input type="hidden" name="t3" value="M">
    <!-- custom variable user ID -->
    <input type="hidden" name="custom" value="<?php echo $code; ?>">
    <!-- specify urls -->
    <input type="hidden" name="cancel_return" value="<?php echo $cancelURL; ?>">
    <input type="hidden" name="return" value="<?php echo $successURL; ?>">
    <input type="hidden" name="notify_url" value="<?php echo $notifyURL; ?>">
    <!-- display the payment button -->
    <input class="paypal_button" type="submit" value="Buy">
</form>
</body>

</html>