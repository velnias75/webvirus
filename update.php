<?php
/*
 * Copyright 2017-2019 by Heiko Schäfer <heiko@rangun.de>
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

require_once 'TwitterAPIExchange.php';

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

    // Twitter
    $settings = array(
      'oauth_access_token' => $_SESSION['ui']['oauth_access_token'],
      'oauth_access_token_secret' => $_SESSION['ui']['oauth_access_token_secret'],
      'consumer_key' => $_SESSION['ui']['consumer_key'],
      'consumer_secret' => $_SESSION['ui']['consumer_secret']
      );

    try {
      $twitter = new TwitterAPIExchange($settings);
      $twitter->buildOauth("https://api.twitter.com/1.1/statuses/update.json", "POST")
      ->setPostfields(array('status' => 'Neue hirnlose Schrott- & Rentnerfilme wurden soeben auf https://rangun.de/db/index.php hinzugefügt!'))
      ->performRequest();
    } catch(Exception $e) {
      echo '<pre>Twitter-API-Exception: ',  $e->getMessage(), "</pre>\n";
    }

    // Reddit (seems not to work from webhoster STRATO)
    /*
      *    $ch = curl_init("https://www.reddit.com/api/v1/access_token");
      *    curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: rangun.de: v1.0 (by /u/Velnias75)"));
      *    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      *    curl_setopt($ch, CURLOPT_USERNAME, curl_escape($ch, "aaa:bbb"));
      *    curl_setopt($ch, CURLOPT_POST, true);
      *    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=password&username=uuu&password=ppp");
      *    $ansa = json_decode(curl_exec($ch), true);
      *    curl_close($ch);
      *
      *    if(!isset($ansa['error'])) {
      *      echo "<pre>Reddit access_token: ".$ansa['access_token']."</pre>\n";
  } else {
    echo "<pre>Reddit-API-Exception: ".$ansa['message']." (".$ansa['error'].")</pre>\n";
  } */

  } else if(!(isset($_SESSION['ui']) && $_SESSION['ui']['admin'])) {
    echo "<pre>Nur Administratoren d&uuml;rfen ein Datenupdate durchf&uuml;hren!</pre>\n";
  }

}

header("Location: ".dirname($_SERVER['REQUEST_URI'])."/".(isset($_POST['q']) ? "?".urldecode($_POST['q']) : ""), true, 302);

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
