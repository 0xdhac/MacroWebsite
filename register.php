<?php
session_start();

if(!isset($_SESSION['admin']) || $_SESSION['admin'] == '0')
{
	header("Location: login.php");
	exit;
}

if(isset($_POST['discord']) && isset($_POST['email']))
{
	include_once 'includes/dbh.inc.php';
	include 'functions.php';

	$email = $_POST['email'];

	$emailValid = checkEmail($email);

	if(!$emailValid)
	{
		echo "INVALID_EMAIL";
		exit;
	}

	$emailExists = checkIfEmailExists($email);
	if($emailExists)
	{
		echo "EMAIL_EXISTS";
		exit;
	}

	$alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$password = "";
	for($i = 0; $i < 10; $i++)
	{
		$password .= $alphabet[rand(0, strlen($alphabet) - 1)];
	}

	$hash = password_hash($password, PASSWORD_BCRYPT);

	$admin = 0;
	if(isset($_POST['admin']))
	{
		$admin = 1;
	}

	$discord = mysqli_escape_string($mysqli, $_POST['discord']);
	$query = "INSERT into users (email, pass, discord, isAdmin) VALUES ('$email', '$hash', '$discord', '$admin');";

	if (!$result = $mysqli->query($query)) 
	{
	    echo "ERROR:QUERY_FAILED";
	}

	echo "Email: ".$email."<br>Password: ".$password;
}
?>

<!DOCTYPE html>
<html>
		<head>
			<meta charset="UTF-8">

	        <style type="text/css">
	        ul
	        {
				text-align: center;
			}

			.link
			{
				text-align: center;
			}

            label
            {
				display:inline-block;
				width:200px;
				margin-right:10px;
				text-align:right;
			}

			fieldset
			{
				border:none;
				width:500px;
				margin:0px auto;
			}
		    </style>

			<title>Generate New Account</title>
		</head>
	<body>
		<form method="POST">
			<fieldset>
			<label for="email">E-mail: </label><input type="text" name="email" size="20">
			<label for="discord">Discord: </label><input type="text" name="discord" size="20">
			<label for="admin">Admin: </label><input type="checkbox" class="checkboxes" name="admin" size="0">
			<ul><button>Register</button></ul>
			<center><a href="control.php" class="link">Control Panel</a><center>
			</fieldset>
		</form>
	</body>
</html>

<?php
if(isset($_SESSION['auth']) && $_SESSION['auth'] == true)
{
	include 'logout.php';
}
?>