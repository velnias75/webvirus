<?php
/*
 * Copyright 2018 by Heiko Schäfer <heiko@rangun.de>
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

require_once 'table/table.php';
require_once 'mysql_base.php';

final class Stats extends Table {

  private $con;

  function __construct() {
    $this->con = MySQLBase::instance()->con();
  }

  public final function render() {

    $stat_res = $this->con->query("SELECT stat, duration, category, title FROM statistics");

    while($row = $stat_res->fetch_assoc()) {
      $this->addRow(new Row(
	null,
	array(
	  new Cell(array('align' => 'right', 'nowrap' => null), htmlentities($row['stat'], ENT_SUBSTITUTE, "utf-8").":&nbsp;"),
	  new Cell(array('align' => 'center', 'nowrap' => null), htmlentities($row['duration'], ENT_SUBSTITUTE, "utf-8")."&nbsp;"),
	  new Cell(array('align' => 'left', 'nowrap' => null), htmlentities($row['category'], ENT_SUBSTITUTE, "utf-8")."&nbsp;"),
	  new Cell(null),
      )));
    }

    $stat_res->free_result();

    return parent::render();
  }

}

?>
