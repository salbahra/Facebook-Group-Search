<?php

//Settings

//Facebook group ID
$gid = '';
//Website URL
$my_url = 'http://mysite.com/index.php';
//MySQL hostname
$host = 'localhost';
//MySQL username
$user = '';
//MySQL password
$pass = '';
//MySQL database
$db = '';

/*
Override setting forces the import to grab ALL posts from Facebook starting with the very first one.
Setting override to 0 disables that and scans from the newest post in MySQL to the newest available.
The reason for this setting is to allow comments to be updated from previous posts.

Unless your group is VERY large you should be okay running this every 1 hour
*/
$override = 1;

//Facebook Access token in the form of 'access_token=token'
$access_token = 'access_token=';


//End of settings

//Set denied message
$denied = "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\"><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p>The requested URL ".$_SERVER['REQUEST_URI']." was not found on this server.</p><hr></body></html>";
?>
