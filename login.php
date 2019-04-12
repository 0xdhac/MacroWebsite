<?php
include 'includes/dbh.inc.php';
session_start();

if(!isset($_SESSION['auth']))
{
	$_SESSION['auth'] = false;
}

if(isset($_POST['email']) and isset($_POST['pass']))
{
	// Find user in db
	$query = "SELECT email, pass, isAdmin, banned FROM users WHERE email COLLATE utf8_general_ci LIKE '" . mysqli_escape_string($mysqli, $_POST['email']) . "';";

	$result = $mysqli->query($query);

	if ($result->num_rows === 0)
	{
	    echo "ERROR:ACCOUNT_NOT_FOUND";
	    exit;
	}

	// Fetch the information about the user
	$row = $result->fetch_assoc();
	if(password_verify($_POST['pass'], $row['pass']))
	{
		$_SESSION['admin'] = $row['isAdmin'];
		$_SESSION['banned'] = $row['banned'];

		if(!$row['isAdmin'] && $row['banned'])
		{
			echo "ERROR:BANNED_ACCOUNT";
			exit;
		}
		else
		{
			// Log in is succressful
			$_SESSION['auth'] = true;
			$_SESSION['email'] = $row['email'];
			UpdateToken();

			if($_SESSION['admin'] == '1' && $_SERVER['HTTP_USER_AGENT'] != "EasyEdit")
			{
				header("Location: control.php");
			}
			else
			{
				echo "SUCCESS:LOGGED_IN";
			}

			exit;
		}
	}
	else
	{
		echo "ERROR:INVALID_PASSWORD";
	}

	if(!isset($_SESSION['attempts']))
	{
		$_SESSION['attempts'] = 0;
	}
	$_SESSION['attempts']++;
}
else
{
	if($_SESSION['auth'] == true)
	{
		if($_SESSION['admin'] == '1' && $_SERVER['HTTP_USER_AGENT'] != "EasyEdit")
		{
			header("Location: control.php");
		}

		echo "SUCCESS:LOGGED_IN";
		include 'logout.php';
		exit;
	}
}

function UpdateToken()
{
	include 'includes/dbh.inc.php';

	$username = mysqli_escape_string($mysqli, $_SESSION['email']);
	$query = "SELECT token FROM user_token WHERE username COLLATE utf8_general_ci LIKE '$username';";

	if (!$result = $mysqli->query($query))
	{
		echo "ERROR:QUERY_FAILED";
		exit;
	}

	$token = GenerateToken();

	if($result->num_rows == 0)
	{
		$query = "INSERT INTO user_token (username, token) VALUES ('$username', '$token');";
	}
	else
	{
		$query = "UPDATE user_token SET token = '$token' WHERE username COLLATE utf8_general_ci LIKE '$username'";
	}

	if($mysqli->query($query))
	{
		$_SESSION['token'] = $token;
	}
}

function GenerateToken()
{
	$alphabet = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$token = "";
	for($i = 0; $i < 10; $i++)
	{
		$token .= $alphabet[rand(0, strlen($alphabet) - 1)];
	}

	return $token;
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

            label
            {
				display:inline-block;
				width:200px;
				margin-right:10px;
				text-align:right;
			}

			input
			{

			}

			fieldset
			{
				border:none;
				width:500px;
				margin:0px auto;
			}
		    </style>

			<title>Login</title>
		</head>
	<body>
		<form method="POST">
			<fieldset>
			<label for="email">E-mail:</label><input type="text" name="email" size="20">
			<label for="pass">Password:</label><input type="password" name="pass" size="20">
			<ul><button>Login</button></ul>
			</fieldset>
		</form>
	</body>
</html>
