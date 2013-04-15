<?php
require_once '../func.php';

// Opens a connection to a MySQL server
$connection=mysql_connect ("localhost", $user, $pass);
if (!$connection) {
  die('Not connected : ' . mysql_error());
}

// Set the active MySQL db
$db_selected = mysql_select_db($db, $connection);
if (!$db_selected) {
  die ('Can\'t use db : ' . mysql_error());
}

// Select all the rows in the markers table
$query = "SELECT p.post_id AS id, p.post_body AS message, FROM_UNIXTIME(p.post_time) AS time, m.lat AS lat, m.lng AS lng "
         . "FROM fb_posts AS p LEFT JOIN fb_meta AS m ON m.post_id = p.post_id WHERE m.lat>0";
$result = mysql_query($query);
if (!$result) {
  die('Invalid query: ' . mysql_error());
}

$results = array();

// Iterate through the rows, printing XML nodes for each
while ($row = @mysql_fetch_assoc($result)){
	$results[] = $row;
}

// End XML file
echo json_encode($results);

?>