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

require_once 'filterdrop_base.php';

final class FilterdropDisc extends FilterdropBase {

  function __construct() {
    parent::__construct("SELECT `name`, `ID` FROM `disc` AS `disc` WHERE `ID` <> 265 AND `ID` <> 263 ORDER BY `regular` ASC, `vdvd` ASC, LEFT( `name`, 1 ) ASC, LENGTH( `name` ) ASC, `name` ASC");
  }

  protected function idField() {
    return "ID";
  }

  protected function nameField() {
    return "name";
  }

  protected function filterName() {
    return "filter_disc";
  }

  protected function noneValue() {
    return -1;
  }

}

?>
