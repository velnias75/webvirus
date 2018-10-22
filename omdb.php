<?php
/*
 * Copyright 2017-2018 by Heiko Schäfer <heiko@rangun.de>
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

if(isset($_GET['cover-oid'])) {

  $ch = curl_init("https://www.omdb.org/movie/".$_GET['cover-oid']);
  curl_setopt($ch, CURLOPT_USERAGENT, "db-webvirus/1.0");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  $doc = DOMDocument::loadHTML(curl_exec($ch));
  curl_close($ch);

  $ch = curl_init($doc->getElementById("left_image")->getAttribute("src"));
  curl_setopt($ch, CURLOPT_USERAGENT, "db-webvirus/1.0");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  //curl_setopt($ch, CURLOPT_HEADER, true);
  $pic = curl_exec($ch);
  curl_close($ch);

  header("Content-Type: image");
  header("Cache-Control: public");

  echo $pic;

} else if(isset($_GET['mid']) && isset($_GET['oid']) && isset($_GET['url']) && isset($_SESSION['ui'])) {
  MySQLBase::instance()->update_omdb_id($_GET['mid'], $_GET['oid']);
  header("Location: ".urldecode($_GET['url']));
} else if(isset($_GET['search']) && isset($_SESSION['ui'])) {
  header("Location: http://www.omdb.org/search/movies/?search[text]=".$_GET['search']);
} else if(isset($_GET['id']) && isset($_SESSION['ui'])) {
  header("Location: https://www.omdb.org/movie/".$_GET['id']);
} else if(isset($_GET['q'])) {
  header("Location: ".MySQLBase::instance()->protocol()."://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI'])."/?".urldecode($_GET['q']));
} else {
  header("Location: ".MySQLBase::instance()->protocol()."://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI'])."/");
}

?>
