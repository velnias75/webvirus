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

require_once 'irenderable.php';
require_once 'table/table.php';
require_once 'table/headercell.php';

class CatNavTable extends Table {

  protected function __construct($title, $clazz = "") {
    parent::__construct(array('class' => trim("cat_nav ".$clazz), 'border' => "0", 'width' => "100%"));
    $this->addRow(new Row(array(), array(new HeaderCell(array('class' => "cat_nav"), $title))));
  }
}

?>
