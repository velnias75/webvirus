<?php

require 'classes/mysql_base.php';

session_start();

if(isset($_POST['login']) && isset($_POST['pass'])) {
  
  $ui = MySQLBase::instance()->login($_POST['login'], $_POST['pass']);
  
  if(is_string($ui)) {
    $_SESSION['error'] = $ui;
  } else {
    $_SESSION['ui'] = $ui;
    setcookie("login", $_POST['login'], time()+60*60*24*365);
    setcookie("magic", $ui['epass'], time()+60*60*24*365);
  }

} else if(isset($_POST['logout'])) {
  unset($_SESSION['error']);
  unset($_SESSION['ui']);
  setcookie("login", null);
  setcookie("magic", null);
}

header("Location: ".dirname($_SERVER['REQUEST_URI'])."/".(isset($_POST['q']) ? "?".urldecode($_POST['q']) : ""), true, 302);

?>
