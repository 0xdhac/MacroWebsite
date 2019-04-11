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
  <head>
    <title>Modify Users</title>
</head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script src="js/modify.js"></script>
<style>
  table {
    font-family: arial, sans-serif;
    border-collapse: collapse;
    width: 50%;
    align-content: center;
  }

  td, th {
    border: 1px solid #dddddd;
    text-align: left;
    padding: 8px;
  }

  input {
    margin-top: 10px;
    margin-bottom: 10px;
  }

  button {
    margin-top: 10px;
    margin-bottom: 10px;
  }

  input[type=checkbox] {
    /* Double-sized Checkboxes */
    -ms-transform: scale(1.5); /* IE */
    -moz-transform: scale(1.5); /* FF */
    -webkit-transform: scale(1.5); /* Safari and Chrome */
    -o-transform: scale(1.5); /* Opera */
    margin: auto;
    padding: 10px;
  }

  .messagepop {
    border:2px solid #999999;
    display:none;
    margin-top: 15px;
    text-align:left;
    width: 210px;
    height: 200px;
    padding: 0px 25px 20px;
    position: fixed;
      top:50%;
      left:50%;
      transform: translate(-50%, -50%);
      opacity: 1.0;
    background-color: rgb(255, 255, 255);
  }

  label {
    display: block;
    margin-bottom: 3px;
    padding-left: 15px;
    text-indent: -15px;
  }
</style>
</head>
<body class="b">

<center>
<h2>Modify Users</h2>
<input type="text" id="textbox" class="searchbox"></input><button type="button" id="search">Search</button> 
<table class="t">
  <col width="50">
  <tr>
    <th>Select</th>
    <th>E-mail</th>
    <th>Discord</th>
    <th>Admin</th>
    <th>Banned</th>
  </tr>
</table>
<button type="button" id="ban">Ban</button>
<button type="button" id="unban">Unban</button>
<button type="button" id="delete">Delete</button>
<button type="button" id="newpass">New Password</button>
<button type="button" id="setdiscord">Set Discord Name</button>
<br><a href="control.php">Control Panel</a>
<div class="messagepop pop">
  <form method="post" id="new_message" action="/messages">
      <p><label for="password_label">New Password</label><input type="password" size="30" name="password_input" id="password_input" /></p>
      <p><button type="button" id="password_submit">Submit</button> or <a class="close" href="/">Cancel</a></p>
  </form>
  <h3 class = "login_output"></h3>
</div>
<div class="messagepop discord">
  <form method="post" id="new_message" action="/messages">
      <p><label for="discord_label">New Discord Name</label><input type="text" size="30" name="discord_input" id="discord_input" /></p>
      <p><button type="button" id="discord_submit">Submit</button> or <a class="discord_close" href="/">Cancel</a></p>
  </form>
  <h3 class = "discord_output"></h3>
</div>

</center>

</body>
</html>
