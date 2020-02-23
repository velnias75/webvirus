<?php
/*
 * Copyright 2020 by Heiko Schäfer <heiko@rangun.de>
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

require_once 'levenshteintraits.php';

class TMDbResponse {

  private $code;
  private $mobj;

  function __construct($json, $code) {
	$this->code = $code;
	$this->mobj = json_decode($json);
  }

  function abstract() {
	return $this->mobj->{'overview'};
  }

  function poster_base() {
	$size = $this->mobj->{'images'}->{'poster_sizes'};
	return $this->mobj->{'images'}->{'secure_base_url'}.$size[count($size)-5];
  }

  function poster_url($base) {
	return $base.$this->mobj->{'poster_path'};
  }

  function search_results() {
	return $this->mobj->{'results'};
  }

  function alternatives() {
	return $this->mobj->{'titles'};
  }

  function cast($max = 5) {

	$rs = "";
	$i = 0;

	foreach($this->mobj->{'cast'} as $g) {
	  if($i >= $max) break;
	  $rs .= "◦ ".$g->{'name'}."\n";
	  $i++;
	}

	return substr($rs, 0, -1);
  }

  function director() {

	$rs = "";

	foreach($this->mobj->{'crew'} as $g) {
	  if($g->{'job'} == "Director") {
		$rs .= $g->{'name'}.", ";
	  }
	}

	return substr($rs, 0, -2);
  }

  function genres() {

	$rs = "";

	foreach($this->mobj->{'genres'} as $g) {
	  $rs .= $g->{'name'}."/";
	}

	return substr($rs, 0, -1);
  }

  function countries() {

	$rs = "";

	if(isset($this->mobj->{'origin_country'})) {
	  foreach($this->mobj->{'origin_country'} as $g) {
		$rs .= $g.", ";
	  }
	}

	if(isset($this->mobj->{'production_countries'})) {
	  foreach($this->mobj->{'production_countries'} as $g) {
		$rs .= $g->{'name'}.", ";
	  }
	}

	return !empty($rs) ? substr($rs, 0, -2) : "unbekannt";
  }

  function origtitle() {
	return isset($this->mobj->{'original_title'}) ? $this->mobj->{'original_title'} : $this->mobj->{'original_name'};
  }
}

class TMDb {

  use LevenshteinTraits;

  private $api_key;
  private $mresp;
  private $cresp;
  private $conf;
  private $pbase;
  private $mtype;
  private $mid;

  function __construct($q, $type, $tid) {

	require dirname(dirname(__FILE__)).'/db_cred.php';

	$id = null;

	if(isset($tmdb_k)) {

	  $this->api_key = $tmdb_k;
	  $this->conf    = $this->req("https://api.themoviedb.org/3/configuration");

	  if(is_null($tid)) {

		if(!is_numeric($q)) {

		  $id = $this->search($q, $v);

		  if(is_null($id)) {

			if(mb_strrchr($q, ")", true, 'UTF-8') !== false) {
			  $pos = mb_strrchr($q, "(", true, 'UTF-8');
			  if($pos !== false) $id = $this->search($pos, $v);
			}

			if(is_null($id)) {
			  $pos = mb_strrchr($q, ":", true, 'UTF-8');
			  if($pos !== false) $id = $this->search($pos, $v);
			}
		  }

		} else {
		  $id = $q;
		}

	  } else {
		$v = $type;
		$id = $tid;
	  }

	  $this->mtype = $v;
	  $this->mid  = $id;

	  if(!is_null($id)) {
		$this->mresp = $this->req("https://api.themoviedb.org/3/".($v == "name" ? "tv" : "movie")."/".$id);
		$this->cresp = $this->req("https://api.themoviedb.org/3/".($v == "name" ? "tv" : "movie")."/".$id."/credits");
		$this->pbase = $this->conf->poster_base();
	  } else {
		throw new RuntimeException("no movie found for \"".$q."\"");
	  }

	} else {
	  throw new RuntimeException("no TMDb-API-Key available");
	}
  }

  private function inspect($rsp, $q, &$v) {

	$arr = $rsp->search_results();

	usort($arr, function($a, $b) {
	  return strcmp($a->{'media_type'}, $b->{'media_type'});
	});

	foreach($arr as $r) {

	  $v  = $r->{'media_type'} == "tv" ? "name" : "title";
	  $t  = mb_strtolower($r->{$v}, 'UTF-8');

	  if($t == $q && !empty($r->{'overview'})) {
		return $r->{'id'};
	  }
	}

	foreach($arr as $r) {

	  $v  = $r->{'media_type'} == "tv" ? "name" : "title";
	  $ot = mb_strtolower($r->{'original_'.$v}, 'UTF-8');

	  if($ot == $q && !empty($r->{'overview'})) {
		return $r->{'id'};
	  }
	}

	foreach($arr as $r) {

	  $v  = $r->{'media_type'} == "tv" ? "name" : "title";
	  $t  = mb_strtolower($r->{$v}, 'UTF-8');

	  if($t == $q) {
		return $r->{'id'};
	  }
	}

	foreach($arr as $r) {

	  $v  = $r->{'media_type'} == "tv" ? "name" : "title";
	  $ot = mb_strtolower($r->{'original_'.$v}, 'UTF-8');

	  if($ot == $q) {
		return $r->{'id'};
	  }
	}

	foreach($arr as $r) {

	  $v  = $r->{'media_type'} == "tv" ? "name" : "title";
	  $t  = mb_strtolower($r->{$v}, 'UTF-8');
	  $ot = mb_strtolower($r->{'original_'.$v}, 'UTF-8');

	  if(mb_strpos($t, $q, 0, 'UTF-8') !== false || mb_strpos($ot, $q, 0, 'UTF-8') !== false ||
		 mb_strpos($q, $t, 0, 'UTF-8') !== false || mb_strpos($q, $ot, 0, 'UTF-8') !== false ||
		 $this->damerauLevenshteinDistance($this->toUnicodeCharArray($t),  $this->toUnicodeCharArray($q)) < 4 ||
		 $this->damerauLevenshteinDistance($this->toUnicodeCharArray($ot), $this->toUnicodeCharArray($q)) < 4) {
		  return $r->{'id'};
		}
	}

	foreach($arr as $r) {

	  $v  = $r->{'media_type'} == "tv" ? "name" : "title";
	  $alt = $this->req("https://api.themoviedb.org/3/".($r->{'media_type'} == "tv" ?
	                          "tv" : "movie")."/".$r->{'id'}."/alternative_titles");

	  foreach($alt->alternatives() as $at) {

		$t = mb_strtolower($at->{$v}, 'UTF-8');

		if($t == $q) {
		  return $r->{'id'};
		}
	  }

	  foreach($alt->alternatives() as $at) {

		$t = mb_strtolower($at->{$v}, 'UTF-8');

		if(mb_strpos($t, $q, 0, 'UTF-8') !== false || mb_strpos($q, $t, 0, 'UTF-8') !== false ||
		  $this->damerauLevenshteinDistance($this->toUnicodeCharArray($t), $this->toUnicodeCharArray($q)) < 4) {
			return $r->{'id'};
		  }
	  }
	}

	return null;
  }

  private function search($q, &$v) {

	$q   = mb_strtolower($this->titleNormalizer(preg_replace('/\x{2013}/u',"-", $q)), 'UTF-8');
	$rsp = $this->req("https://api.themoviedb.org/3/search/multi", "&query=".urlencode($q)."&include_adult=true");

    return $this->inspect($rsp, $q, $v);
  }

  private function req($url, $extra = null) {

	if($ch = curl_init($url."?api_key=".$this->api_key."&language=de-DE".(!is_null($extra) ? $extra : ""))) {

	  curl_setopt($ch, CURLOPT_USERAGENT, "db-webvirus/1.0");
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	  curl_setopt($ch, CURLOPT_HTTPHEADER,     array("Accept-Language: de-DE,en;q=0.5"));

	  $resp = new TMDBResponse(curl_exec($ch), curl_getinfo($ch, CURLINFO_RESPONSE_CODE));

	  curl_close($ch);

	  return $resp;

	} else {
	  throw new RuntimeException("error in curl_init()");
	}
  }

  function id() {
	return $this->mid;
  }

  function media_type() {
	return $this->mtype == "title" ? "movie" : "tv";
  }

  function cover_url() {
	return $this->mresp->poster_url($this->conf->poster_base());
  }

  function abstract() {
	return trim($this->mresp->abstract()."\n\n• ".$this->mresp->genres()." (".$this->mresp->countries().")\n".
				$this->cresp->cast()."\n🎥 Originaltitel: ".$this->mresp->origtitle()."\n".
				(!empty($this->cresp->director()) ? "🎥 Regie: ".$this->cresp->director()."\n" : ""));
  }
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
