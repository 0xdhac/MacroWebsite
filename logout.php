<!DOCTYPE html>
<html>
		<head>
			<meta charset="UTF-8">
		</head>
	<body>
		<form method="POST">
			<ul><button name="logout">Logout</button></ul>
		</form>
	</body>
</html>

<?php
	if(isset($_POST['logout']))
	{
		session_destroy();
		header("Location: login.php");
	}
?>

