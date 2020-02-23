<?php
/*
 * Copyright 2017-2020 by Heiko Schäfer <heiko@rangun.de>
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
require 'classes/omdb_base.php';
require 'classes/tracker.php';
require 'classes/tmdb.php';

function noCoverPic() {
    $filename = "img/nocover.png";
    $handle = fopen($filename, "rb");
    $p = fread($handle, filesize($filename));
    fclose($handle);

    return $p;
}

function loadCover($url) {

  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_USERAGENT, "db-webvirus/1.0");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  //curl_setopt($ch, CURLOPT_HTTPHEADER,     array("If-None-Match: ".trim($_SERVER["HTTP_IF_NONE_MATCH"]));
  curl_setopt($ch, CURLOPT_HTTPHEADER,     array("Accept-Language", "de,en-US;q=0.7,en;q=0.3"));

  if(isset($proxy)) {
	curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
	curl_setopt($ch, CURLOPT_PROXY, $proxy);
  }

  curl_setopt($ch, CURLOPT_HEADERFUNCTION,
  function($curl, $header) use (&$headers) {

	$len = strlen($header);
	$header = explode(':', $header, 2);

	if(count($header) < 2) return $len;

	$name = strtolower(trim($header[0]));

	if(!is_null($headers)) {
	  if(!array_key_exists($name, $headers)) {
		$headers[$name] = [trim($header[1])];
	  } else {
		$headers[$name][] = trim($header[1]);
	  }
	}

	return $len;
  }
  );

  $pic = curl_exec($ch);
  $rsc = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

  if($rsc != 200) $pic == null;

  if(curl_errno($ch) || $rsc != 200) error_log("Curl error (loading omdb movie picture): ".curl_error($ch));

  curl_close($ch);

  return $pic;
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

  $doc = !empty($_GET['cover-oid']) ? fetchOMDBPage($_GET['cover-oid']) : null;

  try {
	$tmdb = new TMDb(!empty($_GET['cover-oid']) ? MySQLBase::instance()->title_from_omdb_id($_GET['cover-oid']) : urldecode($_GET['fallback']),
	  isset($_GET['tmdb_type']) ? $_GET['tmdb_type'] : "movie", isset($_GET['tmdb_id']) && !empty($_GET['tmdb_id']) ? $_GET['tmdb_id'] : null);
  } catch(RuntimeException $exc) {
	$tmdb = null;
  }

  if(!is_null($doc) && !isset($_GET['abstract']) && !empty($doc->getElementById("left_image"))) {
    $pic = loadCover($doc->getElementById("left_image")->getAttribute("src"));
  } else {
	try {
	  if(is_null($tmdb)) throw new RuntimeException();
	  $pic = loadCover($tmdb->cover_url());
	} catch(RuntimeException $exc) {
	  $pic = null;
	}
  }

  if(isset($_GET['abstract']) && !is_null($doc) && !empty($doc->getElementById("abstract"))) {

    $abs_enc = extractAbstract($doc);

    if(!is_null($abs_enc)) {
      header("Content-type: text/plain; charset=".$abs_enc['encoding']);
      echo $abs_enc['abstract'];
    } else {
      http_response_code(503);
    }

    exit;

  } else if(isset($_GET['abstract'])) {
	  header("Content-type: text/plain; charset=UTF-8");
	  try {
		if(is_null($tmdb)) throw new RuntimeException("DB");
		echo $tmdb->abstract();
	  } catch(RuntimeException $exc) {
		if("DB" != $exc->getMessage()) {
		  echo "Konnte Kurzbeschreibung nicht laden.\n".(!empty($_GET['cover-oid']) ? "Womöglich ist omdb zur Zeit nicht erreichbar." :
			  "Aufgrund fehlender Intelligenz kann weder Webvirus noch TMDb eine Kurzbeschreibung für Dich erfinden.");
		}
	  }
	  exit;
  }

  if(is_null($pic) || md5($pic) == "106f2c74718ffe31354f44a40cc2f4a8") {
    $pic = noCoverPic();
    $headers['content-type'][0] = "image/png";
  }

  $etagHeader = (isset($_SERVER["HTTP_IF_NONE_MATCH"]) ? trim($_SERVER["HTTP_IF_NONE_MATCH"]) : false);
  $etag       = null;

  if(isset($headers['etag'])) {
	$etag = sprintf('"%s"', substr($headers['etag'][0], 1 , -1));
	header("ETag: ".$etag);
  }

  if(!is_null($etag) && $etag === $etagHeader) {
    header("HTTP/1.1 304 Not Modified");
  } else {

    $im = imagecreatefromstring($pic);

    if($im === false) $im = imagecreatefromstring(noCoverPic());

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
  $tmdb = new TMDb($_GET['search'], 'movie', null);
  header("Location: https://www.themoviedb.org/".$tmdb->media_type()."/".$tmdb->id());
} else if(isset($_GET['id']) && isset($_SESSION['ui'])) {
  header("Location: https://www.omdb.org/movie/".$_GET['id']);
} else if(isset($_GET['wvid']) && isset($_SESSION['ui'])) {
  header("Location: ".MySQLBase::instance()->tmdb_url_from_id($_GET['wvid']));
} else if(isset($_GET['q'])) {
  header("Location: ".MySQLBase::instance()->protocol()."://".$_SERVER['SERVER_NAME'].MySQLBase::getRequestURI()."/?".urldecode($_GET['q']));
} else {
  header("Location: ".MySQLBase::instance()->protocol()."://".$_SERVER['SERVER_NAME'].MySQLBase::getRequestURI()."/");
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
