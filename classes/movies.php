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

require 'form/formabletraits.php';
require 'table/headercell.php';
require 'filterdrop_disc.php';
require 'filterdrop_lang.php';
require 'form/iformable.php';
require 'user_actions.php';
require 'movies_base.php';
require 'pagination.php';

final class Movies extends MoviesBase implements IFormable {

  use FormableTraits;

  private $par;
  private $loggedIn = false;

  function __construct($order_by = "ltitle", $from = 0, $to = -1, $cat = -1) {

    parent::__construct($order_by, $from, $to, $cat);

    $this->par = 1;
    $this->loggedIn = isset($_SESSION['ui']);
  }

  public function hidden() {
    return array(
      'order_by' => $this->order(),
      'cat' => $this->category(),
      'from' => 0,
      'to' => $this->pageSize()
      );
  }

  public function action() {
    return null;
  }

  private function renderRow($id = "", $ltitle = "", $st = "", $duration = "", $dursec = 0, $lingos = "", $disc = "", $fname = "", $cat = 1, $isSummary = false) {

    if(empty($id) && empty($ltitle) && empty($st) && empty($duration) && empty($lingos) && empty($disc) && empty($fname)) {
      $isSummary = true;
    }

    if(!$isSummary) {
      $nid = $this->makeLZID($id);
    } else {
      $nid = $id;
    }

    $atts = array('class' => "parity_".($this->par % 2));
    $tatt = array('align' => "left", 'class' => "list ".($isSummary ? "" : "hasTooltip")." cat_".$cat.($isSummary ? "" : " ltitle"));

    if(!$isSummary) {
      $atts['itemscope'] = null;
      $atts['itemtype']  = "http://schema.org/MediaObject";
      $tatt['nowrap']    = null;
    }

    $this->addRow(new Row(
      $atts,
      array(
	new Cell(array('nowrap' => null, 'class' => "list hack", 'align' => "right"),
	  ($id === "" ? "&nbsp;" : ($isSummary || !$this->loggedIn ? "" : "<a href=\"#openModal_".$id."\">").
	  htmlentities($nid, ENT_SUBSTITUTE, "utf-8").($isSummary || !$this->loggedIn ? "" : "</a><div id=\"openModal_".$id."\" class=\"modalDialog\">".
	  "<div><a href=\"#close\" title=\"Schlie&szlig;en\" class=\"close\">X</a><div class=\"ua cat_".$cat."\">".
	  htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8")."</div>".(new UserActions($_SESSION['ui'], $id))->render()."</div>")).
	  ($isSummary || !$this->loggedIn ? "" : "</div>")),
	new Cell($tatt,
	  ($this->loggedIn && !$isSummary ? "<a target=\"_blank\" href=\"omdb.php?search=".urlencode($st)."&amp;q=".
	  urlencode($_SERVER['QUERY_STRING'])."\">" : "").
	  ($ltitle === "" ? "&nbsp;" : htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8").($this->loggedIn  && !$isSummary ? "</a>" : "").
	  ($isSummary ? "" : "<span itemprop=\"name\">".htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8")."</span>"))),
	new Cell(array('nowrap' => null, 'align' => "right", 'class' => "list ".($dursec != 0 ? "hasTooltip" : "")." duration cat_".$cat),
	  ($duration === "" ? "&nbsp;" : ($dursec != 0 ? "<span>&asymp;".htmlentities(round($dursec/60), ENT_SUBSTITUTE, "utf-8")." Minuten</span>" : "").
	  ($isSummary ? "" : "<div itemprop=\"duration\" content=\"".(new DateTime($duration))->format('\P\TG\Hi\Ms\S')."\"").($isSummary ? "" :">").
	  htmlentities($duration, ENT_SUBSTITUTE, "utf-8")).($isSummary ? "" : "</div>")),
	new Cell(array('nowrap' => null, 'class' => "list cat_".$cat." hack lingos"),
	  ($lingos === "" ? "&nbsp;" : htmlentities($lingos, ENT_SUBSTITUTE, "utf-8"))),
	new Cell(array('nowrap' => null, 'align' => "left", 'class' => "list hasTooltip cat_".$cat),
	  ($disc === "" ? "&nbsp;" : (htmlentities($disc, ENT_SUBSTITUTE, "utf-8")."<span>".
	  htmlentities(empty($fname) ? "Video-DVD" : $fname, ENT_SUBSTITUTE, "utf-8"))."</span>")))
    ));

    $this->par++;
  }

  public final function render() {

    $i = 0;

    $result = $this->mySQLRowsQuery();
    $hasRes = !is_null($result);

    $act_id = ($this->id_order === "");
    $act_ti = ($this->ti_order === "");
    $act_du = ($this->du_order === "");
    $act_di = ($this->di_order === "");

    $this->addRow(new Row(
      array('id' => "list_topbot"),
      array(
	new HeaderCell(array('class' => "min_th hack"),
	  ($act_id ? "<a class=\"list\" href=\"?order_by=ID".$this->createQueryString(true, false, true, true, false)."\">" : "").
	  "Nr".$this->id_order.($act_id ? "</a>" : "")),
	new HeaderCell(array('class' => "max_th ltitle"),
	  ($act_ti ? "<a class=\"list\" href=\"?order_by=title".
	  $this->createQueryString(true, false, true, true, false)."\">" : "")."Titel".$this->ti_order.($act_ti ? "</a>" : "")),
	new HeaderCell(array('class' => "min_th duration"),
	  ($act_du ? "<a class=\"list\" href=\"?order_by=duration".$this->createQueryString(true, false, true, true, false).
	  "\">" : "")."L&auml;nge".$this->du_order.($act_du ? "</a>" : "")),
	new HeaderCell(array('class' => "min_th hack lingos"),
	  "Sprache(n)"),
	new HeaderCell(array(),
	  ($act_di ? "<a class=\"min_th list\" href=\"?order_by=disc".$this->createQueryString(true, false, true, true, false)."\">" : "").
	  "DVD".$this->di_order.($act_di ? "</a>" : "")))
    ));

    $this->addRow(new Row(
      array('class' => "list_filter"),
      array(
	new Cell(array('title' => "Durch Kommata getrennte Liste von Nummern, die an das Ergebnis angef&uuml;gt werden sollen",
		       'class' => "list_filter"),
	  "<input name=\"filter_ID\" class=\"list_filter\" id=\"list_filter_id\" size=\"3\" type=\"text\" ".
	  "value=\"".($this->filters['filter_ID'][0] ? $this->filters['filter_ID'][1] : "")."\">"),
	new Cell(array('title' => "/REGEXP/ erm&ouml;glicht Filterung mit regul&auml;ren Ausdr&uuml;cken.",
		       'class' => "list_filter"),
	  "<input name=\"filter_ltitle\" class=\"list_filter\" placeholder=\"Suchbegriff(e) oder /regul&auml;rer Ausdruck/\" ".
	  "id=\"list_filter_ltitle\" type=\"text\" onkeydown=\"if (event.keyCode == 13) { this.form.submit(); return false; }\" ".
	  "onfocus=\"var temp_value=this.value; this.value=''; this.value=temp_value\" value=\"".
	  ($this->filters['filter_ltitle'][0] ? $this->filters['filter_ltitle'][1] : "")."\">"),
	new Cell(array('class' => "list_filter")),
	new Cell(array('nowrap' => null, 'class' => "list_filter lingos"),
	  (new FilterdropLang())->render($this->filters['filter_lingo'][0] ?
	  $this->filters['filter_lingo'][1] : "".$this->filters['filter_lingo_not'][0])),
	new Cell(array('class' => "list_filter"),
	  (new FilterdropDisc())->render($this->filters['filter_disc'][0] ? $this->filters['filter_disc'][1] : -1)))
    ));

    if($result) {

	$fids = "";
	$tits = array();

	while($row = $result->fetch_assoc()) {

	  $fids  .= $row['ID'].",";
	  $tits[] = preg_replace("/\\\"/", "&quot;", htmlentities($row['ltitle'], ENT_SUBSTITUTE, "utf-8"));

	  if($i >= $this->limit_from && ($this->limit_to == -1 || $i <= $this->limit_to)) {
	    $this->renderRow($row['ID'], $row['ltitle'], $row['st'], $row['duration'], $row['dur_sec'], $row['lingos'], $row['disc'], $row['filename'], $row['category']);
	  }

	  $i++;
	}

	if(isset($_SESSION['ui'])) {
	  $_SESSION['ui']['fid'] = $this->isFiltered() ? substr($fids, 0, -1) : null;
	}

	$this->renderRow();

	$total_res = $this->mySQLTotalQuery();

	if($total_res) $total = $total_res->fetch_assoc();

	if($total_res && $total) {
	  $this->renderRow($result->num_rows, ($result->num_rows != 1 ? "Videos insgesamt" : "Video"), "",
	  $this->secondsToDHMS($total['tot_dur']), "0", "", "", "", 1, true);
	  $total_res->free_result();
	} else {
	  $this->renderRow(0, "MySQL-Fehler: ".MySQLBase::instance()->con()->error, "", "00:00:00", "0", "", "", 4, true);
	}

	$result->free_result();
    } else if(!empty(MySQLBase::instance()->con()->error)) {
      $this->renderRow(0, "MySQL-Fehler: ".MySQLBase::instance()->con()->error, "", "00:00:00", "0", "", "", "", 4, true);
    } else {
      $this->renderRow(0, "Videos gefunden, überprüfen Sie die Filter!", "", "00:00:00", "0", "", "", "", 1, true);
    }

    if($hasRes) {
      $this->addRow(new Row(
	array('id' => "list_topbot"),
	array(new Cell(array('align' => "center", 'valign' => "middle", 'colspan' => "5"),
	  (new Pagination($i, isset($tits) ? $tits : array(),
	    $this->createQueryString(true, true, true, false),
	    $this->pageSize() == -1 ? (preg_match("/Android.*Mobile/",
	      $_SERVER['HTTP_USER_AGENT']) ? MoviesBase::MOBILE_PAGESIZE : MoviesBase::STD_PAGESIZE) : $this->pageSize(),
	    $this->limit_from, $this->limit_to))->render()))
      ));

      if(isset($_SESSION['ui'])) {
	MySQLBase::instance()->update_fid($_SESSION['ui']['id'], $this->isFiltered() ? $_SESSION['ui']['fid'] : null);
      }
    }

    return parent::render()."<input type=\"submit\" id=\"filter_submit\">";
  }
}

?>
