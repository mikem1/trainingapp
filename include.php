<?php
if($_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['REMOTE_ADDR'] == "::1"){
	$sqlh = "localhost";
	$sqlu = "root";
	$sqlp = "";
	$sqld = "phplogin";
}else{
	$sqlh = "localhost";
	$sqlu = "root";
	$sqlp = "53c26c3c7bfa86b1dbd4bfb90309bdb69b3c101444ff3746";
	$sqld = "phplogin";	
}
?>