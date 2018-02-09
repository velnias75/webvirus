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

  require 'head.php';
  require 'classes/userbox.php';
  require 'classes/form/form.php';
  require 'classes/dataupdate.php';
  require 'classes/cat_choice.php';
  require 'classes/latest_disc.php';

?>

<table id="layout" border="0" width="100%">
  <tr><td id="layout_top" valign="middle" align="center" colspan="3">
    <h1><a id="title_link" href="<?php echo $_SERVER['PHP_SELF']; ?>">Heikos Schrott- &amp; Rentnerfilme</a></h1>
    <h3><span class="red_text">&#9995;</span>&nbsp;Die&nbsp;Webvirenversion&nbsp;<span class="red_text">&#9995;</span></h3></td></tr>
  <tr><td id="layout_left" align="center" valign="top">
      <?php
	try {
	  echo (new CatChoice($movies))->render().(new LatestDisc($movies))->render();
	} catch(Exception $e) {
	  echo "<strong>Fehler:</strong> ".htmlentities($e->getMessage(), ENT_SUBSTITUTE, "utf-8");
	}
      ?><table class="cat_nav downloads" border="0" width="100%">
	<tr><th class="cat_nav">Downloads</th></tr>
	<tr><td nowrap><a class="pdflink" href="pdf.php<?= $movies->fullQueryString() ?>" target="_blank">Filmliste als PDF-Datei</a></td></tr>
      </table>
      <?= (new Form(new UserBox(isset($_SESSION['ui']) ? $_SESSION['ui'] : null, $movies)))->render(); ?>
      <?php
	if(isset($_SESSION['ui']) && !isset($_SESSION['error']) && $_SESSION['ui']['admin'] && MySQLBase::instance()->update_allowed()) {
	  echo (new DataUpdate())->render();
	}
      ?>
    </td>
    <td id="layout_content" align="center" valign="top">
      <?php
	try {
	  echo (new Form($movies))->render();
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
	<tr><td align="center"><img border="0" alt="Ja, ich mag L&auml;rm und Krach!" src="https://www.rangun.de/metal-button.png"></td></tr>
      </table>
    </td></tr>
  <tr><td id="layout_bottom" valign="middle" align="center" colspan="3">
    <small>&copy;&nbsp;<?php echo strftime("%Y"); ?>&nbsp;by <a class="note_link" href="mailto:heiko@rangun.de?subject=Schrottfilme">Heiko Sch&auml;fer</a>
    <em>(<a class="note_link" target="_blank" href="https://github.com/velnias75/webvirus">work in progess</a>)</em></small></td></tr>
</table>

<?php require 'foot.php'; ?>
