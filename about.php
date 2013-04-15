<?php

#Start session
if(!isset($_SESSION)) session_start();

#If not an AJAX request deny it
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')) { header("HTTP/1.0 401 Authorization Required"); exit(); }

#Required scripts
require_once "func.php";

?>
<p class="title">About</p>
<div id="abouttext">
	<span class="subtitle">Background</span>
		<p>Some background information</p>
	<span class="subtitle">Version History</span>
        <?php echo versions(); ?>
</div>