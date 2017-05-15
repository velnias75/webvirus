<?php

require 'filterdrop_disc.php';
require 'filterdrop_lang.php';
require 'user_actions.php';
require 'movies_base.php';

final class Movies extends MoviesBase {

  private $par;
  private $loggedIn = false;

  function __construct($order_by = "ltitle", $from = 0, $to = -1, $cat = -1) {
    
    parent::__construct($order_by, $from, $to, $cat);
    
    $this->par = 1;
    $this->loggedIn = isset($_SESSION['ui']);
  }
  
  private function renderRow($id = "", $ltitle = "", $duration = "", $dursec = 0, $lingos = "", $disc = "", $fname = "", $cat = 1, $isSummary = false) {
    echo "<tr class=\"parity_".($this->par % 2)."\"><td nowrap class=\"list hack\" align=\"right\">".
      ($id === "" ? "&nbsp;" : ($isSummary || !$this->loggedIn ? "" : "<a href=\"#openModal_".$id."\">").htmlentities($id, ENT_SUBSTITUTE, "utf-8").
      ($isSummary || !$this->loggedIn ? "" : "</a><div id=\"openModal_".$id."\" class=\"modalDialog\"><div><a href=\"#close\" title=\"Schlie&szlig;en\" class=\"close\">X</a><div class=\"ua cat_".$cat."\">".htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8")."</div>".
      (new UserActions($_SESSION['ui'], $id))->render()."</div>"))."</td><td ".($isSummary ? "" : "nowrap")." align=\"left\" class=\"list ".
      ($isSummary ? "" : "hasTooltip")." cat_".$cat.($isSummary ? "" : " ltitle")."\">".($ltitle === "" ? "&nbsp;" : htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8").
      ($isSummary ? "" : "<span>".htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8"))."</span>")."</td><td nowrap align=\"right\" class=\"list ".
      ($dursec != 0 ? "hasTooltip" : "")." duration cat_".$cat."\">".($duration === "" ? "&nbsp;" : htmlentities($duration, ENT_SUBSTITUTE, "utf-8")).
      ($dursec != 0 ? "<span>&asymp;".htmlentities(round($dursec/60), ENT_SUBSTITUTE, "utf-8")." Minuten</span>" : "").
      "</td><td nowrap align=\"left\" class=\"list cat_".$cat." hack lingos\">".($lingos === "" ? "&nbsp;" : htmlentities($lingos, ENT_SUBSTITUTE, "utf-8")).
      "</td><td nowrap align=\"left\" class=\"list hasTooltip cat_".$cat."\">".($disc === "" ? "&nbsp;" : 
      (htmlentities($disc, ENT_SUBSTITUTE, "utf-8")."<span>".htmlentities(empty($fname) ? "Video-DVD" : $fname, ENT_SUBSTITUTE, "utf-8"))).
      "</span></td></tr>\n";
      
    $this->par++;
  }
  
  public final function render() {

    $i = 0;
  
    echo "<form method=\"GET\"><table class=\"list\" border=\"0\">\n";
    echo "<input type=\"hidden\" name=\"order_by\" value=\"".$this->order()."\" />".
      "<input type=\"hidden\" name=\"cat\" value=\"".$this->category()."\" />".
      "<input type=\"hidden\" name=\"from\" value=\"0\" />".
      "<input type=\"hidden\" name=\"to\" value=\"-1\" />\n";

    $result = $this->mySQLRowsQuery();

    if($result) {
      
      $act_id = ($this->id_order === "");
      $act_ti = ($this->ti_order === "");
      $act_du = ($this->du_order === "");
      $act_di = ($this->di_order === "");

      echo "<tr id=\"list_topbot\">".
	"<th class=\"min_th hack\">".($act_id ? "<a class=\"list\" href=\"?order_by=ID".$this->createQueryString(true, false, true, true, false)."\">" : "").
	"Nr".$this->id_order.($act_id ? "</a>" : "")."</th><th class=\"max_th ltitle\">".($act_ti ? "<a class=\"list\" href=\"?order_by=title".
	$this->createQueryString(true, false, true, true, false)."\">" : "")."Titel".$this->ti_order.($act_ti ? "</a>" : "").
	"</th><th class=\"min_th duration\">".($act_du ? "<a class=\"list\" href=\"?order_by=duration".$this->createQueryString(true, false, true, true, false).
	"\">" : "")."L&auml;nge".$this->du_order.($act_du ? "</a>" : "")."</th><th class=\"min_th hack lingos\">Sprache(n)</th><th>".
	($act_di ? "<a class=\"min_th list\" href=\"?order_by=disc".$this->createQueryString(true, false, true, true, false)."\">" : "").
	"DVD".$this->di_order.($act_di ? "</a>" : "")."</th></tr>\n";
	
      echo "<tr class=\"list_filter\">".
	"<td title=\"Durch Kommata getrennte Liste von Nummern, die an das Ergebnis angef&uuml;gt werden sollen\" ".
	"class=\"list_filter\"><input name=\"filter_ID\" class=\"list_filter\" id=\"list_filter_id\" size=\"3\" type=\"text\" ".
	"onkeydown=\"if (event.keyCode == 13) { this.form.submit(); return false; }\" ".
	"onfocus=\"var temp_value=this.value; this.value=''; this.value=temp_value\" value=\"".($this->filters['filter_ID'][0] ? 
	$this->filters['filter_ID'][2] : "")."\"></td><td title=\"/REGEXP/ erm&ouml;glicht Filterung mit regul&auml;ren Ausdr&uuml;cken.\" ".
	"class=\"list_filter\" ><input name=\"filter_ltitle\" class=\"list_filter\" ".
	"id=\"list_filter_ltitle\" type=\"text\" "."onkeydown=\"if (event.keyCode == 13) { this.form.submit(); return false; }\" ".
	"onfocus=\"var temp_value=this.value; this.value=''; this.value=temp_value\" value=\"".
	($this->filters['filter_ltitle'][0] ? $this->filters['filter_ltitle'][2] : "")."\"></td>".
	"<!-- <td class=\"list_filter\"><input readonly disabled class=\"list_filter\" id=\"list_filter_duration\" type=\"text\"></td> -->".
	"<td class=\"list_filter\">&nbsp;</td><td nowrap class=\"list_filter\">".(new FilterdropLang())->render($this->filters['filter_lingo'][0] ? 
	$this->filters['filter_lingo'][1] : "",$this->filters['filter_lingo_not'][0])."</td>".
	"<td class=\"list_filter\">".(new FilterdropDisc())->render($this->filters['filter_disc'][0] ? $this->filters['filter_disc'][1] : -1)."</td></tr>\n";
      
      while($row = $result->fetch_assoc()) {

        if($i >= $this->limit_from && ($this->limit_to == -1 || $i <= $this->limit_to)) {
	  $this->renderRow($row['ID'], $row['ltitle'], $row['duration'], $row['dur_sec'], $row['lingos'], $row['disc'], $row['filename'], $row['category']);
	}

	$i++;
      }

      $this->renderRow();

      $total_res = $this->mySQLTotalQuery();

      if($total_res) $total = $total_res->fetch_assoc();
      
      if($total_res && $total) {
	$this->renderRow($result->num_rows, ($result->num_rows != 1 ? "Videos insgesamt" : "Video"), $total['tot_dur'], "0", "", "", "", 1, true);
	$total_res->free_result();
      } else {
	$this->renderRow(0, "MySQL-Fehler: ".$this->con->error, "00:00:00", "0", "", "", 4, true);
      }

      $result->free_result();
      
    } else {
      $this->renderRow(0, "MySQL-Fehler: ".$this->con->error, "00:00:00", "0", "", "", "", 4, true);
    }
    
    echo "<tr id=\"list_topbot\"><td align=\"center\" valign=\"center\" colspan=\"5\">".$this->createPagination($i)."</td></tr>\n";
    echo "</table><input type=\"submit\" id=\"filter_submit\"></form>\n";
  }

  private function createAllPage() {
    return "<td class=\"page_nr".($this->limit_to == -1 ? " page_active" : "")."\">".
      ($this->limit_to == -1 ? "Alle" : "<a class=\"page_nr\" href=\"".
      $this->createQueryString(true, true, true, false)."&from=0&to=-1\">Alle</a>")."</td>";
  }
  
  private function createPagination($rows) {
    
    $psize = abs(($this->limit_to == -1 ? $this->pageSize() : abs($this->limit_to)) - abs($this->limit_from));
    $pages = ceil($rows/($psize + 1));
    
    $prev  = ($this->limit_from - $psize - 1) >= 0 ? $this->limit_from - $psize - 1 : ($pages - 1) * ($psize + 1);
    $next  = ($this->limit_from + $psize + 1) < $rows ? $this->limit_from + $psize + 1 : 0;
    
    $pagin = "<table width=\"100%\" border=\"0\"><tr align=\"center\">".$this->createAllPage().
      "<td width=\"".floor(100/($pages + 4))."%\" class=\"page_nr\"><a class=\"page_nr\" href=\"".
      $this->createQueryString(true, true, true, false)."&from=".$prev."&to=".($prev + $psize)."\">&#10525;</a></td>";
    
    for($i = 0; $i < $pages; $i++) {
      
      $from  = $i * ($psize + 1);
      $activ = $this->limit_to == -1 || !(abs($this->limit_from) >= $from && abs($this->limit_to) <= ($from + $psize));
      $pagin = $pagin."<td width=\"".floor(100/($pages + 4))."%\" class=\"page_nr".($activ ? "" : " page_active")."\">".
	($activ ? "<a class=\"page_nr\" href=\"".$this->createQueryString(true, true, true, false).
	"&from=".$from."&to=".($from + $psize)."\">" : "").($i + 1).($activ ? "</a>" : "")."</td>";
    }
    
    return $pagin."<td width=\"".floor(100/($pages + 4)).
      "%\" class=\"page_nr\"><a class=\"page_nr\" href=\"".$this->createQueryString(true, true, true, false).
      "&from=".$next."&to=".($next + $psize)."\">&#10526;</a></td>".$this->createAllPage()."</tr></table>";
  }
  
}

?>
