<?php
$codeExpirationTime = (60 * 60);

include_once 'functions.php';

if(isset($_GET['code']))
{
	// Check if code is in the database
	include 'includes/dbh.inc.php';
	$stmt = $mysqli->prepare("SELECT email FROM recovery WHERE code COLLATE utf8_general_ci LIKE ? AND UNIX_TIMESTAMP() - timestamp < $codeExpirationTime");
	$stmt->bind_param("s", $_GET['code']);
	$stmt->execute();

	$result = $stmt->get_result();
	if($result->num_rows == 0)
	{
		echo "INVALID_CODE";
		exit;
	}

	$row = $result->fetch_array();

	$email = $row['email'];

	$password = '';
	if(isset($_GET['password']))
	{
		$password = $_GET['password'];
	}

	// If it is, return a json object containing an updated username and password
	$password = changePassword($email, $password);

	$newInfo = array();
	$newInfo['email'] = $email;
	$newInfo['pass']  = $password;

	echo json_encode($newInfo);
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

	$emailExists = checkIfEmailExists($email);
	if(!$emailExists)
	{
		echo "EMAIL_DOESNT_EXIST";
		exit;
	}


	$code = recoveryCodeExistsForEmail($email, TRUE);
	if($code === FALSE)
	{
		$code = generateRecoveryCodeForEmail($email);
	}

	if(sendRecoveryEmail($email, $code))
	{
		echo "EMAIL_SENT";
	}
	else
	{
		echo "EMAIL_NOT_SENT";
	}
}

function generateRecoveryCodeForEmail($email)
{
	// Generate confirmation code
	$code       = '';
	$alphabet   = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
	$codeLength = 10;
	
	for($i = 0; $i < $codeLength; $i++)
	{
		$code .= $alphabet[rand(0, strlen($alphabet) - 1)];
	}

	include 'includes/dbh.inc.php';
	if(recoveryCodeExistsForEmail($email, FALSE))
	{
		$stmt = $mysqli->prepare("UPDATE recovery SET code = ?, timestamp = UNIX_TIMESTAMP() WHERE email COLLATE utf8_general_ci LIKE ?");
		$stmt->bind_param("ss", $code, $email);
		$stmt->execute();
	}
	else
	{
		$stmt = $mysqli->prepare("INSERT INTO recovery(email, code, timestamp) VALUES(?, ?, UNIX_TIMESTAMP())");
		$stmt->bind_param("ss", $email, $code);
		$stmt->execute();
	}

	return $code;
}

function recoveryCodeExistsForEmail($email, $checkTime)
{
	$query = "SELECT code FROM recovery WHERE email COLLATE utf8_general_ci LIKE ? ";
	if($checkTime === TRUE)
	{
		global $codeExpirationTime;
		$query .= "AND UNIX_TIMESTAMP() - timestamp < ".$codeExpirationTime." ";
	}
	$query .= "LIMIT 0, 1";

	include 'includes/dbh.inc.php';
	$stmt = $mysqli->prepare($query);
	$stmt->bind_param("s", $email);
	$success = $stmt->execute();
	if($success === FALSE)
	{
		return FALSE;
	}

	$result = $stmt->get_result();
	if($result->num_rows == 0)
	{
		return FALSE;
	}

	$row = $result->fetch_array();
	
	return $row['code'];
}

function sendRecoveryEmail($email, $code)
{
	// Send confirmation code to e-mail
	$body = "Here is your confirmation code to generate a new password for your EasyEdit account: ".$code;
	$emailResult = mail($email, "EasyEdit Confirmation code", $body);
	return $emailResult;
}