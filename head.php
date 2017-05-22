<!-- Schrottfilme - Webinterface. (c) 2017 by Heiko Schaefer <heiko@rangun.de> -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />
<meta name="description" content="Liste der auf DVD gespeicherten Filme, Dokumentationen und Konzerten">
<meta name="keywords" content="MP4, MKV, Filmliste, Spielfilme, Dokumentationen, Dokus, Konzerte">
<script src="https://cdn.jsdelivr.net/jquery/3.2.1/jquery.min.js"></script>
<script src="https://twitter.github.com/typeahead.js/releases/latest/typeahead.bundle.js"></script>
<script>
var titles = new Bloodhound({
  datumTokenizer: Bloodhound.tokenizers.whitespace,
  queryTokenizer: Bloodhound.tokenizers.whitespace,
  prefetch: {
      url: '<?= dirname($_SERVER['REQUEST_URI'])."/" ?>title-json.php?cat=<?= isset($_GET['cat']) ? $_GET['cat'] : -1 ?>',
      cache: false
    }
});

$(document).ready(function() {
  $('#list_filter_ltitle').typeahead({
    hint: false,
    highlight: true,
    minLength: 3
  }, {
    source: titles,
    limit: 10
  });
})

$(document).ready(function() {
  $('#list_filter_ltitle').focus();
})
</script>
<link rel="shortcut icon" type="image/x-icon" href="img/favicon.ico">
<link rel="alternate" title="Heikos Schrott- &amp; Rentnerfilme" type="application/rss+xml" href="feed.php">
<link href='hsrsearch.xml' rel='search' title='Suche in Heikos Schrott- &amp; Rentnerfilmen' type='application/opensearchdescription+xml'>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/font-hack/2.020/css/hack-extended.min.css">
<link rel="stylesheet" href="css/master.php?t=<?php echo time(); ?>" rel="stylesheet" title="Hirnloser Stil" type="text/css" media="screen">
<link rel="stylesheet" href="css/print.css?t=<?php echo time(); ?>" rel="alternate stylesheet" title="Druckversion" type="text/css" media="screen">
<link rel="stylesheet" href="css/print.css?t=<?php echo time(); ?>" type="text/css" media="print">
<title>Heikos Schrott- &amp; Rentnerfilme</title>
</head>
<body>