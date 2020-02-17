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

function titleNormalizer($title) {
  return trim(preg_replace('~[^\x00-\xFF]~u', "", $title));
}

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

  private $item;
  private $children = null;

  function __construct($item) {
	$this->item = $item;
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
	return titleNormalizer($this->item['title']);
  }
}

final class BKTree {

  const CACHE_FILE = "/cache/schrottfilme.json.gz";

  private $_root = null;
  private $size = 0;

  function __construct() {

	if(!file_exists(dirname(dirname(__FILE__)).BKTree::CACHE_FILE)) {

	  $movies = (new Movies())->mySQLRowsArray();
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
	$it = $this->toUnicodeCharArray(mb_strtolower(titleNormalizer($item['title']), 'UTF-8'));

	$dist = $this->damerauLevenshteinDistance($this->toUnicodeCharArray(mb_strtolower($curNode, 'UTF-8')), $it);

	while($curNode->containsKey($dist)) {

	  if($dist == 0) return;

	  $curNode = $curNode->get($dist);

	  $dist = $this->damerauLevenshteinDistance($this->toUnicodeCharArray(mb_strtolower($curNode, 'UTF-8')), $it);
	}

	$curNode->addChild($dist, $item);

	$this->size++;
  }

  private function damerauLevenshteinDistance($source, $target) {

	$sourceLength = count($source);
	$targetLength = count($target);

	if($sourceLength == 0) return $targetLength;
	if($targetLength == 0) return $sourceLength;

	$dist = array(array());

	for($i = 0; $i <= $sourceLength; $i++) $dist[$i][0] = $i;
	for($j = 0; $j <= $targetLength; $j++) $dist[0][$j] = $j;

	for($i = 1; $i <= $sourceLength; $i++) {

	  $sca = $source[$i - 1];

	  for($j = 1; $j <= $targetLength; $j++) {

		$tca = $target[$j - 1];
		$cost = $sca == $tca ? 0 : 1;

		$dist[$i][$j] = min(min($dist[$i - 1][$j] + 1, $dist[$i][$j - 1] + 1), $dist[$i - 1][$j - 1] + $cost);

		if($j > 1 && $i > 1 && $sca == $target[$j - 2] && $source[$i - 2] == $tca) {
		  $dist[$i][$j] = min($dist[$i][$j], $dist[$i - 2][$j - 2] + $cost);
		}
	  }
	}

	return $dist[$sourceLength][$targetLength];
  }

  private function toUnicodeCharArray($str) {
	return preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
  }

  function render() {

	if(!file_exists(dirname(dirname(__FILE__)).BKTree::CACHE_FILE)) {

	  $json = json_encode(new Nodes($this->size, $this->_root), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|
																JSON_PARTIAL_OUTPUT_ON_ERROR|JSON_INVALID_UTF8_IGNORE|
																JSON_INVALID_UTF8_SUBSTITUTE);

	  if($f = gzopen(dirname(dirname(__FILE__)).BKTree::CACHE_FILE, "wb9")) {
		gzwrite($f, $json);
		gzclose($f);
	  }

	  echo $json;

	} else if($f = gzopen(dirname(dirname(__FILE__)).BKTree::CACHE_FILE, "r")) {

		while(!gzeof($f)) {
		  echo gzread($f, 1024);
		}

		gzclose($f);
	}
  }
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
