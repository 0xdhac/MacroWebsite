<?php
session_start();
if(!isset($_SESSION['admin']) || $_SESSION['admin'] != '1')
{
	exit;
}

if(isset($_POST['user']) == true && empty($_POST['user']) == false && isset($_POST['ban']) == true)
{
	include '../includes/dbh.inc.php';

	if($_POST['ban'] != '1' && $_POST['ban'] != '0')
	{
		echo "FAILED:BAD_BAN_ARG";
	}
	else
	{
		$user = mysqli_escape_string($mysqli, $_POST['user']);
		$ban  = mysqli_escape_string($mysqli, $_POST['ban']);
		$query = "UPDATE users SET banned = '$ban' WHERE id = '$user' AND isAdmin = '0';";
		$result = $mysqli->query($query);
		if($result)
		{
			echo "SUCCESS";
		}
		else
		{
			echo "FAILED:QUERY_FAILED";
		}
	}
}