<?php

#Start session
if(!isset($_SESSION)) session_start(); 

#Required scripts
require_once "func.php";

if (isset($_REQUEST['action']) && !isset($_SESSION['reget'])) callfunc($_REQUEST['action']);
?>
<!DOCTYPE html> 
<html> 
	<head> 
	<title>Ross Search</title> 
	<meta name="viewport" content="width=320.1, initial-scale=1.0">
	<meta content="yes" name="apple-mobile-web-app-capable">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="App Name">
	<link rel="apple-touch-icon" href="img/icon.png">
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.css" />
	<style type="text/css">#new a:link,#new a:visited{color:#303030}.wrap{white-space:normal}.center{text-align: center}</style>
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
	<script src="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.js"></script>
	<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=APIKEY&sensor=false"></script>
    <script type="text/javascript" src="http://google-maps-utility-library-v3.googlecode.com/svn/tags/markerclustererplus/2.0.14/src/markerclusterer_packed.js"></script>
	<script>
		(function(){
			var p, l, r = window.devicePixelRatio;
			if (navigator.platform === "iPad") {
				p = r === 2 ? "img/startup-tablet-portrait-retina.png" : "img/startup-tablet-portrait.png";
				l = r === 2 ? "img/startup-tablet-landscape-retina.png" : "img/startup-tablet-landscape.png";
				document.write('<link rel="apple-touch-startup-image" href="'+l+'" media="screen and (orientation: landscape)"><link rel="apple-touch-startup-image" href="'+p+'" media="screen and (orientation: portrait)">');
			} else {
				p = r === 2 ? "img/startup-retina.png": "img/startup.png";
                if (window.screen.height == 568) p = "img/startup-iphone5-retina.png";
				document.write('<link rel="apple-touch-startup-image" href="'+p+'">');
			}
		})()
	</script>
</head> 
<body> 

<!-- Start of first page -->
<div data-role="page" id="start" data-dom-cache="true">

	<div data-role="header" data-position="fixed">
		<div><h1 class="center">Group Name</h1></div>
	</div>

	<div data-role="content">
		<p class="center">Search the Facebook group by entering your search query below</p><br>
		<form id="fbsearch" action="javascript:get()" method="post">
			<input type="search" name="search" id="search" value="">
			<input type="submit" value="Submit">
		</form>
                <br><p class="center" id="new"></p>
	</div>

	<div data-role="footer" data-position="fixed" data-tap-toggle="false" class=".ui-bar" style="text-align:center">
            <div data-role="controlgroup" data-type="horizontal">
                <a data-icon="search" data-theme="a" data-role="button" class="ui-btn-active ui-state-persist" href="#start">Search</a>
                <a data-icon="grid" data-theme="a" data-role="button" href="#mapview" data-transition="fade">Map</a>
                <a data-icon="info" data-theme="a" data-role="button" href="#about" data-transition="fade">About</a>
            </div>
	</div>
</div>

<!-- Start of results page -->
<div data-role="page" id="results">

	<div data-role="header" data-position="fixed">
	</div>

	<div data-role="content" id="post_results">
	</div>

    	<div data-role="footer" data-position="fixed" data-tap-toggle="false" class=".ui-bar" style="text-align:center">
            <div data-role="controlgroup" data-type="horizontal">
                <a data-icon="search" data-theme="a" data-role="button" data-direction="reverse" href="#start">Search</a>
                <a data-role="button" data-theme="a" class="ui-btn-active ui-state-persist" data-direction="reverse" href="#results">Results</a>
                <a data-icon="grid" data-theme="a" data-role="button" href="#mapview" data-transition="fade">Map</a>
                <a data-icon="info" data-theme="a" data-role="button" href="#about" data-transition="fade">About</a>
            </div>
	</div>
</div>

<!-- Start of map page -->
<div data-role="page" id="mapview" style="width:100%; height:100%;">
	<div data-role="header">
            <input type="search" name="map-search" placeholder="Filter by..." id="map-search" value="" />
        </div>
	<div data-role="content" style="width:100%; height:100%; padding:0;"> 
		<div id="map_canvas"></div>
	</div>
    	<div data-role="footer" data-position="fixed" data-tap-toggle="false" class=".ui-bar" style="text-align:center">
            <div data-role="controlgroup" data-type="horizontal">
                <a data-icon="search" data-theme="a" data-role="button" data-transition="fade" data-direction="reverse" href="#start">Search</a>
                <a data-icon="grid" data-theme="a" data-role="button" class="ui-btn-active ui-state-persist" href="#mapview" data-transition="fade">Map</a>
                <a data-icon="info" data-theme="a" data-role="button" href="#about" data-transition="fade">About</a>
            </div>
        </div>
</div>

<!-- Start of about page -->
<div data-role="page" id="about">

	<div data-role="header" data-position="fixed">
	</div>

	<div data-role="content">
		<div data-role="collapsible-set" data-content-theme="d">
			<div data-role="collapsible" data-collapsed="false">
				<h3>Background</h3>
				<p>Some background information here.</p>
			</div>
			<div data-role="collapsible">
				<h3>Version History</h3>
                <?php echo versions(); ?>
			</div>
            <div data-role="collapsible">
				<h3>Feedback</h3>
                    <form id="feedbacka" action="javascript:submit_feedback()" method="post">
                        <label for="feedbacki">Feedback:</label>
                        <textarea name="feedback" placeholder="Enter feedback here..." id="feedbacki"></textarea>
                        <input type="submit" value="Submit" />
                    </form>
			</div>
		</div>
	</div>
	<div data-role="footer" data-position="fixed" data-tap-toggle="false" class=".ui-bar" style="text-align:center">
            <div data-role="controlgroup" data-type="horizontal">
                <a data-icon="search" data-theme="a" data-role="button" data-transition="fade" data-direction="reverse" href="#start">Search</a>
                <a data-icon="grid" data-theme="a" data-role="button" href="#mapview" data-transition="fade" data-direction="reverse">Map</a>
                <a data-icon="info" data-theme="a" data-role="button" class="ui-btn-active ui-state-persist" href="#about">About</a>
            </div>
	</div>

</div>

<script src="js/mobile.js"></script>
</body>
</html>