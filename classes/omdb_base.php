<?php
/*
 * Copyright 2019-2020 by Heiko Schäfer <heiko@rangun.de>
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

function fetchOMDBPage($omdb_id) {

  $ch = curl_init("https://www.omdb.org/movie/".$omdb_id);

  curl_setopt($ch, CURLOPT_USERAGENT, "db-webvirus/1.0");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER,     array("Accept-Language: de-DE,en;q=0.5"));

  /*if(!is_null($proxy)) {
    curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
  }*/

  $libxml_previous_state = libxml_use_internal_errors(true);
  $doc = DOMDocument::loadHTML(curl_exec($ch));
  $rsc = curl_getinfo($ch,  CURLINFO_RESPONSE_CODE);

  if(curl_errno($ch) || $rsc != 200) error_log("Curl error (loading omdb movie page): ".curl_error($ch));

  libxml_clear_errors();
  libxml_use_internal_errors($libxml_previous_state);
  curl_close($ch);

  return $rsc != 200 ? null : $doc;
}

function extractAbstract($doc) {

  if(!is_null($doc) && !empty($doc->getElementById("abstract"))) {
    $abstract = utf8_decode($doc->getElementById("abstract")->nodeValue);
    $encoding = strtolower(mb_detect_encoding($abstract,"UTF-8, ISO-8859-15, ISO-8859-1", true));

    return array("abstract" => $abstract, "encoding" => $encoding);
  }

  return null;
}

?>
