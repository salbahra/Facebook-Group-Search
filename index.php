<?php

#Start session
if(!isset($_SESSION)) session_start(); 

#Check if mobile
require_once 'mobile_detect.php';
$detect = new Mobile_Detect();
$layout = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'desktop');

#Redirect to mobile site
if ($layout != "desktop" && !isset($_REQUEST["noredirect"])) {include 'mobile.php'; exit();}

#Required scripts
require_once "func.php";

if (isset($_REQUEST['action']) && !isset($_SESSION['reget'])) callfunc($_REQUEST['action']);
?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link href="css/main.css" rel="stylesheet" media="screen" type="text/css" />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<title>Group Name</title>
</head>
<body>
	<div id="background"></div>	
	<div id="loading" class="overlay">Loading...</div>
	<div id="aboutlink"><a href="javascript:about()">About</a></div>
	<div id="feedbacklink"><a href="javascript:feedback()">Feedback</a></div>
	<div id="header">
		<div id="title">Group Name</div>
		<p>Search the Facebook group by entering your search query below</p>
		<form id="fbsearch" action="javascript:get()" method="post">
			<input id="search" placeholder="Enter search query" name="search" type="text" maxlength=255>
			<input type="submit" style="position: absolute; left: -9999px; width: 1px; height: 1px;">
		</form>
		<div id="new"></div>
	</div>
	<div id="content">
	</div>
	<script src="js/main.js" type="text/javascript"></script>
</body>
</html> 
