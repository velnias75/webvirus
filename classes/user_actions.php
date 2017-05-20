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

final class UserActions {

  private $ui = null;
  private $id = -1;

  function __construct($ui, $id) {
    $this->ui = $ui;
    $this->id = $id;
  }

  public function render() {
    return "Render some useless user actions<br>for ".htmlentities($this->ui['display_name'], ENT_SUBSTITUTE, "utf-8")." here...";
  }

}

?>
