<?php
if(!isset($_GET['code']) || !isset($_GET['email']))
{
	echo "HEADER_NOT_SET";
	exit;
}

$email = $_GET['email'];

// Check if code is in the database
include 'includes/dbh.inc.php';
include 'includes/webinfo.inc.php';
$stmt = $mysqli->prepare("SELECT count(*) AS c FROM activation WHERE code COLLATE utf8_general_ci LIKE ? AND email COLLATE utf8_general_ci LIKE ?");
$stmt->bind_param("ss", $_GET['code'], $email);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_array();
if($row['c'] == 0)
{
	echo "INVALID_CODE";
	exit;
}

if(isAtActivationLimit($email))
{
	echo "ADDRESS_LIMIT";
	exit;
}

$api_key = 'LisOdzAYn2lucmIJkcM5Kthc0g2B1fHgfWmqa4VQg5Q';
$url = 'https://www.blockonomics.co/api/new_address?reset=1';
$data = '';
//Unique ID of item being sold 
$invoiceid = uniqid();
$options   = array( 
    'http' => array(
        'header'  => 'Authorization: Bearer '.$api_key,
        'method'  => 'POST',
        'content' => $data
    )   
);  

$context = stream_context_create($options);
$contents = file_get_contents($url, false, $context);
$object = json_decode($contents);
addToActivationCount($email);

$price    = 50;
$url      = "https://blockchain.info/tobtc?currency=USD&value=$EasyEditPrice";
$contents = file_get_contents($url);

//Associate address with email in DB
$stmt = $mysqli->prepare("UPDATE payments SET invoiceid = ?, addr = ?, price = ? WHERE email COLLATE utf8_general_ci LIKE ?");
$stmt->bind_param("ssss", $invoiceid, $object->address, $contents, $email);
$stmt->execute();

$arr = array();
$arr["invoice"] = $invoiceid;
$arr["address"] = $object->address;
$arr["price"] = $contents;
$jobj = json_encode($arr);

echo $jobj;

function addToActivationCount($email)
{
	include 'includes/dbh.inc.php';
	$stmt = $mysqli->prepare("UPDATE activation SET addresscount = addresscount + 1 WHERE email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();
}

function isAtActivationLimit($email)
{
	include 'includes/dbh.inc.php';
	$stmt = $mysqli->prepare("SELECT addresscount FROM activation WHERE email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();
	$result = $stmt->get_result();

	if($result->num_rows == 0)
	{
		return FALSE;
	}

	$row = $result->fetch_array();
	return $row['addresscount'] >= 3;
}