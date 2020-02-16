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

  private $_root = null;

  function __construct() {

	$movies = (new Movies())->mySQLRowsArray();
	$mcount = count($movies);

	for($i = 0; $i < $mcount; $i++) {
	  $this->add($movies[$i]);
	}
  }

  private function add($item) {

	if($this->_root == null) {
	  $this->_root = new _node($item);
	  return;
	}

	$curNode = $this->_root;
	$it = mb_strtolower(titleNormalizer($item['title']), 'UTF-8');;

	$dist = $this->damerauLevenshteinDistance(mb_strtolower($curNode, 'UTF-8'), $it);

	while($curNode->containsKey($dist)) {

	  if($dist == 0) return;

	  $curNode = $curNode->get($dist);

	  $dist = $this->damerauLevenshteinDistance(mb_strtolower($curNode, 'UTF-8'), $it);
	}

	$curNode->addChild($dist, $item);
  }

  private function damerauLevenshteinDistance($source, $target) {

	$sourceLength = strlen($source);
	$targetLength = strlen($target);

	if($sourceLength == 0) return $targetLength;
	if($targetLength == 0) return $sourceLength;

	$dist = array(array());

	for($i = 0; $i <= $sourceLength; $i++) $dist[$i][0] = $i;
	for($j = 0; $j <= $targetLength; $j++) $dist[0][$j] = $j;

	for($i = 1; $i <= $sourceLength; $i++) {

	  $sca = ((string)$source)[$i - 1];

	  for($j = 1; $j <= $targetLength; $j++) {

		$tca = $target[$j - 1];
		$cost = $sca == $tca ? 0 : 1;

		$dist[$i][$j] = min(min($dist[$i - 1][$j] + 1, $dist[$i][$j - 1] + 1), $dist[$i - 1][$j - 1] + $cost);

		if($j > 1 && $i > 1 && $sca == $target[$j - 2] && ((string)$source)[$i - 2] == $tca) {
		  $dist[$i][$j] = min($dist[$i][$j], $dist[$i - 2][$j - 2] + $cost);
		}
	  }
	}

	return $dist[$sourceLength][$targetLength];
  }

  function render() {
	echo json_encode($this->_root, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|
	                               JSON_PARTIAL_OUTPUT_ON_ERROR|JSON_INVALID_UTF8_IGNORE|
	                               JSON_INVALID_UTF8_SUBSTITUTE);
  }

}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
