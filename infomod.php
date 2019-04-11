<?php

function changePassword($email, $password = '')
{
	include 'includes/dbh.inc.php';

	if(strlen($password) == 0)
	{
		for($i = 0; $i < 10; $i++)
		{
			$password .= $alphabet[rand(0, strlen($alphabet) - 1)];
		}
	}
	
	$hash = password_hash($password, PASSWORD_BCRYPT);

	$stmt = $mysqli->prepare("UPDATE users SET pass = ? WHERE email COLLATE utf8_general_ci LIKE ?");
	$stmt->bind_param("ss", $hash, $email);
	$stmt->execute();

	return $password;
}