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

require 'classes/mysql_base.php';
require 'ordermailtraits.php';

class Order {

  use OrderMailTraits;

  private $pdfd;
  private $pdfn;
  private $fida;
  private $fidu;

  function __construct($fids) {

	$this->fida = explode(',', urldecode($fids));
	$this->pdfn = tempnam("/tmp", "ORDER-").".pdf";
	$this->fidu = $this->getLink()."/?filter_ID=".$fids."&order_by=disc";

	if(!($this->pdfd = file_get_contents($this->getLink()."/pdf.php?filter_ID=".$fids."&order_by=disc"))) {
	  throw new RuntimeException("Couldn't create PDF-file");
	}

	if(!file_put_contents($this->pdfn, $this->pdfd)) {
	  throw new RuntimeException("Couldn't save PDF-file to temporary dir");
	}

  }

  function __destruct() {
	if(!unlink($this->pdfn)) {
	  error_log("Failed to unlink: ".$this->pdfn);
	}
  }

  public function mail($cc) {

	$msg = "Sehr geehrter Vollpfosten,</br></br>\r\n\r\n".
		   "dank der k&uuml;rzlich erworbenen Informatik-Diplome bin ich nun in der Lage</br>\r\n".
		   "folgende Bestellung an <b><u>Schrott- &amp; Rentnerfilmen</u></b> aufzugeben:</br><br>\r\n\r\n".
	       "<a href=\"".$this->fidu."\">".$this->fidu."</a>".
		   "</br></br>\r\n\r\nMit unfreundlichsten Gr&uuml;&szlig;en</br>\r\n".$cc."</br></br>\r\n\r\n";

	if(isset($_SERVER['HTTP_USER_AGENT'])) $msg .= "<em>".$_SERVER['HTTP_USER_AGENT']."</em>";

	date_default_timezone_set('Europe/Berlin');
	setlocale(LC_ALL, 'de_DE@euro', 'de_DE', 'de', 'ge');

	$this->mail_att("heiko@rangun.de", $cc, "😨 Hirnlose Rentnerfilmbestellung vom ".
					utf8_encode(strftime("%A, den %d. %B %Y"))." 😨", $msg,
					"😨 Heikos Schrott- & Rentnerfilme", "no-reply@rangun.de", "heiko@rangun.de",
					array($this->pdfn => "rentnerfilmbestellung.pdf"));
  }

  private function getLink() {
	return MySQLBase::instance()->protocol()."://".$_SERVER['SERVER_NAME'].MySQLBase::getRequestURI();
  }

}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
