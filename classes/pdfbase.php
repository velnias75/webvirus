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

    $lm = $this->GetX();

    $this->SetY(-15);
    $this->SetFont('Arial', 'I', 6);
    $this->Cell(0, 10, "Dokument erstellt ".strftime("am %d.%m.%Y um %H:%M:%S")." via http://".$_SERVER['SERVER_NAME'].
      MySQLBase::getRequestURI()."/", "T", 0, "L", false,
    "http://".$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
    $this->SetFont('Courier', '', 10);
    $this->SetX($lm);
    $this->Cell(0, 10, 'Seite '.$this->PageNo(), "T", 0, "C");

  }

  public function latest() {
    return str_replace(".", "-", $this->latest);
  }

  public function margin() {
    return $this->margin;
  }

  public function pdf() {
    return $this;
  }
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
