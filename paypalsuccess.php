<?php
//Include DB configuration file
include 'includes/dbh.inc.php';

if(!empty($_GET['item_number']) && !empty($_GET['tx']) && !empty($_GET['amt']) && $_GET['st'] == 'Completed')
{
    //get transaction information from query string
    $phtml = '<h5 class="success">Payment successful. Please check your e-mail for your login information.</h5>';
}
elseif(!empty($_GET['item_number']) && !empty($_GET['tx']) && !empty($_GET['amt']) && $_GET['st'] != 'Completed')
{
    $phtml = '<h5 class="error">Your payment was unsuccessful, please try again.</h5>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Status</title>
    <meta charset="utf-8">
</head>
<body>
<div class="container">
    <h1>PayPal Payment Status</h1>
    <!-- render subscription details -->
    <?php echo !empty($phtml)?$phtml:''; ?>
</body>
</html>
