<?php

require 'classes/mysql_base.php';

header("Content-Type: application/rss+xml");
// header("Content-Type: text/plain");

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

$head = $xml->createElement('link', "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI'])."/");

$atomlink = $xml->createElementNS('http://www.w3.org/2005/Atom', 'atom:link');
$atomlink->setAttribute('href', "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI'])."/feed.php");
$atomlink->setAttribute('rel',  'self');
$atomlink->setAttribute('type', 'application/rss+xml');
$channel->appendChild($atomlink);

$channel->appendChild($head);

$head = $xml->createElement('lastBuildDate', gmdate("D, j M Y H:i:s ", time()).'GMT');
$channel->appendChild($head);

$result = MySQLBase::instance()->con()->query("SELECT `d`.`id` AS `did`, `d`.`name` AS `name`, UNIX_TIMESTAMP(`d`.`created`) AS `created`, `m`.`ID` AS `ID`, ".
  "MAKE_MOVIE_TITLE(`m`.`title`, `m`.`comment`, `s`.`name`, `es`.`episode`, `s`.`prepend`) AS `title`, `c`.`name` as `cat` FROM `movies` AS `m` ".
  "LEFT JOIN `episode_series` AS `es` ON `m`.`ID` = `es`.`movie_id` LEFT JOIN `series` AS `s` ON `s`.`id` = `es`.`series_id` ".
  "LEFT JOIN `disc` AS `d` ON `m`.`disc` = `d`.`id` LEFT JOIN `categories` AS `c` ON `c`.`id` = `m`.`category` WHERE `d`.`created` IS NOT NULL ".
  "ORDER BY `d`.`created` DESC , MAKE_MOVIE_SORTKEY(`title`, `m`.`skey`) ASC");

while($rssdata = $result->fetch_assoc()) {	

    $item = $xml->createElement('item');
    $channel->appendChild($item);

    $data = $xml->createElement('title', $rssdata['title']);
    $item->appendChild($data);

    $data = $xml->createElement('description', $rssdata['name']);
    $item->appendChild($data);
    
    $data = $xml->createElement('category', $rssdata['cat']);
    $item->appendChild($data);

    $data = $xml->createElement('link', "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI'])."/?filter_disc=".$rssdata['did']);
    $item->appendChild($data);

    $data = $xml->createElement('pubDate', gmdate("D, j M Y H:i:s ", $rssdata['created']).'GMT');
    $item->appendChild($data);

    $data = $xml->createElement('guid', "http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['REQUEST_URI'])."/?filter_ID=".$rssdata['ID']);
    $data->setAttribute("isPermaLink", "true");
    $item->appendChild($data);
}

$result->free_result();

echo $xml->saveXML();

?>
