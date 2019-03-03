<?php
/*
 * Copyright 2019 by Heiko Schäfer <heiko@rangun.de>
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

final class Tracker {

  private $login = null;
  private $siteid = 0;

  function __construct() {

    if(session_status() == PHP_SESSION_NONE) session_start();

    require __DIR__.'/../db_cred.php';

    if(isset($_SESSION['ui'])) {
      $this->login = $_SESSION['ui']['login'];
    }

    $this->siteid = $siteid;

  }

  public function track($action_name) {

    $ip = "";

    if(!empty($_SERVER["HTTP_CLIENT_IP"])) {
      $ip = $_SERVER["HTTP_CLIENT_IP"];
    } else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) {
      $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    } else {
      $ip = $_SERVER["REMOTE_ADDR"];
    }

    $headers = getallheaders();
    $piwik_headers = array("X-Forwarded-For: ".$ip);

    foreach ($headers as $header => $value) {
      if($header == "Host" || $header == "Referer" || $header == "User-Agent" || $header == "Origin" || $header == "Accept-Language") {
	$piwik_headers[] = $header.": ".$value;
      }
    }

    $rq = "https:/rangun.de/piwik/matomo.php?idsite=".$this->siteid."&rec=1&bots=1&apiv=1&action_name=".urlencode($action_name).
    "&send_image=0&url=".urlencode((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]").
    "&rand=".urlencode(openssl_random_pseudo_bytes(4)).(is_null($this->login) ? "" : "&uid=".urlencode($this->login));

    //error_log("{".$rq."}");

    $ch = curl_init($rq);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,  true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,  true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,  false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,  true);
    curl_setopt($ch, CURLOPT_HTTPHEADER,      $piwik_headers);
    curl_exec($ch);
    curl_close($ch);
  }

}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>