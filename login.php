<?php

require 'classes/mysql_base.php';

session_start();

if(isset($_POST['btn']) && isset($_POST['btn']['login']) && 
  isset($_POST['login']) && isset($_POST['pass'])) {
  
  $ui = MySQLBase::instance()->login($_POST['login'], $_POST['pass']);
  
  if(is_string($ui)) {
    $_SESSION['error'] = $ui;
  } else {
    $_SESSION['ui'] = $ui;
  }
}

if(isset($_POST['btn']) && isset($_SESSION['ui'])) {
  
  if(isset($_POST['btn']['create']) && $_SESSION['ui']['admin'] && isset($_POST['display']) && 
    isset($_POST['login_new']) && isset($_POST['pass_new'])) {
    MySQLBase::instance()->new_user($_POST['display'], $_POST['login_new'], $_POST['pass_new']);
  } else if(isset($_POST['btn']['chg'])) {
    MySQLBase::instance()->chg_pass($_SESSION['ui']['id'], $_POST['pass_chg']);
  } else if(isset($_POST['btn']['logout'])) {
    
    unset($_SESSION['error']);
    unset($_SESSION['ui']);
    
    session_write_close();
  }
}

header("Location: ".dirname($_SERVER['REQUEST_URI'])."/".(isset($_POST['q']) ? "?".urldecode($_POST['q']) : ""), true, 302);

?>
