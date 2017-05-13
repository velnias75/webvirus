<?php

require 'fpdf/fpdf.php';

final class PDFBase extends FPDF {

  private $margin;
  private $latest;

  function __construct($latest) {
  
    parent::__construct("L", "mm", "A4");
    
    $this->latest = $latest;
    
    $this->setTitle("Heikos Schrott- & Rentnerfilme", true);
    $this->setAuthor("Heiko Schäfer", true);
    $this->setCreator("✋ Die Webvirenversion ✋", true);
    $this->setSubject("Liste der auf DVD gespeicherten Filme, Dokumentationen und Konzerten", true);
    $this->setKeywords("MP4, MKV, Filmliste, Spielfilme, Dokumentationen, Dokus, Konzerte", true);
    $this->SetAutoPageBreak(true, 15);
    $this->SetCompression(true);
    
    $this->AddFont('Hack', '',   'Hack-Regular.php');
    $this->AddFont('Hack', 'I',  'Hack-RegularOblique.php');
//     $this->AddFont('Hack', 'B',  'Hack-Bold.php');
//     $this->AddFont('Hack', 'BI', 'Hack-BoldOblique.php');
    
    $this->margin = $this->GetX();
  }
  
  function Header() {
    
    $d = "Stand: ".$this->latest;
    $this->SetFont('Arial', 'B', 10);
    $this->Cell($this->getPageWidth()/2, 6, "Heikos Schrott- & Rentnerfilme", "B", 0);
    $this->SetTextColor(37, 49, 0);
    $this->Cell(($this->getPageWidth()/2) - $this->GetStringWidth($d) + $this->margin, 6, $d, "B", 1, "R");
    $this->SetTextColor(0, 0, 0);
    $this->Ln();
  }
  
  function Footer() {
    $this->SetY(-15);
    $this->SetFont('Courier', '', 10);
    $this->Cell(0, 10, 'Seite '.$this->PageNo(), "T", 0, "C");
  }
  
  public function margin() {
    return $this->margin;
  }
  
  public function pdf() {
    return $this;
  }
}

?>
