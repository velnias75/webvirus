<?php
/*
 * Copyright 2017 by Heiko Schäfer <heiko@rangun.de>
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

  require 'classes/movies.php';

  session_start();

  try {
    $movies = new Movies(isset($_GET['order_by']) ? $_GET['order_by'] : "ltitle",
      isset($_GET['from']) ? $_GET['from'] : 0,
      isset($_GET['to']) ? $_GET['to'] : Movies::pageSize(), isset($_GET['cat']) ? $_GET['cat'] : -1);
  } catch(Exception $e) {
    echo "<strong>Fehler:</strong> ".htmlentities($e->getMessage(), ENT_SUBSTITUTE, "utf-8");
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="cache-control" content="max-age=0">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="expires" content="0">
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT">
<meta http-equiv="pragma" content="no-cache">
<meta name="description" content="Liste der auf DVD gespeicherten Filme, Dokumentationen und Konzerten">
<meta name="keywords" content="MP4, MKV, Filmliste, Spielfilme, Dokumentationen, Dokus, Konzerte">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/font-hack/2.020/css/hack-extended.min.css">
<link rel="stylesheet" href="css/master.php?t=<?php echo time(); ?>" title="Hirnloser Stil" type="text/css" media="screen">
<link href="css/print.css?t=<?php echo time(); ?>" rel="alternate stylesheet" title="Druckversion" type="text/css" media="screen">
<link rel="stylesheet" href="css/print.css?t=<?php echo time(); ?>" type="text/css" media="print">
<script src="https://cdn.jsdelivr.net/g/jquery,typeahead.js" type="text/javascript"></script>
<script type="text/javascript">
var titles = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.whitespace,
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
      url: '<?= dirname($_SERVER['REQUEST_URI'])."/" ?>title-json.php<?= $movies->filterJSONQueryString(); ?>&filter_ltitle=%QUERY',
      wildcard: '%QUERY',
      rateLimitBy: 'throttle'
    }
});

$(document).ready(function() {
  $('#list_filter_ltitle').typeahead({
    hint: true,
    highlight: true,
    minLength: 3
  }, {
    source: titles,
    templates: {
      empty: 'Liebe(r) Nutzer(in), <span class="red_text"><strong>dieser Suchbegriff wird Sie zu keinem Film f&uuml;hren!</strong></span>',
      pending: 'Schnarch&#8230;'
    },
    limit: 1e06
  });
})

$(document).ready(function() {
  $('#list_filter_ltitle').focus();
})
</script>
<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
<link rel="alternate" title="Heikos Schrott- &amp; Rentnerfilme" type="application/rss+xml" href="feed.php">
<link href='hsrsearch.xml' rel='search' title='Suche in Heikos Schrott- &amp; Rentnerfilmen' type='application/opensearchdescription+xml'>
<title>Heikos Schrott- &amp; Rentnerfilme</title>
</head>
<body>