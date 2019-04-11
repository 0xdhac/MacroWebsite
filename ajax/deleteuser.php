<?php
session_start();
if(!isset($_SESSION['admin']) || $_SESSION['admin'] != '1')
{
	exit;
}

if(isset($_POST['user']) == true && empty($_POST['user']) == false)
{
	include '../includes/dbh.inc.php';

	$user = mysqli_escape_string($mysqli, $_POST['user']);
	$query = "DELETE FROM users WHERE id = '$user' AND isAdmin = '0';";
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