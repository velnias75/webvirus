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

require 'pdfbase.php';
require 'movies_base.php';

define("MAX_TITLE_LENGTH", 105);

final class PDF extends MoviesBase {

  private $pdf;

  function __construct($order_by = "ltitle", $from = 0, $to = -1, $cat = -1) {

    parent::__construct($order_by, $from, $to, $cat);

    $this->pdf = new PDFBase($this->latest());
    $this->pdf->pdf()->SetDrawColor(204, 204, 204);
    $this->pdf->pdf()->SetLineWidth(0.1);
    $this->pdf->pdf()->AddPage();
  }

  private function makeDescLine($wx, $pt, $fs) {

    $this->pdf->pdf()->SetTextColor(0, 0, 0);
    $this->pdf->pdf()->SetFont('Hack', '', $fs);
    $this->pdf->pdf()->Cell($wx['id_w'], $pt, "Nr", 0, 0, "R");
    $this->pdf->pdf()->SetFont('Arial', 'B', $fs);
    $this->pdf->pdf()->Cell($wx['ltitle_w'], $pt, "Titel", 0, 0, "C");
    $this->pdf->pdf()->SetFont('Courier', '', $fs);
    $this->pdf->pdf()->Cell($wx['duration_w'], $pt, iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', "Länge"), 0, 0, "C");
    $this->pdf->pdf()->SetFont('Hack', 'I', $fs);
    $this->pdf->pdf()->Cell($wx['lingos_w'], $pt, "Sprache(n)", 0, 0, "C");
    $this->pdf->pdf()->SetFont('Arial', '', $fs);
    $this->pdf->pdf()->Cell($wx['disc_w'], $pt, "DVD", 0, 1, "L");

    $this->pdf->pdf()->Ln();
  }

  private function makeTitle($title) {

    $t = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $title);

    if((preg_match('/^(\?( |\?)+)? \x96 (.*)|(.*[^\x96]*) \x96 \?( |\?)+([^\?]*)$/', $t, $m) === 1)) {
      $t = strlen($m[3]) ? $m[3] : ($m[4].(strlen($m[6]) ? " ".$m[6] : ""));
    }

    if(preg_match('/(^[ ]+\x96 (.*))|(.*)\x96[^\x96]* ( [\(\[].*)?$/', $t, $m)) {
      $t = count($m) == 4 ? $m[3] : (count($m) == 3 ? $m[2] : $m[3].$m[4]);
    }

    if(strlen($t) > MAX_TITLE_LENGTH) {
      return substr($t, 0, MAX_TITLE_LENGTH)."...";
    }

    return $t;
  }

  public function render() {

    $result = $this->mySQLRowsQuery();

    if($result) {

      $i = 0;
      $cat = array();

      $id = array();
      $id_w = array();

      $ltitle = array();
      $ltitle_w = array();

      $duration = array();
      $duration_w = array();

      $lingos = array();
      $lingos_w = array();

      $disc = array();
      $disc_w = array();

      $fs = 10;

      while($row = $result->fetch_assoc()) {

	$this->pdf->pdf()->SetFont('Hack', '', $fs);
	array_push($id, iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $this->makeLZID($row['ID'])));
	array_push($id_w, $this->pdf->pdf()->GetStringWidth(array_values(array_slice($id, -1))[0]));

	$this->pdf->pdf()->SetFont('Arial', 'B', $fs);
	array_push($ltitle, $this->makeTitle($row['ltitle']));
	array_push($ltitle_w, $this->pdf->pdf()->GetStringWidth(array_values(array_slice($ltitle, -1))[0]));

	$this->pdf->pdf()->SetFont('Courier', '', $fs);
	array_push($duration, iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $row['duration']));
	array_push($duration_w, $this->pdf->pdf()->GetStringWidth(array_values(array_slice($duration, -1))[0]));

	$this->pdf->pdf()->SetFont('Hack', 'I', $fs);
	array_push($lingos, iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $row['lingos']));
	array_push($lingos_w, $this->pdf->pdf()->GetStringWidth(array_values(array_slice($lingos, -1))[0]));

	$this->pdf->pdf()->SetFont('Arial', '', $fs);
	array_push($disc, iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $row['disc']));
	array_push($disc_w, $this->pdf->pdf()->GetStringWidth(array_values(array_slice($disc, -1))[0]));

	array_push($cat, $row['category']);

	$i++;
      }

      do {

	$cw = max($id_w) + max($ltitle_w) + max($duration_w) + max($lingos_w) + max($disc_w) + 20;
	$of = ceil((($this->pdf->pdf()->GetPageWidth() - 35) - $cw)/2);

	if($of < 0) {

	  $fs -= 0.5;

	  for($j = 0; $j < $i; $j++) {

	    $this->pdf->pdf()->SetFont('Hack', '', $fs);
	    $id_w[$j] = $this->pdf->pdf()->GetStringWidth($id[$j]);

	    $this->pdf->pdf()->SetFont('Arial', 'B', $fs);
	    $ltitle_w[$j] = $this->pdf->pdf()->GetStringWidth($ltitle[$j]);

	    $this->pdf->pdf()->SetFont('Courier', '', $fs);
	    $duration_w[$j] = $this->pdf->pdf()->GetStringWidth($duration[$j]);

	    $this->pdf->pdf()->SetFont('Hack', 'I', $fs);
	    $lingos_w[$j] = $this->pdf->pdf()->GetStringWidth($lingos[$j]);

	    $this->pdf->pdf()->SetFont('Arial', '', $fs);
	    $disc_w[$j] = $this->pdf->pdf()->GetStringWidth($disc[$j]);
	  }

	}

      } while($of < 0);

      $wx = array(
	'id_w' => max($id_w) + 5,
	'ltitle_w' => max($ltitle_w) + $of + 5,
	'duration_w' => max($duration_w) + 5,
	'lingos_w' => max($lingos_w) + $of + 5,
	'disc_w' => max($disc_w)
	);

	$pt = ceil($fs * 0.3528) + 1;
	$rw = floor(($this->pdf->pdf()->GetPageHeight() - 27)/$pt) - 4;
	$cr = 0;

	$this->makeDescLine($wx, $pt, $fs);

	for($j = 0; $j < $i; $j++) {

	  $this->pdf->pdf()->SetTextColor(0, 0, 0);
	  $this->pdf->pdf()->SetFont('Hack', '', $fs - 0.1);
	  $this->pdf->pdf()->Cell($wx['id_w'], $pt, $id[$j], 'TB', 0, "R");

	  if($this->category() == -1) {
	    if($cat[$j] == 2) {
	      $this->pdf->pdf()->SetTextColor(102, 51, 159);
	    } else if($cat[$j] == 3) {
	      $this->pdf->pdf()->SetTextColor(37, 49, 0);
	    } else if($cat[$j] == 4) {
	      $this->pdf->pdf()->SetTextColor(81, 0, 0);
	    }
	  }

	  $this->pdf->pdf()->SetFont('Arial', 'B', $fs);
	  $this->pdf->pdf()->Cell($wx['ltitle_w'], $pt, $ltitle[$j], 'TB', 0, "L");
	  $this->pdf->pdf()->SetFont('Courier', '', $fs);
	  $this->pdf->pdf()->Cell($wx['duration_w'], $pt, $duration[$j], 'TB', 0, "R");
	  $this->pdf->pdf()->SetFont('Hack', 'I', $fs);
	  $this->pdf->pdf()->Cell($wx['lingos_w'], $pt, $lingos[$j], 'TB', 0, "L");
	  $this->pdf->pdf()->SetFont('Arial', '', $fs);
	  $this->pdf->pdf()->Cell($wx['disc_w'], $pt, $disc[$j], 'TB', 1, "L");

	  $cr++;

	  if($cr >= $rw) {
	    $this->makeDescLine($wx, $pt, $fs);
	    $cr = 0;
	  }
	}

	$this->pdf->pdf()->SetTextColor(0, 0, 0);

	$total_res = $this->mySQLTotalQuery();

	if($total_res) $total = $total_res->fetch_assoc();

	if($total_res && $total) {

	  $this->pdf->pdf()->Ln();
	  $this->pdf->pdf()->SetFont('Hack', '', $fs);
	  $this->pdf->pdf()->Cell($wx['id_w'], $pt, $result->num_rows, 0, 0, "R");
	  $this->pdf->pdf()->SetFont('Arial', '', $fs);
	  $this->pdf->pdf()->Cell($wx['ltitle_w'], $pt, ($result->num_rows != 1 ? "Videos insgesamt" : "Video"), 0, 0, "L");
	  $this->pdf->pdf()->SetFont('Courier', '', $fs);
	  $this->pdf->pdf()->Cell($wx['duration_w'], $pt, $this->secondsToDHMS($total['tot_dur']), 0, 0, "R");
	  $this->pdf->pdf()->SetFont('Hack', 'I', $fs);
	  $this->pdf->pdf()->Cell($wx['lingos_w'], $pt, "", 0, 0, "L");
	  $this->pdf->pdf()->SetFont('Arial', '', $fs);
	  $this->pdf->pdf()->Cell($wx['disc_w'], $pt, "", 0, 1, "L");

	  $total_res->free_result();
	}

	$result->free_result();
    }

    $this->pdf->pdf()->Output('I', "filmliste-".$this->pdf->latest().".pdf", true);
  }

}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
