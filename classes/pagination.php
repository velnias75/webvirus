<?php
/*
 * Copyright 2017-2019 by Heiko Schäfer <heiko@rangun.de>
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

require_once 'table/table.php';

final class Pagination extends Table {

  function __construct($rows, $tits, $q, $page_size, $limit_from, $limit_to) {

    parent::__construct(array('width' => "100%", 'border' => "0"));

    $psize = abs(($limit_to == -1 ? $page_size : abs($limit_to)) - abs($limit_from));
    $pages = ceil($rows/($psize + 1));

    $prev  = ($limit_from - $psize - 1) >= 0 ? $limit_from - $psize - 1 : ($pages - 1) * ($psize + 1);
    $next  = ($limit_from + $psize + 1) < $rows ? $limit_from + $psize + 1 : 0;

    $lratt = array('width' => min(2, floor(100/($pages + 4)))."%", 'class' => "page_nr");

    $cells = array(
      $this->createAllPage($rows, $tits, $limit_from, $limit_to, $q),
      new Cell($lratt,
      "<a ".(count($tits) ? "title=\"".$tits[$prev]." &#8594;&#13;&#10;".
      $tits[min($prev + $psize, $rows - 1)]."\"" : "")." class=\"page_nr\" href=\"".
      $q."&amp;from=".$prev."&amp;to=".($prev + $psize)."\">&#10525;</a>"));

      $ncell = new Cell($lratt, "&#8230;");

      $fill_l = false;
      $fill_r = false;

      $curr_m = ceil($limit_from/($psize + 1));

      if($curr_m <= 36) {
	$curr_l = 0;
	$curr_r = 38;
      } else if($curr_m >= (($pages - 1) - 36)) {
	$curr_l = ($pages - 1) - 38;
	$curr_r = ($pages - 1);
      } else {
	$curr_l = $curr_m - 18;
	$curr_r = $curr_m + 18;
      }

      for($i = 0; $i < $pages; $i++) {

	$from  = $i * ($psize + 1);
	$activ = $limit_to == -1 || !(abs($limit_from) >= $from && abs($limit_to) <= ($from + $psize));

	if($pages <= 40 || ($i == 0 || $i == ($pages - 1) || ($i > $curr_l && $i < $curr_r))) {

	  $patt = array('width' => min(2, floor(100/($pages + 4)))."%",
	  'title' => $tits[$from]." &#8594;&#13;&#10;".$tits[min($from + $psize, $rows - 1)]);

	  if($activ) {
	    $patt['class'] = "page_nr";
	  } else {
	    $patt['class'] = "page_nr page_active ";
	  }

	  $cells[] = new Cell($patt, ($activ ? "<a class=\"page_nr\" href=\"".$q."&amp;from=".$from."&amp;to=".($from + $psize)."\">" : "").
	  ($i + 1).($activ ? "</a>" : ""));

	} else if(!$fill_l && $i <= $curr_l) {
	  $cells[] = $ncell;
	  $fill_l  = true;
	} else if(!$fill_r && $i >= $curr_r) {
	  $cells[] = $ncell;
	  $fill_r  = true;
	}
      }

      $cells[] = new Cell($lratt,
      "<a ".(count($tits) ? "title=\"".$tits[$next]." &#8594;&#13;&#10;".$tits[min($next + $psize, $rows - 1)].
      "\"" : "")." class=\"page_nr\" href=\"".$q."&amp;from=".$next."&amp;to=".($next + $psize)."\">&#10526;</a>");
      $cells[] = $this->createAllPage($rows, $tits, $limit_from, $limit_to, $q);

      $this->addRow(new Row(array('align' => "center"), $cells));
  }

  private function createAllPage($rows, $tits, $limit_from, $limit_to, $q) {
    return new Cell(array('class' => "page_nr".($limit_to == -1 ? " page_active" : "")),
    ($limit_to == -1 ? "Alle" : "<a class=\"page_nr\" ".(count($tits) ? "title=\"".$tits[0]." &#8594;&#13;&#10;".
    $tits[$rows - 1]."\"" : "")." href=\"".$q."&amp;from=0&amp;to=-1\">Alle</a>"));
  }

  public function render() {
    return parent::render();
  }

}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
