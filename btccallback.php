 <?php
$secret = 's89gknmkas8967jedin8f';
$txid = $_GET['txid'];
$value = $_GET['value'];
$status = $_GET['status'];
$addr = $_GET['addr'];

//Match secret for security
if ($_GET['secret'] != $secret) 
{
    return;
}

if ($status != 2) 
{
echo "INVALID_STATUS";
exit;
}

include 'includes/dbh.inc.php';

//Mark address in database as paid
$stmt = $mysqli->prepare("UPDATE payments set txid=?,value=value+? where addr=?");
$stmt->bind_param("sis", $txid, $value, $addr);
$stmt->execute();

$stmt = $mysqli->prepare("SELECT email, price FROM payments WHERE addr = ?");
$stmt->bind_param("s", $addr);
$stmt->execute();

$result = $stmt->get_result();
if($result->num_rows == 0)
{
	echo "EMAIL_NOT_FOUND";
	exit;
}

$row = $result->fetch_array();

include 'functions.php';
$email    = $row['email'];
$price    = $row['price'];

$btcprice = floatval($price);

// Get how much the user has paid and convert it from Satoshis to Bitcoins
$paidAmount = floatval(getPaidAmount($email)) * 0.00000001;

if($paidAmount < ($btcprice * 0.99))
{
	echo "INSUFFICIENT_AMOUNT";
	exit;
}

$password = generatePassword();
registerAccount($email, $password, FALSE);

function getPaidAmount($email)
{
	include 'includes/dbh.inc.php';
	$stmt = $mysqli->prepare("SELECT value FROM payments WHERE email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();

	if($result->num_rows == 0)
	{
		return FALSE;
	}

	$row = $result->fetch_array();
	return $row['value'];
}