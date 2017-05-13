<?php

require 'pdfbase.php';
require 'movies_base.php';

final class PDF extends MoviesBase {

  private $pdf;

  function __construct($order_by = "ltitle", $from = 0, $to = -1, $cat = -1) {
    
    parent::__construct($order_by, $from, $to, $cat);
    
    $this->pdf = new PDFBase($this->latest());
    $this->pdf->pdf()->SetDrawColor(204, 204, 204);
    $this->pdf->pdf()->SetLineWidth(0.1);
    $this->pdf->pdf()->AddPage();
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
	array_push($id, iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $row['ID']));
	array_push($id_w, $this->pdf->pdf()->GetStringWidth($id[$i]));
	
	$this->pdf->pdf()->SetFont('Arial', 'B', $fs);
	array_push($ltitle, iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $row['ltitle']));
	array_push($ltitle_w, $this->pdf->pdf()->GetStringWidth($ltitle[$i]));
	
	$this->pdf->pdf()->SetFont('Courier', '', $fs);
	array_push($duration, iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $row['duration']));
	array_push($duration_w, $this->pdf->pdf()->GetStringWidth($duration[$i]));
	
	$this->pdf->pdf()->SetFont('Hack', 'I', $fs);
	array_push($lingos, iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $row['lingos']));
	array_push($lingos_w, $this->pdf->pdf()->GetStringWidth($lingos[$i]));
	
	$this->pdf->pdf()->SetFont('Arial', '', $fs);
	array_push($disc, iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $row['disc']));
	array_push($disc_w, $this->pdf->pdf()->GetStringWidth($disc[$i]));
	
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
    
      for($j = 0; $j < $i; $j++) {
      
        $this->pdf->pdf()->SetTextColor(0, 0, 0);
        $this->pdf->pdf()->SetFont('Hack', '', $fs);
	$this->pdf->pdf()->Cell($wx['id_w'], $pt, $id[$j], 'B', 0, "R");
	
	if($cat[$j] == 2) {
	  $this->pdf->pdf()->SetTextColor(102, 51, 159);
	} else if($cat[$j] == 3) {
	  $this->pdf->pdf()->SetTextColor(37, 49, 0);
	} else if($cat[$j] == 4) {
	  $this->pdf->pdf()->SetTextColor(81, 0, 0);
	}
	
	$this->pdf->pdf()->SetFont('Arial', 'B', $fs);
	$this->pdf->pdf()->Cell($wx['ltitle_w'], $pt, $ltitle[$j], 'B', 0, "L");
	$this->pdf->pdf()->SetFont('Courier', 'B', $fs);
	$this->pdf->pdf()->Cell($wx['duration_w'], $pt, $duration[$j], 'B', 0, "R");
	$this->pdf->pdf()->SetFont('Hack', 'I', $fs);
	$this->pdf->pdf()->Cell($wx['lingos_w'], $pt, $lingos[$j], 'B', 0, "L");
	$this->pdf->pdf()->SetFont('Arial', '', $fs);
	$this->pdf->pdf()->Cell($wx['disc_w'], $pt, $disc[$j], 'B', 1, "L");
      }
      
      $this->pdf->pdf()->SetTextColor(0, 0, 0);
    }
  
    $this->pdf->pdf()->Output();
  }
  
}

?>