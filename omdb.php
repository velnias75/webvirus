<?php

session_start();
  
if(isset($_GET['search']) && isset($_SESSION['ui'])) {
  header("Location: http://www.omdb.org/search/movies/?search[text]=".$_GET['search']);
} else if(isset($_GET['q'])) {
  header("Location: http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI'])."/?".urldecode($_GET['q']));
} else {
  header("Location: http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI'])."/");
}

?>
