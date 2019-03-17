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

require_once 'movies_base.php';
require_once 'catnavtable.php';
require_once 'form/formabletraits.php';

final class UserBox extends CatNavTable implements IFormable {

  use FormableTraits;

  private static $ALIGN_NOWRAP_ATTRS = array('align' => "center", 'nowrap' => null);

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

  public function hidden() {

    //if(!is_null($this->ui) && !isset($_SESSION['error']) && $this->ui['admin'] && MySQLBase::instance()->update_allowed()) { die;
      return array(
	'q' => urlencode($_SERVER['QUERY_STRING'])
	);
    /*} else {
      return array();
    }*/
  }

  public function method() {
    return IFormable::POST;
  }

  public function action() {
    return "login.php";
  }

  public function render() {

    if(is_null($this->ui) || isset($_SESSION['error'])) {

      if(isset($_SESSION['error'])) {
	$this->addRow(new Row(array(), array(new Cell(array('nowrap' => null),
	"<span class=\"red_text\">".htmlentities($_SESSION['error'], ENT_SUBSTITUTE, "utf-8")."</span>"))));
      }

      $this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
      "<label>Login:&nbsp;<input type=\"text\" size=\"5\" name=\"login\"></label>"))));
      $this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
      "<label>Passwort:&nbsp;<input type=\"password\" size=\"5\" name=\"pass\"></label>"))));
      $this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
      "<input type=\"checkbox\" name=\"pl\">&nbsp;eingeloggt bleiben"))));
      $this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
      "<input class=\"button\" type=\"submit\" name=\"btn[login]\" value=\"Einloggen\">"))));

    } else {

      $this->addRow(new Row(array(), array(new Cell(array('align' => "center"),
      "Willkommen ".htmlentities($this->ui['display_name'], ENT_SUBSTITUTE, "utf-8")."!"))));

      $this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
      "<input class=\"button\" type=\"submit\" name=\"btn[logout]\" value=\"Ausloggen\">"))));
      $this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS, "<hr>"))));

      $this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
      "<a id=\"remember_button\" class=\"button\" title=\"Setzt ALLE Filter zur&uuml;ck\" href=\"".
      $this->m->noFilterQueryString()."\">Alle Filter l&ouml;schen</a>"))));
      $this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
        "<a id=\"remember_button\" class=\"button\" ".
        "title=\"Merkt sich das aktuelle Ergebnis im Nr-Filter und setzt die anderen Filter zur&uuml;ck\" href=\"fid.php?".
          (isset($_GET['order_by']) ? "order_by=".$_GET['order_by'] : "")."\">Resultat merken</a>"))));

      if(isset($_SESSION['authd']) && $_SESSION['authd']) {

	$this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS, "<hr>"))));
	$this->addRow(new Row(array(), array(new Cell(array('nowrap' => null), "Passwort &auml;ndern:"))));
	$this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
	"<label>Passwort:&nbsp;<input type=\"text\" size=\"5\" name=\"pass_chg\"></label>"))));
	$this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
	"<input class=\"button\" type=\"submit\" name=\"btn[chg]\" value=\"&Auml;ndern\">"))));
      }

      if($this->ui['admin']) {
	$this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS, "<hr>"))));
	$this->addRow(new Row(array(), array(new Cell(array('nowrap' => null), "Benutzer anlegen:"))));
	$this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
	"<label>Name:&nbsp;<input type=\"text\" size=\"5\" name=\"display\"></label>"))));
	$this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
	"<label>Login:&nbsp;<input type=\"text\" size=\"5\" name=\"login_new\"></label>"))));
	$this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
	"<label>Passwort:&nbsp;<input type=\"text\" size=\"5\" name=\"pass_new\"></label>"))));
	$this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS,
	"<input class=\"button\" type=\"submit\" name=\"btn[create]\" value=\"Anlegen\">"))));
      }
    }

    if(isset($_GET['err'])) {
      $this->addRow(new Row(array(), array(new Cell(UserBox::$ALIGN_NOWRAP_ATTRS, "<hr>"))));
      $this->addRow(new Row(array(), array(new Cell(array(),
        "<span class=\"red_text\">".htmlentities($_GET['err'], ENT_SUBSTITUTE, "utf-8")."</span>"))));
    }

    return parent::render();
  }
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
