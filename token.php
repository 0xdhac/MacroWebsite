<?php
session_start();

if(isset($_SESSION['auth']) && isset($_SESSION['token']) && isset($_SESSION['username']))
{
	include 'includes/dbh.inc.php';

	$username = mysqli_escape_string($mysqli, $_SESSION['username']);
	$query = "SELECT token FROM user_token WHERE username COLLATE latin1_general_cs LIKE '$username'";
	$result = $mysqli->query($query);
	if(!$result)
	{
		echo "NOT_VALIDATED";
		session_destroy();
		exit;
	}

	if($result->num_rows == 0)
	{
		echo "NOT_VALIDATED";
		session_destroy();
		exit;
	}

	$row = $result->fetch_assoc();
	if(strcmp($row['token'], $_SESSION['token']) == 0)
	{
		echo "VALIDATED";
		exit;
	}
	else
	{
		echo "NOT_VALIDATED";
		session_destroy();
		exit;
	}
}
else
{
	echo "NOT_VALIDATED";
	session_destroy();
	exit;
}
?>