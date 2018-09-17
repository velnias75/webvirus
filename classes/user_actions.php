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

  private $rating = -1;
  private $ui = null;
  private $id = -1;

  function __construct($ui, $id, $rating) {
    $this->ui     = $ui;
    $this->id     = $id;
    $this->rating = $rating;
  }

  public function render() {

    $rcheck = array($this->rating == -1 ? "checked" : "",
		    $this->rating ==  2 ? "checked" : "",
		    $this->rating ==  1 ? "checked" : "",
		    $this->rating ==  0 ? "checked" : "");

    return "<br />Hirnlose Bewertung:<table>".
      "<tr><td><input id=\"ampleoff\" type=\"radio\" name=\"ample_".$this->id."\" value=\"-1\" ".$rcheck[0].">".
      "<label for=\"ampleoff\"><div id=\"ample_off\">&nbsp;</div>unbewertet/ungesehen</label></td></tr>".
      "<tr><td><input id=\"amplegreen\" type=\"radio\" name=\"ample_".$this->id."\" value=\"2\" ".$rcheck[1].">".
      "<label for=\"amplegreen\"><div id=\"ample_green\">&nbsp;</div>gut</label></td></tr>".
      "<tr><td><input id=\"ampleyellow\" type=\"radio\" name=\"ample_".$this->id."\" value=\"1\" ".$rcheck[2].">".
      "<label for=\"ampleyellow\"><div id=\"ample_yellow\">&nbsp;</div>okay</label></td></tr>".
      "<tr><td><input id=\"amplered\" type=\"radio\" name=\"ample_".$this->id."\" value=\"0\" ".$rcheck[3].">".
      "<label for=\"amplered\"><div id=\"ample_red\">&nbsp;</div>schrecklich</label></td></tr>".
      "</table>";
  }

}

?>
