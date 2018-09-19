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

trait AmpleTraits {

  private function ample($rating, $mid, $hid = "ample_mid") {
    switch((int)$rating) {
      case -1: return "<div id=\"".$hid.$mid."\" class=\"ample_off\">&nbsp;</div>";
      case  0: return "<div id=\"".$hid.$mid."\" class=\"ample_red\">&nbsp;</div>";
      case  1: return "<div id=\"".$hid.$mid."\" class=\"ample_yellow\">&nbsp;</div>";
      case  2: return "<div id=\"".$hid.$mid."\" class=\"ample_green\">&nbsp;</div>";
    }
  }

}

?>
