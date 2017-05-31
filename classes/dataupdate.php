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

require_once 'form/iformable.php';

final class UpdateSQL implements IFormable {

  use FormableTraits;

  public function method() {
    return IFormable::POST;
  }

  public function action() {
    return "update.php";
  }

  public function encType() {
    return "multipart/form-data";
  }

  public function embed() {
    return true;
  }

  public function render() {
    return  "<label class=\"fileContainer\"><input type=\"file\" name=\"dateiupload\"><input type=\"submit\" ".
      "name=\"btn[upload]\" accept=\"application/sql\"></label>";
  }

}

final class DataUpdate extends CatNavTable implements IRenderable {

  function __construct() {
    parent::__construct("Datenupdate", "userbox");
    $this->addRow(new Row(array(), array(new Cell(array(), new Form(new UpdateSQL())))));
  }

//   public function render() {
//     return parent::render();
//   }
}

?>
