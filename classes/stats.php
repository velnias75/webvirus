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

require_once 'ampletraits.php';
require_once 'table/table.php';
require_once 'mysql_base.php';

final class Stats extends Table {

  use AmpleTraits;

  private $con;

  function __construct() {
    $this->con = MySQLBase::instance()->con();
  }

  private function cid2class($cid) {

    if($cid != -1) {
      return "cat_".$cid;
    }

    return "cat_0";
  }

  public final function render() {

    $stat_res = $this->con->query("SELECT cid, stat, duration, category, mid, title, ord FROM statistics");

    while($row = $stat_res->fetch_assoc()) {
      $this->addRow(new Row(
	null,
	array(
	  new Cell(array('align' => 'right', 'nowrap' => null, 'class' => $this->cid2class($row['cid'])),
	  htmlentities($row['stat'], ENT_SUBSTITUTE, "utf-8").":&nbsp;"),
	  new Cell(array('align' => ($row['ord'] != 3 ? 'center' : 'left'), 'nowrap' => null, 'class' => $this->cid2class($row['cid'])),
	  $row['ord'] != 3 ? $row['duration'] : ($this->ample($row['duration'], $row['category'], "ample_stat", true)."&nbsp;(".
	  number_format(round((float)$row['duration'], 2), 2, '.', '').")&nbsp;")),
	  new Cell(array('align' => 'left', 'nowrap' => null, 'class' => $this->cid2class($row['cid'])),
	  htmlentities($row['category'], ENT_SUBSTITUTE, "utf-8")."&nbsp;"),
	  new Cell(array('align' => 'left', 'nowrap' => null, 'class' => $this->cid2class($row['cid'])),
	  is_null($row['title']) ? null : "&nbsp;&ndash;&nbsp;"),
	  new Cell(array('align' => 'right', 'nowrap' => null, 'class' => $this->cid2class($row['cid'])),
	  is_null($row['title']) ? null : htmlentities($row['mid'], ENT_SUBSTITUTE, "utf-8")),
	  new Cell(array('align' => 'left', 'nowrap' => null, 'class' => $this->cid2class($row['cid'])),
	  is_null($row['title']) ? null : "&nbsp;".htmlentities($row['title'], ENT_SUBSTITUTE, "utf-8")."&nbsp;")
	  )));
    }

    $stat_res->free_result();

    return parent::render();
  }

}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
