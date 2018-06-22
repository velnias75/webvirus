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

require 'classes/movies_base.php';

final class TitleJSON extends MoviesBase {

  private $result;
  private $id;

  function __construct($cat = -1) {
    parent::__construct("ltitle", 0, -1, $cat);

    $this->id = isset($_GET['id']);

    $sid = isset($_GET['filter_ltitle']) && preg_match("/^#(0*(?!0)[0-9]+)$/", urldecode($_GET['filter_ltitle']), $m);

    $this->result = $this->mySQLRowsQuery($sid ? "#~~#".$m[1] : "", true);
  }

  function __destruct() {
    if(!is_null($this->result)) $this->result->free_result();
  }

  public function render() {

    $array = array();

    if(!is_null($this->result)) {
      while ($row = $this->result->fetch_assoc()) {
	if($this->id) {
	  $array[] = array('id' => $row['ID'], 'title' => $row['ltitle']);
	} else {
	  $array[] = $row['ltitle'];
	}
      }
    }

    echo json_encode(!is_null($array[0]) ? $array : array());
  }
}

?>
