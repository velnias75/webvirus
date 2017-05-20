<?php
/*
 * Copyright 2017 by Heiko Schäfer <heiko@rangun.de>
 *
 * This file is part of webvirus.
 *
 * webvirus is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * webvirus is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with webvirus.  If not, see <http://www.gnu.org/licenses/>.
 */

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
