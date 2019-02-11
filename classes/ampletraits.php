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

trait AmpleTraits {

  private function ample($rating, $mid, $hid = "ample_mid", $frac = null) {

    $ret = "";

    switch((int)(floor((double)$rating + 0.5))) {
      case -1: $ret .= "<div id=\"".$hid.$mid."\" class=\"ample_off\">&nbsp;</div>"; break;
      case  0: $ret .= "<div id=\"".$hid.$mid."\" class=\"ample_red\">&nbsp;</div>"; break;
      case  1: $ret .= "<div id=\"".$hid.$mid."\" class=\"ample_yellow\">&nbsp;</div>"; break;
      case  2: $ret .= "<div id=\"".$hid.$mid."\" class=\"ample_green\">&nbsp;</div>"; break;
    }

    if(!((int)$rating == -1 || is_null($frac))) {

      $r = 255;
      $g = 255;

      if((double)$rating < 1.0) { // yellow towards red
	$g -= $frac;
      } else if((double)$rating > 1.0) { // yellow towards green
	$r -= $frac;
      }

      //$ret .= "<script>".$rating." ".$frac." #".dechex($r).dechex($g)."0080</script>";
      $ret .= "<script>document.getElementById('".$hid.$mid."').style.backgroundColor='#".dechex($r).dechex($g)."0080';</script>";
    }

    return $ret;
  }

}

?>
