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

function getLink() {
  return MySQLBase::instance()->protocol()."://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI']);
}

header("Content-Type: application/rss+xml");

$xml = new DOMDocument('1.0', 'utf-8');
$xml->formatOutput = true;

$rss = $xml->createElement('rss');
$rss->setAttribute('version', '2.0');
$rss->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:atom', 'http://www.w3.org/2005/Atom');
$xml->appendChild($rss);

$channel = $xml->createElement('channel');
$rss->appendChild($channel);

$head = $xml->createElement('title', 'Heikos Schrott- und Rentnerfilme');
$channel->appendChild($head);

$head = $xml->createElement('description', 'Die neuesten Schrott- und Rentnerfilme');
$channel->appendChild($head);

$head = $xml->createElement('language', 'de');
$channel->appendChild($head);

$head = $xml->createElement('copyright', "Copyright ".strftime("%Y").", Heiko Schäfer");
$channel->appendChild($head);

$head = $xml->createElement('webMaster', "heiko@rangun.de (Heiko Schäfer)");
$channel->appendChild($head);

$head = $xml->createElement('generator', "Die Webvirenversion");
$channel->appendChild($head);

$imago = $xml->createElement('image');

$imago_t = $xml->createElement('title', 'Heikos Schrott- und Rentnerfilme');
$imago->appendChild($imago_t);

$imago_l = $xml->createElement('link', getLink()."/");
$imago->appendChild($imago_l);

$imago_u = $xml->createElement('url', getLink()."/img/feed.png");
$imago->appendChild($imago_u);

$imago_w = $xml->createElement('width', '48');
$imago->appendChild($imago_w);

$imago_h = $xml->createElement('height', '48');
$imago->appendChild($imago_h);

$channel->appendChild($imago);

$head = $xml->createElement('link', getLink()."/");

$atomlink = $xml->createElementNS('http://www.w3.org/2005/Atom', 'atom:link');
$atomlink->setAttribute('href', getLink()."/feed.php");
$atomlink->setAttribute('rel',  'self');
$atomlink->setAttribute('type', 'application/rss+xml');
$channel->appendChild($atomlink);

$channel->appendChild($head);

$head = $xml->createElement('lastBuildDate', gmdate("D, j M Y H:i:s ", time()).'GMT');
$channel->appendChild($head);

$result = MySQLBase::instance()->con()->query("SELECT `d`.`id` AS `did`, `d`.`name` AS `name`, UNIX_TIMESTAMP(`d`.`created`) AS `created`, `m`.`ID` AS `ID`, ".
"MAKE_MOVIE_TITLE(`m`.`title`, `m`.`comment`, `s`.`name`, `es`.`episode`, `s`.`prepend`, `m`.`omu`) AS `title`, `c`.`name` as `cat`, `m`.`omdb_id`AS `oid`, ".
  "SEC_TO_TIME(m.duration) AS `duration`, IF(`languages`.`name` IS NOT NULL, TRIM(GROUP_CONCAT(`languages`.`name` ".
  "ORDER BY `movie_languages`.`lang_id` DESC SEPARATOR ', ')), 'n. V.') as `lingos` FROM `movies` AS `m` ".
  "LEFT JOIN `episode_series` AS `es` ON `m`.`ID` = `es`.`movie_id` LEFT JOIN `series` AS `s` ON `s`.`id` = `es`.`series_id` ".
  "LEFT JOIN `disc` AS `d` ON `m`.`disc` = `d`.`id` LEFT JOIN `categories` AS `c` ON `c`.`id` = `m`.`category` ".
  "LEFT JOIN `movie_languages` ON `m`.`ID` = `movie_languages`.`movie_id` LEFT JOIN `languages` ON `movie_languages`.`lang_id` = `languages`.`id` ".
  "WHERE `d`.`created` IS NOT NULL GROUP BY `m`.`ID` ORDER BY `d`.`created` DESC , MAKE_MOVIE_SORTKEY(`title`, `m`.`skey`) ASC".
  (empty($_GET['n']) ? " LIMIT 20" : ($_GET['n'] == -1 ? "" : " LIMIT ".$_GET['n'])));

$lm = false;

while($rssdata = $result->fetch_assoc()) {

    if(!$lm) {
      $lm = true;
      header("Last-Modified: ".date(DATE_RFC2822, $rssdata['created']));
    }

    $item = $xml->createElement('item');
    $channel->appendChild($item);

    $data = $xml->createElement('title', str_replace("&", "&amp;", $rssdata['title']));
    $item->appendChild($data);

    $cdata = $xml->createCDATASection("<table border=\"0\"><tr>".
      (!empty($rssdata['oid']) ? "<td width=\"*\">".
	"<img src=\"".getLink()."/omdb.php?cover-oid=".$rssdata['oid']."\" alt=\"Cover f&uuml;r &quot;".$rssdata['title']."&quot;\">".
	"</td>" : "").
      "<td valign=\"top\"".(!empty($rssdata['oid']) ? "" : " colspan=\"2\"")."><dl>".
      "<dt><b>Nr</b></dt><dd>".$rssdata['ID']."</dd>".
      "<dt><b>Titel</b></dt><dd>".str_replace("&", "&amp;", $rssdata['title'])."</dd>".
      "<dt><b>L&auml;nge</b></dt><dd>".$rssdata['duration']."</dd>".
      "<dt><b>Sprachen(n)</b></dt><dd>".$rssdata['lingos']."</dd>".
      "<dt><b>DVD</b></dt><dd>".$rssdata['name']."</dd>".
      "</dl></td></tr>");
    $data = $xml->createElement('description');
    $data->appendChild($cdata);
    $item->appendChild($data);

    $data = $xml->createElement('category', $rssdata['cat']);
    $item->appendChild($data);

    $data = $xml->createElement('author', $rssdata['name']);
    $item->appendChild($data);

    $data = $xml->createElement('link', getLink()."/disc/".$rssdata['did']);
    $item->appendChild($data);

    $data = $xml->createElement('pubDate', gmdate("D, j M Y H:i:s ", $rssdata['created']).'GMT');
    $item->appendChild($data);

    $data = $xml->createElement('guid', getLink()."/?filter_ID=".$rssdata['ID']);
    $data->setAttribute("isPermaLink", "true");
    $item->appendChild($data);
}

$result->free_result();

echo $xml->saveXML();

?>
