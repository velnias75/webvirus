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

require 'movies.php';
require_once 'levenshteintraits.php';

final class Nodes implements JsonSerializable {

  private $size;
  private $root;

  function __construct($size, $root) {
	$this->size = $size;
	$this->root = $root;
  }

  public function jsonSerialize() {
	return (object)[ 'size' => $this->size, 'root' => $this->root ];
  }
}

final class _node implements JsonSerializable {

  use LevenshteinTraits;

  private $item;
  private $children = null;

  function __construct($item) {
	$this->item = $item;

	/*if(is_null($this->item['oid'])) {
	  $this->item['oid'] = 0;
	}*/
  }

  function get($key) {
	return $this->children[$key];
  }

  function addChild($key, $item) {
	$this->children[$key] = new _node($item);
  }

  function containsKey($key) {
	return !is_null($this->children) && array_key_exists($key, $this->children);
  }

  public function jsonSerialize() {
	return [ 'item' => $this->item, 'children' => $this->children ];
  }

  function __toString() {
	return $this->titleNormalizer($this->item['title']);
  }
}

final class BKTree {

  use LevenshteinTraits;

  const CACHE_FILE_PRE = "/cache/schrottfilme";
  const CACHE_FILE_SUF = ".json.bz";

  private $_root = null;
  private $size = 0;

  function __construct() {

	if(!file_exists(dirname(dirname(__FILE__)).BKTree::CACHE_FILE_PRE.$this->queryString().BKTree::CACHE_FILE_SUF)) {

	  $movies = (new Movies(isset($_GET['order_by']) ? $_GET['order_by'] : "ltitle", 0, -1,
		isset($_GET['cat']) ? $_GET['cat'] : -1))->mySQLRowsArray();
	  $mcount = count($movies);

	  for($i = 0; $i < $mcount; $i++) {
		$this->add($movies[$i]);
	  }
	}
  }

  private function add($item) {

	if(is_null($this->_root)) {
	  $this->_root = new _node($item);
	  $this->size++;
	  return;
	}

	$curNode = $this->_root;
	$it = $this->toUnicodeCharArray(mb_strtolower($this->titleNormalizer($item['title']), 'UTF-8'));

	$dist = $this->damerauLevenshteinDistance($this->toUnicodeCharArray(mb_strtolower($curNode, 'UTF-8')), $it);

	while($curNode->containsKey($dist)) {

	  if($dist == 0) return;

	  $curNode = $curNode->get($dist);

	  $dist = $this->damerauLevenshteinDistance($this->toUnicodeCharArray(mb_strtolower($curNode, 'UTF-8')), $it);
	}

	$curNode->addChild($dist, $item);

	$this->size++;
  }

  public static function queryString() {
	return array_key_exists('QUERY_STRING', $_SERVER) ?
	  "-".strtolower(str_replace('/', '_', $_SERVER['QUERY_STRING'])) : "";
  }

  function render() {

	$cf = dirname(dirname(__FILE__)).BKTree::CACHE_FILE_PRE.$this->queryString().BKTree::CACHE_FILE_SUF;

	if(!file_exists($cf)) {

	  $json = json_encode(new Nodes($this->size, $this->_root), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|
																JSON_PARTIAL_OUTPUT_ON_ERROR|JSON_INVALID_UTF8_IGNORE|
																JSON_INVALID_UTF8_SUBSTITUTE);
	  if($f = bzopen($cf, "w")) {
		bzwrite($f, $json);
		bzclose($f);
	  }

	  echo $json;

	} else if($f = bzopen($cf, "r")) {

		while(!feof($f)) {
		  echo bzread($f, 8192);
		}

		bzclose($f);
	}
  }
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
