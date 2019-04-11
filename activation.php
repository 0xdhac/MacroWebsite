<?php
include_once 'functions.php';

if(isset($_GET['code']) && isset($_GET['email']))
{
	// Check if code is in the database
	include 'includes/dbh.inc.php';
	$stmt = $mysqli->prepare("SELECT count(*) AS c FROM activation WHERE code COLLATE utf8_general_ci LIKE ? AND email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("ss", $_GET['code'], $_GET['email']);
	$stmt->execute();

	$result = $stmt->get_result();
	$row = $result->fetch_array();
	if($row['c'] == 0)
	{
		echo "INVALID_CODE";
		exit;
	}

	// Add email to payments table
	$email = $_GET['email'];
	if(!checkIfPaymentEmailExists($email))
	{
		$stmt = $mysqli->prepare("INSERT INTO payments (email) VALUES (?)");
		$stmt->bind_param("s", $email);
		$stmt->execute();
	}

	echo "SUCCESS";
}
else if(isset($_GET['email']))
{
	// Check if it is a valid e-mail address
	$email = $_GET['email'];
	if(!checkEmail($email))
	{
		echo "INVALID_EMAIL";
		exit;
	}

	if(checkIfEmailExists($email))
	{
		echo "EMAIL_EXISTS";
		exit;
	}

	$code = activationCodeExistsForEmail($email, TRUE);
	if($code === FALSE)
	{
		$code = generateActivationCodeForEmail($email);
	}

	if(sendActivationEmail($email, $code))
	{
		echo "EMAIL_SENT";
	}
	else
	{
		echo "EMAIL_NOT_SENT";
	}
}

function generateActivationCodeForEmail($email)
{
	// Generate confirmation code
	$code = generatePassword();

	include 'includes/dbh.inc.php';
	if(activationCodeExistsForEmail($email, FALSE))
	{
		$stmt = $mysqli->prepare("UPDATE activation SET code = ?, timestamp = UNIX_TIMESTAMP() WHERE email COLLATE utf8_general_ci LIKE ?");
		$stmt->bind_param("ss", $code, $email);
		$stmt->execute();
	}
	else
	{
		$stmt = $mysqli->prepare("INSERT INTO activation(email, code, timestamp) VALUES(?, ?, UNIX_TIMESTAMP())");
		$stmt->bind_param("ss", $email, $code);
		$stmt->execute();
	}

	return $code;
}

function sendActivationEmail($email, $code)
{
	// Send confirmation code to e-mail
	$body = "Here is your activation code for your EasyEdit account: ".$code;
	$emailResult = mail($email, "EasyEdit Activation code", $body);
	return $emailResult;
}