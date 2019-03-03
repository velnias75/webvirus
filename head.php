<?php
// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
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

require 'classes/movies.php';

session_start();

if(empty($_GET)) {
  if(isset($_COOKIE['query_mem'])) {
    header("Location: ".$_SERVER['PATH_INFO']."?".$_COOKIE['query_mem'], FALSE);
  }
} else {
  setcookie("query_mem", $_SERVER['QUERY_STRING'], time() + (5 * 365 * 24 * 60 * 60));
}

if(isset($_COOKIE['wvpltok'])) {
  MySQLBase::instance()->setLoggedInSession(MySQLBase::instance()->
  plogin(substr($_COOKIE['wvpltok'], 32, 8), substr($_COOKIE['wvpltok'], 0, 32)),
  isset($_SESSION['ui']) && $_SESSION['ui']['auto_login']);
}

$GLOBALS['dblastvisit'] = isset($_COOKIE["dblastvisit"]) ? $_COOKIE["dblastvisit"] : null;

setcookie("dblastvisit", time(), time()+60*60*24*365);

try {
  $movies = new Movies(isset($_GET['order_by']) ? $_GET['order_by'] : "ltitle",
  isset($_GET['from']) ? $_GET['from'] : 0,
  isset($_GET['to']) ? $_GET['to'] : Movies::pageSize(), isset($_GET['cat']) ? $_GET['cat'] : -1);
} catch(Exception $e) {
  echo "<strong>Fehler:</strong> ".htmlentities($e->getMessage(), ENT_SUBSTITUTE, "utf-8");
}

if(isset($_GET['filter_disc'])) {

  try {
    $og_image = MySQLBase::instance()->getOMDBId($_GET['filter_disc']);
  } catch(UnexpectedValueException $e) {
    $og_image = MySQLBase::instance()->getOMDBId();
  }

} else {
  $og_image = MySQLBase::instance()->getOMDBId();
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="cache-control" content="max-age=0">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="expires" content="0">
<meta http-equiv="expires" content="<?= gmdate('D, d M Y H:i:s T', time() - 86400); ?>">
<meta http-equiv="pragma" content="no-cache">
<meta name="description" content="Filmsammlung eines von der modernen Psychiatrie als v&ouml;llig schwachsinnig diagnostizierten PC-konsums&uuml;chtigen (Informatiker)">
<meta name="keywords" content="MP4, MKV, Filmliste, Spielfilme, Dokumentationen, Dokus, Konzerte, L&uuml;genmedien (&Ouml;R-TV)">
<meta name="twitter:card" content="summary">
<meta name="twitter:site" content="@Velnias75">
<meta name="og:title" content="Heikos Schrott- &amp; Rentnerfilme">
<meta property="og:description" content="Hirnlose Ansammlung an Schrott- &amp; Rentnerfilmen bar jeglichen Niveaus">
<meta property="og:image" content="https://rangun.de/db/omdb.php?cover-oid=<?= $og_image ?>">
<meta property="twitter:image:alt" content="RTL2 bietet hochwertigere Inhalte!">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/font-hack/2.020/css/hack-extended.min.css">
<link rel="stylesheet" href="css/master.php?t=<?= time(); ?>" title="Hirnloser Stil" type="text/css" media="screen">
<link href="css/print.css?t=<?= time(); ?>" rel="alternate stylesheet" title="Druckversion" type="text/css" media="screen">
<link rel="stylesheet" href="css/print.css?t=<?= time(); ?>" type="text/css" media="print">
<script src="https://cdn.jsdelivr.net/g/jquery,typeahead.js" type="text/javascript"></script>
<script type="text/javascript">
var titles = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.whitespace,
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  remote: {
      url: 'title-json.php<?= $movies->filterJSONQueryString(); ?>&filter_ltitle=%QUERY',
      wildcard: '%QUERY',
      rateLimitBy: 'throttle'
    }
});

function enableUserActions(id, enabled) {
  $('input[name=ample_' + id + ']').each(function(i) { $(this).prop('disabled', !enabled); });
  return false;
}

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

  $('#list_filter_ltitle').focus();

  $('.list.hasTooltip').mouseover(function(e) {

    var base     = $(this);
    var tooltipp = base.children("span");
    var image    = tooltipp.find("img");

    $('.hasTooltip span').removeAttr('style');

    if(image.length) {

      var tooltipLeft = (base.offset().left + base.width()) - tooltipp.width() - 20;
      tooltipp.css({ left: tooltipLeft });

      image.attr("src", image.attr("data-src")).on('load', function(e) {

	var tooltip = base.children("span");
	var tooltipTop = tooltip.offset().top;
	var tooltipLeft = (base.offset().left + base.width()) - tooltip.width() - 20;
	var tooltipBottom = tooltipTop + tooltip.outerHeight();
	var viewportTop = $(window).scrollTop();
	var viewportBottom = viewportTop + $(window).height();

	tooltip.css({ left: tooltipLeft });

	if(tooltipBottom > viewportBottom) {
	  tooltip.css({ top: (viewportBottom - tooltip.outerHeight() - 25) });
	}
      });
    }
  });
});
</script>
<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
<link rel="alternate" title="Heikos Schrott- &amp; Rentnerfilme" type="application/rss+xml" href="feed.php">
<link href='hsrsearch.xml' rel='search' title='Suche in Heikos Schrott- &amp; Rentnerfilmen' type='application/opensearchdescription+xml'>
<title>Heikos Schrott- &amp; Rentnerfilme</title>
<?php include 'extra_js.php'; ?>
</head>
<body>
