<?php
/*
 * Copyright 2018-2019 by Heiko Schäfer <heiko@rangun.de>
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

require_once 'mysql_base.php';
require_once 'catnavtable.php';

final class LatestDisc extends CatNavTable {

  private $created;
  private $result;
  private $movies;
  private $con;

  function __construct(Movies $m) {

    parent::__construct("Neueste DVD", "latest_dvd");

    $this->con = MySQLBase::instance()->con();

    $this->result = $this->con->query("SELECT `id`, `name`, `created` ".
    "FROM `disc` ORDER BY `created` DESC LIMIT 1");

    $pdate = date_parse_from_format("Y-m-d H:i:s", $this->result->fetch_assoc()['created']);
    $this->created = mktime($pdate['hour'], $pdate['minute'], $pdate['second'], $pdate['month'], $pdate['day'], $pdate['year']);
    $this->result->free_result();

    $this->result = $this->con->query("SELECT `id`, `name`, DATE_FORMAT(`created`, '%d.%m.%Y') AS `df` ".
    "FROM `disc` ORDER BY `created` DESC LIMIT 1");

    if($this->con->errno) {
      throw new ErrorException("MySQL-Fehler: ".$this->con->error);
    }

    if(!isset($_SESSION['display_ldnot'])) $_SESSION['display_ldnot'] = true;

    $this->movies = $m;
  }

  function __destruct() {
    $this->result->free_result();
  }

  public function render() {

    $row = $this->result->fetch_assoc();

    $newdvd = (($GLOBALS['dblastvisit'] != null && $GLOBALS['dblastvisit'] < $this->created) ||
    (isset($_COOKIE["dbnewdvd"]) && $_COOKIE["dbnewdvd"])) ? array("<font color=\"red\">", "</font>".
    (isset($_SESSION['display_ldnot']) && $_SESSION['display_ldnot'] ? "<script>".
    "Notification.requestPermission().then(function(result) {".
    "if(result == 'granted') {".
    "var notification = new Notification('Heikos Schrott- & Rentnerfilme', { body: 'Es gibt eine neue DVD: ".$row['name']."', icon: 'img/favicon.ico' });".
    "window.setTimeout(notification.close.bind(notification), 10000);".
    "}});</script>" : "")) : array("", "");

    if(isset($_COOKIE["dbnewdvd"]) && $_COOKIE["dbnewdvd"]) $_SESSION['display_ldnot'] = false;

    if(($GLOBALS['dblastvisit'] != null && $GLOBALS['dblastvisit'] < $this->created) && !isset($_COOKIE["dbnewdvd"])) {
      setcookie("dbnewdvd", true, time()+60*60*24);
    }

    $this->addRow(new Row(array(), array(new Cell(array('align' => "left", 'nowrap' => null),
    "<ul class=\"cat_nav\"><li>".$newdvd[0]."<a class=\"cat_nav\" href=\"".$this->movies->discQueryString($row['id'])."\">".
    htmlentities($row['name'], ENT_SUBSTITUTE, "utf-8")."</a>&nbsp;(".htmlentities($row['df'], ENT_SUBSTITUTE, "utf-8").
    ")".$newdvd[1]."</li></ul>"))));
    if($GLOBALS['dblastvisit'] != null ) $this->addRow(new Row(array(), array(new Cell(array('align' => "center", 'nowrap' => null),
      "<small><b>Mein letzter Besuch:&nbsp;".strftime("%d.%m.%Y", $GLOBALS['dblastvisit'])."</b></small>"))));

    return parent::render();
  }
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
