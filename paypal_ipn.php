<?php
//Include DB configuration file
include 'includes/dbh.inc.php';
include 'functions.php';
include 'includes/webinfo.inc.php';

/*
 * Read POST data
 * reading posted data directly from $_POST causes serialization
 * issues with array data in POST.
 * Reading raw POST data from input stream instead.
 */
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode ('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}

// Read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
if(function_exists('get_magic_quotes_gpc')) {
    $get_magic_quotes_exists = true;
}
foreach ($myPost as $key => $value) {
    if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1){
        $value = urlencode(stripslashes($value));
    }else{
        $value = urlencode($value);
    }
    $req .= "&$key=$value";
}

/*
 * Post IPN data back to PayPal to validate the IPN data is genuine
 * Without this step anyone can fake IPN data
 */
$ch = curl_init($PaypalUrl);
if ($ch == FALSE){
    return FALSE;
}
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSLVERSION, 6);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

// Set TCP timeout to 30 seconds
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: company-name'));
$res = curl_exec($ch);

/*
 * Inspect IPN validation result and act accordingly
 * Split response headers and payload, a better way for strcmp
 */
$tokens = explode("\r\n\r\n", trim($res));
$res = trim(end($tokens));
if (strcmp($res, "VERIFIED") == 0 || strcasecmp($res, "VERIFIED") == 0){
    //Payment data
    $item_number    = $_POST['item_number'];
    $txn_id         = $_POST['txn_id'];
    $payment_gross  = $_POST['mc_gross'];
    $currency_code  = $_POST['mc_currency'];
    $payment_status = $_POST['payment_status'];
    $custom         = $_POST['custom'];

    // Get original transaction id if it is a chargeback
    if($payment_status == 'Reversed'){
      $txn_id = $_POST['parent_txn_id'];
    }
    

    //Check if payment data exists with the same TXN ID.
    $stmt = $mysqli->prepare("SELECT payment_id FROM payments_paypal WHERE txn_id = ?");
    $stmt->bind_param('s', $txn_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0){
        if($payment_status == 'Reversed'){
            $stmt = $mysqli->prepare("SELECT email FROM payments_paypal WHERE txn_id = ?");
            $stmt->bind_param('s', $txn_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows == 0){
                exit;
            }

            $row = $result->fetch_assoc();
            $email = $row['email'];
            if(!is_null($email)){
                banAccount($email, 1);
            }

            $stmt = $mysqli->prepare("UPDATE payments_paypal SET payment_status = ? WHERE txn_id = ?");
            $stmt->bind_param('ss', $payment_status, $txn_id);
            $stmt->execute();
        }
    }else{
        //Insert tansaction data into the database
        $mysqli->query($query);
        $stmt = $mysqli->prepare("INSERT INTO payments_paypal(item_number, txn_id, payment_gross, currency_code, payment_status) VALUES(?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $item_number, $txn_id, $payment_gross, $currency_code, $payment_status);
        $stmt->execute();
        if($payment_status == 'Completed'){
            $email = activationEmailExistsForCode($custom);
            if($email !== FALSE){
                $password = generatePassword();
                registerAccount($email, $password, 0);
                $stmt = $mysqli->prepare("UPDATE payments_paypal SET email = ? WHERE txn_id = ?");
                $stmt->bind_param('ss', $email, $txn_id);
                $stmt->execute();
            }
        }
    }

}
