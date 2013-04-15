<?php
require_once 'config.php';

date_default_timezone_set('UTC');

#In case the client is able to retrieve this PHP return error message
if ($_SERVER['REQUEST_URI'] == "func.php") { 
	echo $denied;
	deny();
}
function callfunc($action) {
	if (verify($action) && is_callable($action)) {
		call_user_func($action);
	}
}
function verify($action) {
	foreach (array("search", "showpost", "feedback") as $test) {
		if ($test == $action) return true;
	}
	return false;
}
function search() {
  global $host, $user, $pass, $db;
  
  //Intialize variables
  $result_posts = array();
  $result_comments = array();
  $search_hospital = $search_user = "%";
  $sort = "score";
  $q = $m = $c = $tmp = "";

  //Connect to MySQL
  $mysqli = new mysqli($host, $user, $pass, $db);
  if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
  }
  
  //Checks if the request is from the form or not. If not assume its a new request and load page first then search
  if ($_SERVER['REQUEST_METHOD'] != "POST") {
    $_SESSION['search'] = $mysqli->real_escape_string($_GET['search']);
    $_SESSION['reget'] = 1;
    $mysqli->close();
    include("index.php");
    exit();
  }
  
  $search = $_POST['search'];
  //Extract advanced search queries in the format of key:(value)
  preg_match_all("/(\w+):\(([\w\s']*)\)/", $search, $matches, PREG_SET_ORDER);

  //Cycle through the matched key's
  foreach($matches as $match) {
    //Remove the found string for the query
    $search = str_replace($match[0], "", $search);
    $value = $mysqli->real_escape_string($match[2]);
    //Modify the query based on the key
    switch (strtolower($match[1])) {
        case "sort":
            if (preg_match("/^time|score$/", $value)) $sort = $value;
            break;
        case "user":
            $q = $q."AND u.`user_name` LIKE '%".$value."%' ";
            break;
    }
  }
  
  //Find all words of 4 characters or more
  preg_match_all("/\b([\w']{4,})\b/", $search, $matches, PREG_SET_ORDER);
  
  //Cycle through the matches seperating them from the 3 or less letter words
  foreach($matches as $match) {
    $search = str_replace($match[0], "", $search);
    //Append boolean modifers to words 4+ characters
    $tmp = $tmp." +".$match[1]."*";
  }
  
  //Remove excess whitespaces
  $search_small = trim(preg_replace('/\s\s+/', ' ', $search));
  $search = trim(preg_replace('/\s\s+/', ' ', $tmp));
  
  if ($search_small != "") {
      //Seperate each <3 letter words and modify the SQL
      $search_small = explode(" ",$search_small);
      foreach ($search_small as $short_word) {
        $q = $q."AND p.`post_body` LIKE '%".$short_word."%' ";
      }
  }
  
  //Further prepare environment by setting needed variables if unset
  $search = $mysqli->real_escape_string($search);
  
  //Check posts and get results
  $query = "SELECT p.post_id AS id, p.user_id AS user, u.user_name AS name, p.post_body AS post, FROM_UNIXTIME(p.post_time) AS time, p.num_comments AS comments, p.likes AS likes, MATCH(p.`post_body`) AGAINST ('".$search."') AS score "
          . "FROM fb_posts AS p LEFT JOIN fb_users AS u ON u.user_id = p.user_id "
          . "WHERE MATCH(p.`post_body`) AGAINST ('".$search."' IN BOOLEAN MODE) ".$q.$m
          . "ORDER BY ".$sort." DESC, comments+likes DESC";
  
  //Search posts
  $posts = $mysqli->query($query);
  
  //If no matches against full text index
  if (!$posts->num_rows && $search == "") {
      $query = "SELECT p.post_id AS id, p.user_id AS user, u.user_name AS name, p.post_body AS post, FROM_UNIXTIME(p.post_time) AS time, p.num_comments AS comments, p.likes AS likes "
          . "FROM fb_posts AS p LEFT JOIN fb_users AS u ON u.user_id = p.user_id "
          . "WHERE 1 ".$q.$m
          . "ORDER BY comments+likes DESC";
      $posts = $mysqli->query($query);
  }
  
  //Loop through each result
  while( $row = $posts->fetch_assoc() ){
    $row["comment"] = array();
    $result_posts[] = $row;
  }
  
  //Check comments and get results
  $q = str_replace("p.`post_body`","c.comment_body",$q).$c;
  $query = "SELECT c.comment_id AS comment_id, c.post_id AS id, c.user_id AS user, u.user_name AS name, c.comment_body AS post, FROM_UNIXTIME(c.comment_time) AS time, c.likes AS likes, MATCH(c.comment_body) AGAINST ('".$search."') AS score "
          . "FROM fb_comments AS c LEFT JOIN fb_users AS u ON u.user_id = c.user_id "
          . "WHERE MATCH(c.comment_body) AGAINST ('".$search."' IN BOOLEAN MODE) ".$q
          . "ORDER BY ".$sort." DESC, likes DESC";
  
  //Search comments
  $comments = $mysqli->query($query);
  
  //If no matches against full text
  if (!$comments->num_rows && $search == "") {
      $query = "SELECT c.comment_id AS comment_id, c.post_id AS id, c.user_id AS user, u.user_name AS name, c.comment_body AS post, FROM_UNIXTIME(c.comment_time) AS time, c.likes AS likes "
          . "FROM fb_comments AS c LEFT JOIN fb_users AS u ON u.user_id = c.user_id "
          . "WHERE 1 ".$q
          . "ORDER BY likes DESC";
      $comments = $mysqli->query($query);
  }

  //Loop through each result
  while( $row = $comments->fetch_assoc() ){
    $i = 0;
    //Check if the comment is part of a post result
    foreach($result_posts as $post) {
        if ($post["id"] == $row["id"]) {
            //Add the comment to the post's array
            array_push($result_posts[$i]["comment"],$row);
            $i = 0;
            break;
        }
        $i++;
    }
    //If no parent post found append to comment only results
    if ($i || !count($result_posts)) $result_comments[] = $row;
  }
  
  //Return results and exit
  echo json_encode(array($result_posts, $result_comments));
  
  $mysqli->close();
  exit();
}
function showpost() {
    global $host, $user, $pass, $db;
    //Connect to MySQL
    $mysqli = new mysqli($host, $user, $pass, $db);
    if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
    }
    if (!isset($_GET['id'])) deny();
    $id = $mysqli->real_escape_string($_GET['id']);
    $post = $mysqli->query("SELECT u.user_name AS name, p.user_id AS user, p.post_body AS post, p.post_time AS time, p.likes AS likes FROM fb_posts AS p LEFT JOIN fb_users AS u ON u.user_id = p.user_id WHERE post_id = ".$id);
    if (!$post->num_rows) deny();
    $post = $post->fetch_assoc();
    $comments = $mysqli->query("SELECT u.user_name AS name, c.user_id AS user, c.comment_body AS post, c.comment_time AS time, c.likes AS likes FROM fb_comments AS c LEFT JOIN fb_users AS u ON u.user_id = c.user_id WHERE post_id = ".$id);
    echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'><div data-role='dialog' id='showpost'><div data-role='header'><h1>View Post</h1></div><div data-role='content'>";
    echo "<ul data-role='listview'><li data-role='list-divider' class='center'>Post</li><li><p class='wrap'>".$post['post']."</p><br><p class='wrap'><strong>Posted By:</strong> ".$post['name'].($post['likes'] > 0 ? " <strong>Likes:</strong> ".$post['likes'] : "")." <strong>Created On:</strong> ".date("m/d/y g:i a",$post['time'])."</p></li>";
    if ($comments->num_rows) {
            echo "<li data-role='list-divider' class='center'>Comments</li>";
            while( $row = $comments->fetch_assoc() ){
                    echo "<li><p class='wrap'>".$row['post']."</p><br><p class='wrap'><strong>Posted By:</strong> ".$row['name'].($row['likes'] > 0 ? " <strong>Likes:</strong> ".$row['likes'] : "")." <strong>Created On:</strong> ".date("m/d/y g:i a",$row['time'])."</p></li>";
            }
    }
    echo "</ul></div><div data-role='footer'></div>";
    exit();
}
function feedback() {
    $fb = fopen("feedback.txt", "a");
    fwrite($fb, "\n".date(DATE_RFC822)." ".$_SERVER['REMOTE_ADDR']."\n".$_POST['feedback']);
    fclose($fb);
    exit();
}
function deny() {
    header('HTTP/1.0 404 Not Found');
    exit(); 
}
function versions() {
    return "<p class=\"version\">Version 1.0</p><ul><li>Site is now up!</li></ul>";
}
?>