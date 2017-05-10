<?php

  require 'head.php';
  require 'classes/movies.php';
  require 'classes/cat_choice.php';
  require 'classes/latest_disc.php';
  
  try {
    $movies = new Movies(isset($_GET['order_by']) ? $_GET['order_by'] : "ltitle", 
	    isset($_GET['from']) ? $_GET['from'] : 0,
	    isset($_GET['to']) ? $_GET['to'] : 24, isset($_GET['cat']) ? $_GET['cat'] : -1);
  } catch(Exception $e) {
    echo "<strong>Fehler:</strong> ".htmlentities($e->getMessage(), ENT_SUBSTITUTE, "utf-8");
  }
  
?>

<table id="layout" border="0" width="100%">
  <tr><td id="layout_top" valign="center" align="center" colspan="3">
    <h1><a href="index.php">Heikos Schrott- &amp; Rentnerfilme</a></h1>
    <h3><span class="red_text">&#9995;</span>&nbsp;Die&nbsp;Webvirenversion&nbsp;<span class="red_text">&#9995;</span></h3></td></tr>
  <tr><td id="layout_left" align="center" valign="top">
      <?php
	try {
	  (new CatChoice($movies))->render();
	  (new LatestDisc($movies))->render();
	} catch(Exception $e) {
	  echo "<strong>Fehler:</strong> ".htmlentities($e->getMessage(), ENT_SUBSTITUTE, "utf-8");
	}
      ?>
      <table class="cat_nav" border="0" width="100%">
	<tr><th class="cat_nav">Downloads</th></tr>
	<tr><td nowrap><a class="pdflink" href="http://rangun.de/filmliste-alpha.pdf" target="_blank">Filmliste als PDF-Datei</a></td></tr>
      </table>
    </td>
    <td id="layout_content" align="center" valign="top">
      <?php 
	try {
	  $movies->render(); 
	} catch(Exception $e) {
	  echo "<strong>Fehler:</strong> ".htmlentities($e->getMessage(), ENT_SUBSTITUTE, "utf-8");
	}
      ?>
      </td>
    <td id="layout_right" valign="top">
      <table class="cat_nav" border="0" width="100%">
	<tr><th class="cat_nav">Hinweise</th></tr>
	<tr><td class="notes"><?php include 'notes.php'; ?></td></tr>
      </table>
      <table class="cat_nav" border="0" width="100%">
	<tr><th class="cat_nav">Sonstiges</th></tr>
	<tr><td align="center"><a target="_blank" href="https://www.openhub.net/accounts/Velnias?ref=sample"><img alt='Open Hub profile for Heiko Schäfer' border='0' height='15' src='https://www.openhub.net/accounts/Velnias/widgets/account_tiny?format=gif&amp;ref=sample' width='80'></a></td></tr>
	<tr><td align="center"><img border="0" src="http://www.rangun.de/metal-button.png"></td></tr>
      </table>
    </td></tr>
  <tr><td id="layout_bottom" valign="center" align="center" colspan="3">
    <small>&copy;&nbsp;<?php echo strftime("%Y"); ?>&nbsp;by <a class="note_link" href="mailto:heiko@rangun.de?subject=Schrottfilme">Heiko Sch&auml;fer</a> <em>(WORK IN PROGRESS)</em></small></td></tr>
</table>

<?php require 'foot.php'; ?>
