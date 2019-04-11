<?php

/*
* Check if an activation code exists for a specified e-mail. Returns FALSE if it doesn't exist. Else it returns the code string.
*/
function activationCodeExistsForEmail($email)
{
	include 'includes/dbh.inc.php';
	$stmt = $mysqli->prepare("SELECT code FROM activation WHERE email COLLATE utf8_general_ci LIKE ? LIMIT 0, 1");
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

/*
* Check if an activating email exists for a specified code. Returns FALSE if it doesn't exist. Else it returns the e-mail it belongs too.
*/
function activationEmailExistsForCode($code)
{
	include 'includes/dbh.inc.php';
	$stmt = $mysqli->prepare("SELECT `email` FROM `activation` WHERE `code` COLLATE utf8_general_ci LIKE ? LIMIT 0, 1");
	$stmt->bind_param("s", $code);
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
	
	return $row['email'];
}

/*
* Change a user's password. Returns the password string if the email is in the database, FALSE otherwise.
*/
function changePassword($email, $password = '')
{
	include 'includes/dbh.inc.php';

	$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	if(strlen($password) == 0)
	{
		$password = generatePassword();
	}
	
	$hash = password_hash($password, PASSWORD_BCRYPT);

	$stmt = $mysqli->prepare("UPDATE users SET pass = ? WHERE email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("ss", $hash, $email);
	$result = $stmt->execute();

	return $password;
}

/*
* Get's a username from an e-mail. Returns username string if it exists, FALSE otherwise.
*/
function getUsernameFromEmail($email)
{
	include 'includes/dbh.inc.php';

	$stmt = $mysqli->prepare("SELECT user FROM users WHERE email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();

	$result = $stmt->get_result();
	if($result->num_rows == 0)
	{
		return FALSE;
	}

	$row = $result->fetch_array();

	return $row['user'];
}

/*
* Gets an invoice id from a paid user's email, FALSE if it doesn't exist
*/
function getInvoiceFromEmail($email)
{
	include 'includes/dbh.inc.php';

	$stmt = $mysqli->prepare("SELECT invoiceid FROM payments WHERE email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();

	$result = $stmt->get_result();
	if($result->num_rows == 0)
	{
		return FALSE;
	}

	$row = $result->fetch_array();

	return $row['invoiceid'];
}

/*
* Gets an email from an invoice id, FALSE if it doesn't exist
*/
function getEmailFromInvoice($invoice)
{
	include 'includes/dbh.inc.php';

	$stmt = $mysqli->prepare("SELECT email FROM payments WHERE invoice COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("s", $invoice);
	$stmt->execute();

	$result = $stmt->get_result();
	if($result->num_rows == 0)
	{
		return FALSE;
	}

	$row = $result->fetch_array();

	return $row['email'];
}

/*
* Registers an account. FALSE if the email already exists
*/
function registerAccount($email, $password, $admin, $discord = '')
{
	// Make sure email is valid
	if(!checkEmail($email))
	{
		return FALSE;
	}

	// Make sure email doesn't already exist
	if(checkIfEmailExists($email))
	{
		return FALSE;
	}

	$hash = password_hash($password, PASSWORD_BCRYPT);

	// Register account
	include 'includes/dbh.inc.php';
	$stmt = $mysqli->prepare("INSERT INTO users (email, pass, isAdmin, discord) VALUES (?, ?, ?, ?)");
	$stmt->bind_param("ssis", $email, $hash, $admin, $discord);
	$stmt->execute();

	// Delete activation code so it can't be used again
	$stmt = $mysqli->prepare("DELETE FROM `activation` WHERE `email` COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();

	// Send confirmation code to e-mail
	$body = "Here is your login information for your EasyEdit account: \r\n\r\nUsername: $email\r\nPassword: $password";
	$emailResult = mail($email, "EasyEdit Login Information", $body);
}

/*
* Ban an account.
*/
function banAccount($email, $ban)
{
	include 'includes/dbh.inc.php';

	$stmt = $mysqli->prepare("UPDATE users SET banned = ? WHERE email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("is", $ban, $email);
	$stmt->execute();

	$result = $stmt->get_result();
	return ($result->affected_rows > 0);
}

/*
* Change an account's email.
*/
function changeEmail($oldEmail, $newEmail)
{
	if(!checkEmail($newEmail))
	{
		return FALSE;
	}

	include 'includes/dbh.inc.php';

	$stmt = $mysqli->prepare("UPDATE users SET email = ? WHERE email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("ss", $newEmail, $oldEmail);
	$stmt->execute();

	$result = $stmt->get_result();
	return ($result->affected_rows > 0);
}

/*
* Check if input is a valid e-mail address
*/
function checkEmail($email) 
{
   return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/*
* Check if e-mail is tied to an existing account
*/
function checkIfEmailExists($email)
{
	include 'includes/dbh.inc.php';

	$stmt = $mysqli->prepare("SELECT email FROM users WHERE email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();

	$result = $stmt->get_result();
	
	return $result->num_rows != 0;
}

/*
* Check if e-mail is tied to an existing account
*/
function checkIfPaymentEmailExists($email)
{
	include 'includes/dbh.inc.php';

	$stmt = $mysqli->prepare("SELECT email FROM payments WHERE email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("s", $email);
	$stmt->execute();

	$result = $stmt->get_result();
	
	return $result->num_rows != 0;
}

/*
* Generates a password string
*/
function generatePassword()
{
	$length   = 10;
	$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$password = '';

	for($i = 0; $i < $length; $i++)
	{
		$password .= $alphabet[rand(0, strlen($alphabet) - 1)];
	}

	return $password;
}

/*
* Check if a specified e-mail is tied to an admin account
*/
function isEmailAdmin($email)
{
	include 'includes/dbh.inc.php';

	$stmt = $mysqli->prepare("SELECT isAdmin FROM users WHERE email COLLATE utf8_general_ci LIKE ? AND isAdmin = 1");
	$stmt->bind_param("s", $email);
	$stmt->execute();

	$result = $stmt->get_result();
	
	return $result->num_rows != 0;
}