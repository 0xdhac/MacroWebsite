<?php
session_start();
if(!isset($_SESSION['admin']) || $_SESSION['admin'] != '1')
{
	exit;
}

if(isset($_POST['textbox']) == true && empty($_POST['textbox']) == false)
{
	include '../includes/dbh.inc.php';

	$data = mysqli_escape_string($mysqli, $_POST['textbox']);
	$query = "SELECT id, email, discord, isAdmin, banned FROM users WHERE email LIKE '%$data%' OR discord LIKE '%$data%' ORDER BY discord ASC;";
	$result = $mysqli->query($query);
	if($result)
	{
		$return_arr = array();
		while ($row = $result->fetch_assoc()) 
		{
			$return_arr[] = array("id" => $row['id'], "email" => $row['email'], "discord" => $row['discord'], "isAdmin"  => $row['isAdmin'] == '1'?'Yes':'No', "banned" => $row['banned'] == '1'?'Yes':'No');
		}

		print json_encode($return_arr);
	}
	else
	{
		echo "Failed";
	}
}