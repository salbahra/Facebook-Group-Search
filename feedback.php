<?php

#Start session
if(!isset($_SESSION)) session_start();

#If not an AJAX request deny it
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || ($_SERVER['HTTP_X_REQUESTED_WITH'] != 'XMLHttpRequest')) { header("HTTP/1.0 401 Authorization Required"); exit(); }

#Required scripts
require_once "func.php";

?>
<p class="title">Feedback</p>
<div>
    <p class="thanks">Your feedback is greatly appreciated and will help drive the development of this website. All feedback is read and is anonymous.</p>
    <form id="submit_feedback" action="javascript:submit_feedback()" method="post">    
        <textarea class="boxsizingBorder" name="feedback" rows="15" placeholder="Enter your feedback here..."></textarea><br>
        <input type="submit" value="Submit" />
    </form>
</div>