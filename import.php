<?php

  require_once "config.php";

  //Intialize array
  $results = array();
  //Get the current time including microseconds
  $time = intval(microtime(true));
  /*
  Set the interval in seconds used to restrict chunks of data retrieved from Facebook.
  This is to prevent errors when requesting all the data at once.
  */ 
  $int = 7776000;
  //Set the HTTP header info
  $opts = array('http'=>array('header' => "User-Agent:FBExport/1.0\r\n"));
  $context = stream_context_create($opts);
  
  //Connect to MySQL
  $mysqli = new mysqli($host, $user, $pass, $db);
  if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
  }
  
  //Check if tables exist
  $needed = array("fb_posts","fb_comments","fb_users");
  foreach ($needed as $value) {
    $result = $mysqli->query("SHOW TABLES LIKE '".$value."'");
    if ($result->num_rows == 0) {
      create_tables();
      break;
    }
  }

  //Get the newest post in MySQL
  $result = $mysqli->query("SELECT post_time FROM fb_posts ORDER BY post_time DESC LIMIT 1")->fetch_assoc();
  //Store the posts creation time to compare against Facebook
  $last_post = (int) $result['post_time'];
  
  //If ovverride is enabled force start from the first post (this is hardcoded for my group...fix later)
  if ($override == 1) $last_post = 1291847525;
  
  do {
    //Construct the FQL query
    $fql_query_url = 'https://graph.facebook.com/'
      . 'fql?q=SELECT+post_id,+actor_id,+created_time,+message,+comments,+likes+FROM+stream+WHERE+source_id='.$gid.'+AND+created_time<'.$time.'+AND+created_time>'.($time - $int).'+LIMIT+5000'
      . '&' . $access_token;
    //Run the query
    $fql_query_result = file_get_contents($fql_query_url,false,$context);
    //Fixes the JSON data returned
    $fql_query_obj = json_decode(preg_replace('/("\w+"):(\d+)/', '\\1:"\\2"', $fql_query_result), true);
    //Merge the results from this timespan with the rest of the results
    $results = array_merge($results, $fql_query_obj['data']);
    //Change the interval and run the loop again until no data is returned
    $time -= $int;
  } while (count($fql_query_obj) != 0 && $time > $last_post);
    
  //Prepare statements for entry to MySQL

  //Get total comments for a given post in MySQL
  $update_stmt = $mysqli->prepare("SELECT count(post_id) FROM fb_comments WHERE post_id=? LIMIT 1");
  $update_stmt->bind_param("s",$post_id);
  //Insert new post into MySQL
  $post_stmt = $mysqli->prepare("INSERT INTO fb_posts (post_id, user_id, post_body, post_time, num_comments, likes) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE likes = VALUES(likes), num_comments = VALUES(num_comments)");
  $post_stmt->bind_param("ssssss",$post_id,$user,$body,$time,$num_comments,$likes);
  //Insert new comment into MySQL
  $comment_stmt = $mysqli->prepare("INSERT INTO fb_comments (comment_id, post_id, user_id, comment_body, comment_time, likes) VALUES(?,?,?,?,?,?) ON DUPLICATE KEY UPDATE likes = VALUES(likes)");
  $comment_stmt->bind_param("ssssss",$comment_id,$post_id,$user,$body,$time,$likes);

  //Cycle through each post
  foreach ($results as $post) {
    //Explode the post id which should be groupid_postid and discard the groupid
    list(,$post_id) = explode("_",$post["post_id"]);
    //Set the variables for the SQL query from the Facebook results
    $user = $post["actor_id"]; $body = $post["message"]; $time = $post["created_time"]; $num_comments = $post["comments"]["count"]; $likes = $post["likes"]["count"];
    //Get the number of comments for the current post
    $update_stmt->execute();
    $update_stmt->bind_result($old_comments);
    $update_stmt->fetch();
    $update_stmt->free_result();
    //Insert the current post
    $post_stmt->execute();
    //If the number of comments changed then query Facebook for the comments and insert them
    if ((int) $num_comments > $old_comments) {
      //Construct the FQL Query
      $query = "https://graph.facebook.com/fql?q=SELECT+likes,+id,+time,+text,+fromid+FROM+comment+WHERE+post_id=%22".$post['post_id']."%22&".$access_token;
      //Execute the query
      $comments = file_get_contents($query);
      //Fix the JSON output
      $comments = json_decode(preg_replace('/("\w+"):(\d+)/', '\\1:"\\2"', $comments), true);
      //Cycle through each comment
      foreach ($comments["data"] as $comment) {
        //Set the variables needed for comment insertion
      	$comment_id = $comment["id"]; $user = $comment["fromid"]; $body = $comment["text"]; $time = $comment["time"]; $likes = $comment["likes"];
        //Insert the current comment into the database
        $comment_stmt->execute();
      }
    }
  }
  //Close the prepared query connection  
  $post_stmt->close();
  $comment_stmt->close();
  
  //Now to update the users; so find user ids in posts that are not in the user database
  $unknowns = $mysqli->query("SELECT DISTINCT p.user_id FROM `fb_posts` AS p LEFT JOIN fb_users AS u ON u.user_id = p.user_id WHERE u.user_id IS NULL");
  //Prepare statement for user insertion into SQL
  $user_stmt = $mysqli->prepare("INSERT INTO fb_users VALUES(?,?)");
  $user_stmt->bind_param("ss",$user,$name);
  //Cycle through every unknown user and query Facebook for information
  while( $row = $unknowns->fetch_assoc() ) {
    //Build the FQL Query
    $query = "https://graph.facebook.com/fql?q=SELECT+name+FROM+user+WHERE+uid=".$row['user_id']."&".$access_token;
    //Execute the query
    $result = file_get_contents($query);
    //Fix the JSON output
    $result = json_decode(preg_replace('/("\w+"):(\d+)/', '\\1:"\\2"', $result), true);
    //Prepare the variables for MySQL
    $user = $row['user_id']; $name = $result['data'][0]['name'];
    //Insert the user into the database
    $user_stmt->execute();
  }

  //Close all open connections to MySQL and exit
  $user_stmt->close();
  $mysqli->close();
  exit();

function create_tables() {
  global $mysqli;

  $tables = "CREATE TABLE IF NOT EXISTS `fb_comments` (
  `comment_id` bigint(18) NOT NULL,
  `post_id` bigint(18) NOT NULL,
  `user_id` int(12) NOT NULL,
  `comment_body` longtext NOT NULL,
  `comment_time` int(11) NOT NULL,
  `likes` smallint(6) NOT NULL,
  UNIQUE KEY `comment_id` (`comment_id`),
  KEY `user_id` (`user_id`),
  FULLTEXT KEY `comment_body` (`comment_body`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
    . "CREATE TABLE IF NOT EXISTS `fb_posts` (
  `post_id` bigint(18) NOT NULL,
  `user_id` int(12) NOT NULL,
  `post_body` longtext NOT NULL,
  `post_time` int(11) NOT NULL,
  `num_comments` smallint(6) NOT NULL,
  `likes` smallint(6) NOT NULL,
  UNIQUE KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`),
  FULLTEXT KEY `post_body` (`post_body`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;"
    . "CREATE TABLE IF NOT EXISTS `fb_users` (
  `user_id` int(12) NOT NULL COMMENT 'User ID',
  `user_name` varchar(100) NOT NULL COMMENT 'User Full Name',
  UNIQUE KEY `user_id` (`user_id`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";

  $result = $mysqli->query($tables);
}


?>
