<?php
session_start();
if(!isset($_SESSION['admin']) || $_SESSION['admin'] != '1')
{
	exit;
}

if(isset($_POST['user']) == true && empty($_POST['user']) == false)
{
	if(isset($_POST['discord']) == true && empty($_POST['discord']) == false)
	{
		include '../includes/dbh.inc.php';

		$id      = mysqli_escape_string($mysqli, $_POST['user']);
		$discord = mysqli_escape_string($mysqli, $_POST['discord']);
		$query   = "UPDATE users SET discord = '$discord' WHERE id = '$id';";

		if (!$result = $mysqli->query($query)) 
		{
		    echo "ERROR:QUERY_FAILED";
		}
		else
		{
			echo "Discord name changed to '$discord'";
		}
	}
}