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

require_once 'mysql_base.php';
require_once 'catnavtable.php';

final class CatChoice extends CatNavTable {

  private $result;
  private $movies;
  private $con;

  function __construct(Movies $m) {

    parent::__construct("Kategorie" , "cat_select");

    $this->con = MySQLBase::instance()->con();
    $this->result = $this->con->query("SELECT `id`, `name` FROM `categories` ORDER BY `id`");

    if($this->con->errno) {
      throw new ErrorException("MySQL-Fehler: ".$this->con->error);
    }

    $this->movies = $m;

  }

  function __destruct() {
    $this->result->free_result();
  }

  public function render() {

    $html = "<ul class=\"cat_nav\"><li class=\"cat_0".($this->movies->category() == -1 ? " cat_nav_active" : "")."\">".
    ($this->movies->category() != -1 ? "<a class=\"cat_nav\" href=\"".$this->movies->catQueryString(-1)."\">" : "").
    "Alle Videos".($this->movies->category() != -1 ? "</a>" : "")."</li>";

    while($row = $this->result->fetch_assoc()) {
      $html .= "<li class=\"cat_".$row['id'].($this->movies->category() == $row['id'] ? " cat_nav_active" : "")."\">".
      ($this->movies->category() != $row['id'] ? "<a class=\"cat_nav\" href=\"".$this->movies->catQueryString($row['id'])."\">" : "").
      htmlentities($row['name'], ENT_SUBSTITUTE, "utf-8").($this->movies->category() != $row['id'] ? "</a>" : "")."</li>";
    }

    $this->addRow(new Row(array(), array(new Cell(array('align' => "left"), $html))));

    return parent::render();
  }

}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
