<?php
	session_start();

	if(isset($_SESSION['auth']) && $_SESSION['auth'] == true && isset($_POST['hash']) && isset($_SESSION['token']) && isset($_SESSION['email']))
	{
		if(isset($_SESSION['admin']) && $_SESSION['admin'] == '1')
		{
			echo "SUCCESS";
			exit;
		}
		else
		{
			// Validate software MD5 Hash
			include 'includes/dbh.inc.php';
			$hash   = mysqli_escape_string($mysqli, $_POST['hash']);
			$query  = "SELECT count(*) AS c FROM versions WHERE md5 LIKE '$hash'";
			$result = $mysqli->query($query);
			if(!$result)
			{
				echo "FAILURE0";
				exit;
			}

			$row = $result->fetch_assoc();
			if($row['c'] == 0)
			{
				echo "FAILURE1";
				exit;
			}

			// Validate token to prevent multiple logins
			$username = mysqli_escape_string($mysqli, $_SESSION['email']);
			$query    = "SELECT token FROM user_token WHERE username COLLATE utf8_general_ci LIKE '$username'";
			$result   = $mysqli->query($query);
			if(!$result)
			{
				echo "FAILURE3";
				session_destroy();
				exit;
			}

			if($result->num_rows == 0)
			{
				echo "FAILURE4";
				session_destroy();
				exit;
			}

			$row = $result->fetch_assoc();
			if(strcmp($row['token'], $_SESSION['token']) != 0)
			{
				echo "FAILURE5";
				session_destroy();
				exit;
			}

			echo "SUCCESS";
		}
	}
	else
	{
		echo "FAILURE6";
	}