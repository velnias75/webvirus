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

require_once 'movies_base.php';
require_once 'catnavtable.php';

final class UserBox extends CatNavTable {

  private $m;
  private $ui = null;

  function __construct($ui, MoviesBase $m) {

    parent::__construct("Benutzerbereich", "userbox");

    $this->m  = $m;
    $this->ui = $ui;
  }

  protected function getClass() {
    return "userbox";
  }

  public function render() {

    echo "<form method=\"POST\" action=\"login.php\">\n";
    echo "<input type=\"hidden\" name=\"q\" value=\"".urlencode($_SERVER['QUERY_STRING'])."\">\n";

    if(!is_null($this->ui) && !isset($_SESSION['error']) && $this->ui['admin'] && MySQLBase::instance()->update_allowed()) {
      echo "<input type=\"hidden\" name=\"q\" value=\"".urlencode($_SERVER['QUERY_STRING'])."\">\n";
    }

    if(is_null($this->ui) || isset($_SESSION['error'])) {

      if(isset($_SESSION['error'])) {
	$this->addRow(new Row(array(), array(new Cell(array('nowrap' => null),
	"<span class=\"red_text\">".htmlentities($_SESSION['error'], ENT_SUBSTITUTE, "utf-8")."</span>"))));
      }

      $this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
      "<label>Login:&nbsp;<input type=\"text\" size=\"5\" name=\"login\"></label>"))));
      $this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
      "<label>Passwort:&nbsp;<input type=\"password\" size=\"5\" name=\"pass\"></label>"))));
      $this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
      "<input type=\"submit\" name=\"btn[login]\" value=\"Einloggen\">"))));

    } else {

      $this->addRow(new Row(array(), array(new Cell(array('align' => "center"),
      "Willkommen ".htmlentities($this->ui['display_name'], ENT_SUBSTITUTE, "utf-8")."!"))));

      $this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
      "<input type=\"submit\" name=\"btn[logout]\" value=\"Ausloggen\">"))));
      $this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null), "<hr>"))));

      $this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
      "<a id=\"remember_button\" title=\"Setzt ALLE Filter zur&uuml;ck\" href=\"".
      $this->m->noFilterQueryString()."\">Alle Filter l&ouml;schen</a>"))));
      $this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
      "<a id=\"remember_button\" ".
      "title=\"Merkt sich das aktuelle Ergebnis im Nr-Filter und setzt die anderen Filter zur&uuml;ck\" href=\"fid.php?".
      (isset($_GET['order_by']) ? "order_by=".$_GET['order_by'] : "")."\">Resultat merken</a><hr>"))));

      $this->addRow(new Row(array(), array(new Cell(array('nowrap' => null), "Passwort &auml;ndern:"))));
      $this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
      "<label>Passwort:&nbsp;<input type=\"text\" size=\"5\" name=\"pass_chg\"></label>"))));
      $this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
      "<input type=\"submit\" name=\"btn[chg]\" value=\"&Auml;ndern\">"))));

      if($this->ui['admin']) {
	$this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null), "<hr>"))));
	$this->addRow(new Row(array(), array(new Cell(array('nowrap' => null), "Benutzer anlegen:"))));
	$this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
	"<label>Name:&nbsp;<input type=\"text\" size=\"5\" name=\"display\"></label>"))));
	$this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
	"<label>Login:&nbsp;<input type=\"text\" size=\"5\" name=\"login_new\"></label>"))));
	$this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
	"<label>Passwort:&nbsp;<input type=\"text\" size=\"5\" name=\"pass_new\"></label>"))));
	$this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
	"<input type=\"submit\" name=\"btn[create]\" value=\"Anlegen\">"))));
      }
    }

    if(!is_null($this->ui) && !isset($_SESSION['error']) && $this->ui['admin'] && MySQLBase::instance()->update_allowed()) {
      $this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null), "<hr>"))));
      $this->addRow(new Row(array(), array(new Cell(array(),
      "<form action=\"update.php\" method=\"POST\" enctype=\"multipart/form-data\">".
      "<label class=\"fileContainer\">Datenupdate: <input type=\"file\" name=\"dateiupload\"><input type=\"submit\" ".
      "name=\"btn[upload]\" accept=\"application/sql\"></label>"))));
    }

    echo parent::render();
    echo "</form>\n";
  }
}

?>
