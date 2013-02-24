<?php
$user=stripnonalphanumeric(strtolower($_POST['user']));
$pass=$_POST['pass'];

if(isset($_POST['user']) && isset($_POST['pass'])){
	$valid=mysqli_num_rows($sql->query("SELECT * FROM accounts WHERE username=\"" . $user . "\" AND hash=\"" . md5hash($pass) . "\""));
	if($valid){
		echo("Logged in!\n");
	}
	else{
		echo("Invalid username/password!");
		//echo("<br />" . md5hash($pass));
	}
}
else{
	echo("<form action=\"?admin\" method=\"post\">\n");
	echo("<p>\n");
	echo("Username: <input type=\"text\" name=\"user\" /><br />\n");
	echo("Password: <input type=\"password\" name=\"pass\" /><br />\n");
	echo("<input type=\"submit\" value=\"Submit\" /><br />\n");
	echo("</p>\n");
	echo("</form>\n");
}
?>