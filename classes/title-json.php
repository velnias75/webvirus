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

  function __construct() {
    parent::__construct();

    $this->result = $this->mySQLRowsQuery();
  }

  function __destruct() {
    $this->result->free_result();
  }

  public function render() {

    $array = array();

    while ($row = $this->result->fetch_assoc()) {
      $array[] = $row['st'];
    }

    echo json_encode($array);
  }
}

?>
