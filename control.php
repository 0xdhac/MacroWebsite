<?php
session_start();

if(!isset($_SESSION['admin']) || $_SESSION['admin'] != '1')
{
	header("Location: login.php");
	exit;
}
?>

<!DOCTYPE html>
<html>
		<head>
			<title>Control Panel</title>
		</head>
	<body>
		<form method="POST">
			<a href="register.php">Register New Account</a><br>
			<a href="modify.php">Modify An Account</a><br>
			<a href="upload.php">Upload New Version</a><br>
		</form>
	</body>
</html>

<?php
	if(isset($_SESSION['auth']) && $_SESSION['auth'] == true)
	{
		include 'logout.php';
	}
?>