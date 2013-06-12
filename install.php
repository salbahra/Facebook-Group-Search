<?php
if (file_exists("config.php")) header("Location: index.php");

if (isset($_REQUEST['action']) && $_REQUEST['action'] == "new_config" && !file_exists("config.php")) {
    new_config();
    exit();
}

#New config setup
function new_config() {
    $config = "<?php\n";
    $needed = array("gid","my_url","host","user","pass","db","override","access_token");
    foreach ($needed as $key) {
        if (!isset($_REQUEST[$key])) fail();
        $data = $_REQUEST[$key];
        if ($key == "access_token") {
            $config .= "$".$key." = 'access_token=".$data."';\n";
        } else {
            $config .= "$".$key." = '".$data."';\n";            
        }
    }
    $file = fopen("config.php", 'w');
    if (!$file) fail();
    $r = fwrite($file,$config."?>");
    if (!$r) fail();

    $output = shell_exec('crontab -l');
    file_put_contents('/tmp/crontab.txt', $output.'* * * * * php '.dirname(__FILE__).'/import.php >/dev/null 2>&1'.PHP_EOL);
    exec('crontab /tmp/crontab.txt');

    echo 1;
}

function fail() {
    echo 0;
    exit();
}

?>

<!DOCTYPE html>
<html>
	<head>
    	<title>New Install</title> 
        <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
        <meta name="viewport" content="initial-scale=1.0,user-scalable=no,maximum-scale=1" media="(device-height: 568px)" />
        <meta content="yes" name="apple-mobile-web-app-capable">
        <meta name="apple-mobile-web-app-title" content="App Name">
        <link rel="apple-touch-icon" href="img/icon.png">
    	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.css" />
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script src="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.js"></script>
        <script>
            function showerror(msg) {
                // show error message
                $.mobile.loading( 'show', {
                    text: msg,
                    textVisible: true,
                    textonly: true,
                    theme: 'a'
                });
            }
            function submit_config() {
                $.mobile.showPageLoadingMsg()
                $.get("install.php","action=new_config&"+$("#options").find(":input").serialize(),function(data){
                    if (data == 1) {
                        $.mobile.hidePageLoadingMsg()
                        showerror("Settings have been saved. Please wait while your redirected to the login screen!")
                        setTimeout(function(){location.reload()},2500);
                    } else {
                        $.mobile.hidePageLoadingMsg()
                        showerror("Settings have NOT been saved. Check folder permissions and file paths then try again.")
                        setTimeout(function(){$.mobile.loading('hide')}, 2500);                    
                    }
                })
            }
        </script>
    </head> 
    <body>
        <div data-role="page" id="install" data-close-btn="none">
        	<div data-role="header" data-position="fixed">
                <h1>New Install</h1>
                <a href="javascript:submit_config()" class="ui-btn-right">Submit</a>
           </div>
        	<div data-role="content">
                <form action="javascript:submit_config()" method="post" id="options">
                    <ul data-inset="true" data-role="listview">
                        <li data-role="list-divider">App Configuration</li>
                        <li>
                            <div data-role="fieldcontain">
                                <label for="my_url">Website URL:</label>
                                <input type="text" name="my_url" id="my_url" value="http://mysite.com/index.php" />
                                <label for="override">Override (0 or 1):</label>
                                <input type="text" name="override" id="override" value="1" />
                            </div>
                        </li>
                    </ul>
                    <ul data-inset="true" data-role="listview">
                        <li data-role="list-divider">Facebook Group Information</li>
                        <li>
                            <div data-role="fieldcontain">
                                <label for="username">Group ID:</label>
                                <input type="text" name="gid" id="gid" value="" />
                                <label for="access_token">Access Token:</label>
                                <input type="text" name="access_token" id="access_token" value="" />                                
                            </div>
                        </li>
                    </ul>
                    <ul data-inset="true" data-role="listview">
                        <li data-role="list-divider">MySQL Information</li>
                        <li>
                            <div data-role="fieldcontain">
                                <label for="host">Hostname:</label>
                                <input type="text" name="host" id="host" value="localhost" />
                                <label for="user">Username:</label>
                                <input type="text" name="user" id="user" value="" />
                                <label for="pass">Password:</label>
                                <input type="password" name="pass" id="pass" value="" />
                                <label for="db">Database:</label>
                                <input type="text" name="db" id="db" value="" />
                            </div>
                        </li>
                    </ul>
                    <input type="submit" value="Submit" />
                </form>
            </div>
        </div>
    </body>
</html>
