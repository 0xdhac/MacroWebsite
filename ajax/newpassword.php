<?php
session_start();
if(!isset($_SESSION['admin']) || $_SESSION['admin'] != '1')
{
	exit;
}

if(isset($_POST['user']) == true && empty($_POST['user']) == false)
{
	include '../includes/dbh.inc.php';

	// Get username and admin status of specified user
	$id = mysqli_escape_string($mysqli, $_POST['user']);
	$query = "SELECT user FROM users WHERE id = '$id';";
	$result = $mysqli->query($query);

	if(!$result)
	{
		echo "ERROR:QUERY_FAILED";
		exit;
	}

	$row = $result->fetch_assoc();
	$user = $row['user'];

	// Create password string
	$password = "";

	if(isset($_POST['password']) == true && empty($_POST['password']) == false)
	{
		$password = $_POST['password'];
	}
	else
	{
		$alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

		for($i = 0; $i < 10; $i++)
		{
			$password .= $alphabet[rand(0, strlen($alphabet) - 1)];
		}		
	}

	// Set new password
	$hash = password_hash($password, PASSWORD_BCRYPT);
	$query = "UPDATE users SET pass = '$hash' WHERE id = '$id';";

	if (!$result = $mysqli->query($query)) 
	{
	    echo "ERROR:QUERY_FAILED";
	}
	else
	{
		echo "Username: ".$user."<br>Password: ".$password;
	}

}