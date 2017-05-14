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
}

if(isset($_POST['action']) && isset($_SESSION['ui'])) {
  
  if($_POST['action'] == "Anlegen" && $_SESSION['ui']['admin'] && isset($_POST['display']) && 
    isset($_POST['login_new']) && isset($_POST['pass_new'])) {
    MySQLBase::instance()->new_user($_POST['display'], $_POST['login_new'], $_POST['pass_new']);
  } else if(urldecode($_POST['action']) == "Ändern" && !empty($_POST['action'])) {
    MySQLBase::instance()->chg_pass($_SESSION['ui']['id'], $_POST['pass_chg']);
  } else if($_POST['action'] == "Ausloggen") {
    unset($_SESSION['error']);
    unset($_SESSION['ui']);
    setcookie("login", null);
    setcookie("magic", null);
  }
}

header("Location: ".dirname($_SERVER['REQUEST_URI'])."/".(isset($_POST['q']) ? "?".urldecode($_POST['q']) : ""), true, 302);

?>
