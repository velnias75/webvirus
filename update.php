<?php

require 'classes/mysql_base.php';

session_start();

if(MySQLBase::instance()->update_allowed()) {

  if(!empty($_FILES) && isset($_SESSION['ui']) && $_SESSION['ui']['admin']) {

    $templine = '';
    $lines = file($_FILES['dateiupload']['tmp_name']);

    foreach($lines as $line) {

      if(substr($line, 0, 2) == '--' || $line == '')
	continue;

      $templine .= $line;

      if(substr(trim($line), -1, 1) == ';') {

	MySQLBase::instance()->con()->query($templine) or print('Error performing query \'<strong>'.
	  $templine.'\': '.MySQLBase::instance()->con()->error.'</strong>');
	$templine = '';
      }
  }
  
  } else if(!(isset($_SESSION['ui']) && $_SESSION['ui']['admin'])) {
    echo "<pre>Nur Administratoren d&uuml;rfen ein Datenupdate durchf&uuml;hren!</pre>\n";
  }

}

header("Location: ".dirname($_SERVER['REQUEST_URI'])."/".(isset($_POST['q']) ? "?".urldecode($_POST['q']) : ""), true, 302);

?>
