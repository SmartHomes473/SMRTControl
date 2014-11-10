<?php
session_start();
//////////////////////////////
// EDIT THESE TWO VARIABLES //
//////////////////////////////
$MySQLUsername = "root";
$MySQLPassword = "smarthouse";

/////////////////////////////////
// DO NOT EDIT BELOW THIS LINE //
/////////////////////////////////
$MySQLHost = "localhost";
$MySQLDB = "uart";

If (($MySQLUsername == "USERNAME HERE") || ($MySQLPassword == "PASSWORD HERE")){
	print 'ERROR - Please set up the script first';
	exit();
}

$dbConnection = mysql_connect($MySQLHost, $MySQLUsername, $MySQLPassword);
mysql_select_db($MySQLDB, $dbConnection);
If (isset($_POST['action'])){
	If ($_POST['action'] == "setPassword"){
		$password1 = $_POST['password1'];
		$password2 = $_POST['password2'];
		If ($password1 != $password2){
			header('Location: uart.php');
		}
		$password = mysql_real_escape_string($_POST['password1']);
		If (strlen($password) > 28){
			mysql_close();
			header('location: uart.php');
		}
		$resetQuery = "SELECT username, salt FROM users WHERE username = 'admin';";
		$resetResult = mysql_query($resetQuery);
		If (mysql_num_rows($resetResult) < 1){
			mysql_close();
			header('location: uart.php');
		}
		$resetData = mysql_fetch_array($resetResult, MYSQL_ASSOC);
		$resetHash = hash('sha256', $salt . hash('sha256', $password));
		$hash = hash('sha256', $password);
		function createSalt(){
			$string = md5(uniqid(rand(), true));
			return substr($string, 0, 8);
		}
		$salt = createSalt();
		$hash = hash('sha256', $salt . $hash);
		echo $hash;
		mysql_query("UPDATE users SET salt='$salt' WHERE username='admin'");
		mysql_query("UPDATE users SET password='$hash' WHERE username='admin'");
		mysql_close();
		header('location: uart.php');
	}
}
If ((isset($_POST['username'])) && (isset($_POST['password']))){
	$username = mysql_real_escape_string($_POST['username']);
	$password = mysql_real_escape_string($_POST['password']);
	$loginQuery = "SELECT UserID, password, salt FROM users WHERE username = '$username';";
	$loginResult = mysql_query($loginQuery);
	If (mysql_num_rows($loginResult) < 1){
		mysql_close();
		header('location: uart.php?error=incorrectLoginStupid');
	}

	$loginData = mysql_fetch_array($loginResult, MYSQL_ASSOC);
	$loginHash = hash('sha256', $loginData['salt'] . hash('sha256', $password));
	If ($loginHash != $loginData['password']){
		mysql_close();
		echo $loginData['password'];
		echo $loginHash;
		header('location: uart.php?error=incorrectLoginTard');
	} else {
		session_regenerate_id();
		$_SESSION['username'] = "admin";
		$_SESSION['userID'] = "1";
		mysql_close();
		echo "you are in the else statement";
		header('location: uart.php');
	}
}
If ((!isset($_SESSION['username'])) || (!isset($_SESSION['userID']))){
	print '
	<html>
	<head>
	<title>UART Test Page - Login</title>
	</head>
	<body>
	<table border="0" align="center">
	<form name="login" action="uart.php" method="post">
	<tr>
	<td>Username: </td><td><input type="text" name="username"></td>
	</tr>
	<tr>
	<td>Password: </td><td><input type="password" name="password"></td>
	</tr>
	<tr>
	<td colspan="2" align="center"><input type="submit" value="Log In"></td>
	</tr>
	</form>
	</table>
	</body>
	</html>
	';
	die();
}
If (isset($_GET['action'])){
	If ($_GET['action'] == "logout"){
		$_SESSION = array();
		session_destroy();
		header('Location: uart.php');
	} else If ($_GET['action'] == "setPassword"){
		print '
		<form name="changePassword" action="uart.php" method="post">
		<input type="hidden" name="action" value="setPassword">
		<p>Enter New Password: <input type="password" name="password1">  Confirm: <input type="password" name="password2"><input type="submit" value="submit"></p>
		</form>
		';
	}
} 
//Webpage display
else {

	print '
		<html>
		<head>
		<title>UART Test Page</title>
		</head>
		<a href = "../">Back to Homepage</a>
		<font face="verdana">
		<p>UART Test Page   <a href="uart.php?action=setPassword">Change Password</a></p>



		<tr style="vertical-align:top;"><td style="vertical-align:top;text-align:center;width:30%;"><b> Enter Action </b></td>
			<td>
			<form method="post" action="" enctype="multipart/form-data" id="entercommand">
				<input type="hidden" name="op" value="textBoxCommand">
				 <input type="text" name="newName" maxlength="20" placeholder="Enter command here">
				<input type="submit" value="Submit">
			</form>
		</td>
		';

		$newtitle = $_POST['newName'];
		$newtitle = mysql_real_escape_string($newtitle);

		$query = mysql_query("INSERT INTO `uart`.`textBoxCommand` (`key`, `command`) VALUES (DEFAULT, '$newtitle');");

		print '</table>';
		mysql_close();
	print '
	<br><br>
	<a href="uart.php?action=logout">Log out</a>
	</font>
	</html>
	';
}
?>
