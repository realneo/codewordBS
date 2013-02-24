<?php
function md5hash($str){
	$salt="cwBS";
	$md5=md5($str . "_" . $salt);
	return $md5;
}
function stripnonalphanumeric($str){
	$str=ereg_replace("[^A-Za-z0-9]", "", $str);
	return $str;
}
?>