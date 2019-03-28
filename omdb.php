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
require 'classes/tracker.php';

function noCoverPic() {
    $filename = "img/nocover.png";
    $handle = fopen($filename, "rb");
    $p = fread($handle, filesize($filename));
    fclose($handle);

    return $p;
}

if(!isset($_GET['cover-oid'])) {
  session_start();
}

if(isset($_GET['cover-oid'])) {

  $headers = [];
  $proxy = MySQLBase::instance()->proxy();
  if(isset($_SERVER['HTTP_REFERER'])) {
    $ref_url = parse_url($_SERVER['HTTP_REFERER']);
    $req_url = parse_url((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")."://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
  } else {
    $ref_url = null;
    $req_url = null;
  }

  if($ref_url['host'] != $req_url['host']) (new Tracker())->track("OMDB cover request for ".$_GET['cover-oid']." {".$_SERVER['HTTP_USER_AGENT']."}");

  $ch = curl_init("https://www.omdb.org/movie/".$_GET['cover-oid']);
  curl_setopt($ch, CURLOPT_USERAGENT, "db-webvirus/1.0");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER,     array("Accept-Language: de-DE,en;q=0.5"));

  if(!is_null($proxy)) {
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
  }

  $libxml_previous_state = libxml_use_internal_errors(true);
  $doc = DOMDocument::loadHTML(curl_exec($ch));

  if(curl_errno($ch)) error_log("Curl error (loading omdb movie page): ".curl_error($ch));

  libxml_clear_errors();
  libxml_use_internal_errors($libxml_previous_state);
  curl_close($ch);

  if(!isset($_GET['abstract']) && !empty($doc->getElementById("left_image"))) {
    $ch = curl_init($doc->getElementById("left_image")->getAttribute("src"));
    curl_setopt($ch, CURLOPT_USERAGENT, "db-webvirus/1.0");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    //curl_setopt($ch, CURLOPT_HTTPHEADER,     array("If-None-Match: ".trim($_SERVER["HTTP_IF_NONE_MATCH"]));
    curl_setopt($ch, CURLOPT_HTTPHEADER,     array("Accept-Language", "de,en-US;q=0.7,en;q=0.3"));

    if(!is_null($proxy)) {
      curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
      curl_setopt($ch, CURLOPT_PROXY, $proxy);
    }

    curl_setopt($ch, CURLOPT_HEADERFUNCTION,
    function($curl, $header) use (&$headers) {

      $len = strlen($header);
      $header = explode(':', $header, 2);

      if(count($header) < 2) return $len;

      $name = strtolower(trim($header[0]));

      if(!array_key_exists($name, $headers)) {
        $headers[$name] = [trim($header[1])];
      } else {
        $headers[$name][] = trim($header[1]);
      }

      return $len;
    }
    );

    $pic = curl_exec($ch);

    if(curl_errno($ch)) error_log("Curl error (loading omdb movie picture): ".curl_error($ch));

    curl_close($ch);
  } else $pic = null;

  if(isset($_GET['abstract']) && !empty($doc->getElementById("abstract"))) {
    $text = utf8_decode($doc->getElementById("abstract")->nodeValue);
    header("Content-type: text/plain; charset=".strtolower(mb_detect_encoding($text,"UTF-8, ISO-8859-15, ISO-8859-1", true)));
    echo $text;
    exit;
  } 

  if(is_null($pic) || md5($pic) == "106f2c74718ffe31354f44a40cc2f4a8") {
    $pic = noCoverPic();
    $headers['content-type'][0] = "image/png";
  }

  $etagHeader = (isset($_SERVER["HTTP_IF_NONE_MATCH"]) ? trim($_SERVER["HTTP_IF_NONE_MATCH"]) : false);
  $etag       = sprintf('"%s"', substr($headers['etag'][0], 1 , -1));

  header("ETag: ".$etag);

  if($etag === $etagHeader) {
    header("HTTP/1.1 304 Not Modified");
  } else {

    $im = imagecreatefromstring($pic);

    if ($im === false) $im = imagecreatefromstring(noCoverPic());

    $stamp = imagecreatetruecolor(110, 30);
    imagefilledrectangle($stamp, 0, 0, 109, 29, 0xFF0000);
    imagefilledrectangle($stamp, 2, 2, 107, 27, 0xFFFFFF);
    if(isset($_GET['top250'])) {
      imagestring($stamp, 5, 30, 7, 'top250', 0x66339F);
    } else {
      imagestring($stamp, 3, 10, 3, 'Rentner- bzw.', 0x66339F);
      imagestring($stamp, 3, 10, 13, ' Schrottfilm', 0x66339F);
    }

    $marge_right = 2;
    $marge_bottom = 2;
    $sx = imagesx($stamp);
    $sy = imagesy($stamp);

    imagecopymerge($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp), 75);

    header("Content-Type: image/png");

    ob_start();
    imagepng($im, NULL, 9,  PNG_ALL_FILTERS);
    header("Content-Length: ".ob_get_length());
    ob_end_flush();

    imagedestroy($im);
  }

} else if(isset($_GET['mid']) && isset($_GET['oid']) && isset($_SESSION['ui'])) {
  if($_SESSION['ui']['admin']) MySQLBase::instance()->update_omdb_id($_GET['mid'], $_GET['oid']);
  if(isset($_GET['url'])) header("Location: ".urldecode($_GET['url']));
} else if(isset($_GET['search']) && isset($_SESSION['ui'])) {
  header("Location: http://www.omdb.org/search/movies/?search[text]=".$_GET['search']);
} else if(isset($_GET['id']) && isset($_SESSION['ui'])) {
  header("Location: https://www.omdb.org/movie/".$_GET['id']);
} else if(isset($_GET['q'])) {
  header("Location: ".MySQLBase::instance()->protocol()."://".$_SERVER['SERVER_NAME'].MySQLBase::getRequestURI()."/?".urldecode($_GET['q']));
} else {
  header("Location: ".MySQLBase::instance()->protocol()."://".$_SERVER['SERVER_NAME'].MySQLBase::getRequestURI()."/");
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
