<?php

require 'filterdrop_disc.php';
require 'filterdrop_lang.php';

require_once 'mysql_base.php';
require_once 'irenderable.php';

final class Movies implements IRenderable {

  private $par;
  private $con;
  private $order;
  private $category;
  private $limit_to;
  private $limit_from;
  
  private $id_order = "";
  private $du_order = "";
  private $di_order = "";
  private $ti_order = "";
  
  private static $dvd_choice = <<<'EOD'
    SELECT `m`.`ID`, MAKE_MOVIE_TITLE(`m`.`title`, `m`.`comment`, `s`.`name`, `es`.`episode`, `s`.`prepend`) AS `ltitle`, 
    SEC_TO_TIME(m.duration) AS `duration`, `m`.`duration` AS `dur_sec`, IF(`languages`.`name` IS NOT NULL, TRIM(GROUP_CONCAT(`languages`.`name` 
    ORDER BY `movie_languages`.`lang_id` DESC SEPARATOR ', ')), 'n. V.') as `lingos`, `disc`.`name` AS `disc`,`category`,
    `m`.`filename` AS `filename` FROM `disc` AS `disc`, `movies` AS `m` LEFT JOIN `episode_series` AS `es` ON  `m`.`ID` =`es`.`movie_id` 
    LEFT JOIN`series`AS `s` ON `s`.`id` = `es`.`series_id` LEFT JOIN `movie_languages` ON `m`.`ID` = `movie_languages`.`movie_id` 
    LEFT JOIN `languages` ON `movie_languages`.`lang_id` = `languages`.`id`  WHERE `disc`.`ID` = `m`.`disc` 
EOD;

  function __construct($order_by = "ltitle", $from = 0, $to = -1, $cat = -1) {
    
    $this->con = MySQLBase::instance()->con();
    
    if($order_by === "ID") {
      $this->order = "`m`.`ID`";
      $this->id_order = "&nbsp;&#10037;";
    } else if($order_by === "duration") {
      $this->order = "`dur_sec` DESC, MAKE_MOVIE_SORTKEY(`ltitle`, `m`.`skey`)";
      $this->du_order = "&nbsp;&#10037;";
    } else if($order_by === "disc") {
      $this->order = "LEFT( `disc`.`name`, 1 ) ASC, LENGTH( `disc`.`name` ) ASC, `disc`.`name` ASC, MAKE_MOVIE_SORTKEY(`ltitle`, `m`.`skey`)";
      $this->di_order = "&nbsp;&#10037;";
    } else {
      $this->order = "MAKE_MOVIE_SORTKEY(`ltitle`, `m`.`skey`)";
      $this->ti_order = "&nbsp;&#10037;";
    }
    
    $this->limit_from = $to == -1 ? $from : min($from, $to);
    $this->limit_to   = $to == -1 ? $to : max($from, $to);
    $this->category   = $cat;
    $this->par = 1;

  }
  
  public function category() {
    return $this->category;
  }
      
  private function order() {
  
    if($this->id_order <> "") {
      return "ID";
    } else if($this->du_order <> "") {
      return "duration";
    } else if($this->di_order <> "") {
      return "disc";
    } 
  
    return "ltitle";
  }
  
  private function filters() {
  
    $ret = "";
    
    if(isset($_GET['filter_ID']) && !empty($_GET['filter_ID'])) {
      $ret .= "&filter_ID=".$_GET['filter_ID'];
    }
    
    if(isset($_GET['filter_ltitle']) && !empty($_GET['filter_ltitle'])) {
      $ret .= "&filter_ltitle=".$_GET['filter_ltitle'];
    }
    
    if(isset($_GET['filter_lingo']) && !empty($_GET['filter_lingo'])) {
      $ret .= "&filter_lingo=".$_GET['filter_lingo'];
    }
    
    if(isset($_GET['filter_lingo_not']) && $_GET['filter_lingo_not'] == "on") {
      $ret .= "&filter_lingo_not=".$_GET['filter_lingo_not'];
    }
    
    if(isset($_GET['filter_disc']) && !empty($_GET['filter_disc'])) {
      $ret .= "&filter_disc=".$_GET['filter_disc'];
    }
    
    return $ret;
    
  }
  
  public function catQueryString($cat) {
    return $this->createQueryString(false, true, true, false)."&from=0&to=".$this->pageSize()."&cat=".$cat;
  }
  
  public function discQueryString($disc) {
    return $this->createQueryString(false, true, false, false)."&from=0&to=-1&filter_disc=".$disc;
  }
    
  private function createQueryString($cat, $order, $filter, $limits, $qm = true) {
    return ($qm ? "?" : "").($cat ? "&cat=".$this->category : "").
      ($order   ? "&order_by=".$this->order() : "").
      ($filter  ? $this->filters() : "").
      ($limits  ? "&from=".$this->limit_from."&to=".$this->limit_to : "");
  }
  
  private function renderRow($id = "", $ltitle = "", $duration = "", $dursec = 0, $lingos = "", $disc = "", $fname = "", $cat = 1, $isSummary = false) {
    echo "<tr class=\"parity_".($this->par % 2)."\"><td nowrap class=\"list hack\" align=\"right\">".
      ($id === "" ? "&nbsp;" : htmlentities($id, ENT_SUBSTITUTE, "utf-8"))."</td><td nowrap class=\"list hasTooltip cat_".
      $cat.($isSummary ? "" : " ltitle")."\">".($ltitle === "" ? "&nbsp;" : htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8").
      "<span>".htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8"))."</span></td><td nowrap align=\"right\" class=\"list ".
      ($dursec != 0 ? "hasTooltip" : "")." duration cat_".$cat."\">".
      ($duration === "" ? "&nbsp;" : htmlentities($duration, ENT_SUBSTITUTE, "utf-8")).
      ($dursec != 0 ? "<span>&asymp;".htmlentities(round($dursec/60), ENT_SUBSTITUTE, "utf-8")." Minuten</span>" : "").
      "</td><td nowrap class=\"list cat_".$cat." hack lingos\">".($lingos === "" ? "&nbsp;" : htmlentities($lingos, ENT_SUBSTITUTE, "utf-8")).
      "</td><td nowrap class=\"list ".(empty($fname) ? "" : "hasTooltip")." cat_".$cat."\">".($disc === "" ? "&nbsp;" : 
      (htmlentities($disc, ENT_SUBSTITUTE, "utf-8")."<span>".htmlentities($fname, ENT_SUBSTITUTE, "utf-8"))).(empty($fname) ? "" : "</span>")."</td></tr>\n";
      
    $this->par++;
  }
  
  public function render() {

    $i = 0;
  
    echo "<form method=\"GET\"><table class=\"list\" border=\"0\">\n";
    echo "<input type=\"hidden\" name=\"order_by\" value=\"".$this->order()."\" />".
      "<input type=\"hidden\" name=\"cat\" value=\"".$this->category()."\" />".
      "<input type=\"hidden\" name=\"from\" value=\"0\" />".
      "<input type=\"hidden\" name=\"to\" value=\"-1\" />\n";
    
    $like = " LIKE ".(isset($_GET['filter_ltitle']) ? " CONCAT('%', '".$this->con->real_escape_string(urldecode($_GET['filter_ltitle']))."', '%')" : "'%'");
    $tfil = (isset($_GET['filter_ltitle']) && !empty($_GET['filter_ltitle']) ? " AND (`m`.`title` ".$like." OR `m`.`comment` ".$like." OR `s`.`name` ".
      $like." OR `es`.`episode` ".$like.") " : "");
    $ifil = (isset($_GET['filter_ID']) && is_numeric($_GET['filter_ID']) ? " AND `m`.`ID` = ".$_GET['filter_ID'] : "");
    $dfil = (isset($_GET['filter_disc']) && is_numeric($_GET['filter_disc']) && $_GET['filter_disc'] != -1 ? " AND `m`.`disc` = ".$_GET['filter_disc'] : "");
    $lfil = (isset($_GET['filter_lingo']) && !empty($_GET['filter_lingo']) ? " AND '".$this->con->real_escape_string(urldecode($_GET['filter_lingo']))."' ".
      (isset($_GET['filter_lingo_not']) && $_GET['filter_lingo_not'] == "on" ? "NOT " : "").
      "IN (SELECT `movie_languages`.`lang_id` FROM `movie_languages` WHERE `movie_languages`.`movie_id` = `m`.`id`)" : "");
    
    $bq = self::$dvd_choice.($this->category == -1 ? "" : " AND `category` = ".$this->category).
      $tfil.$ifil.$dfil.$lfil.
      " GROUP BY `m`.`ID` ORDER BY ".$this->order;
    
    $result = $this->con->query($bq);

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
	"<td class=\"list_filter\"><input name=\"filter_ID\" class=\"list_filter\" id=\"list_filter_id\" size=\"3\" type=\"text\" ".
	"onkeydown=\"if (event.keyCode == 13) { this.form.submit(); return false; }\"".
	"value=\"".(isset($_GET['filter_ID']) && is_numeric($_GET['filter_ID']) ? urldecode($_GET['filter_ID']) : "")."\"></td>".
	"<td class=\"list_filter\" ><input name=\"filter_ltitle\" class=\"list_filter\" id=\"list_filter_ltitle\" type=\"text\" ".
	"onkeydown=\"if (event.keyCode == 13) { this.form.submit(); return false; }\" value=\"".
	(isset($_GET['filter_ltitle']) ? urldecode($_GET['filter_ltitle']) : "")."\"></td>".
	"<!-- <td class=\"list_filter\"><input readonly disabled class=\"list_filter\" id=\"list_filter_duration\" type=\"text\"></td> -->".
	"<td class=\"list_filter\">&nbsp;</td>".
	"<td nowrap class=\"list_filter\">".(new FilterdropLang())->render(isset($_GET['filter_lingo']) ? $_GET['filter_lingo'] : "",
	  isset($_GET['filter_lingo_not']) && $_GET['filter_lingo_not'] == "on")."</td>".
	"<td class=\"list_filter\">".(new FilterdropDisc())->render(isset($_GET['filter_disc']) ? $_GET['filter_disc'] : -1)."</td></tr>\n";
      
      while ($row = $result->fetch_assoc()) {

        if($i >= $this->limit_from && ($this->limit_to == -1 || $i <= $this->limit_to)) {
	  $this->renderRow($row['ID'], $row['ltitle'], $row['duration'], $row['dur_sec'], $row['lingos'], $row['disc'], $row['filename'], $row['category']);
	}

	$i++;
      }

      $this->renderRow();

      $total_res = $this->con->query("SELECT CONCAT( IF( FLOOR( SUM( `dur_sec` ) / 3600 ) <= 99, ".
	"RIGHT( CONCAT( '00', FLOOR( SUM( `dur_sec` ) / 3600 ) ), 2 ), FLOOR( SUM( `dur_sec` ) / 3600 ) ), ':', ".
	"RIGHT( CONCAT( '00', FLOOR( MOD( SUM( `dur_sec` ), 3600 ) / 60 ) ), 2 ), ':', ".
	"RIGHT( CONCAT( '00', MOD( SUM( `dur_sec` ), 60 ) ), 2 ) ) AS `tot_dur` FROM (".self::$dvd_choice.
	  ($this->category == -1 ? "" : "AND `category` = ".$this->category).$tfil.$ifil.$dfil.$lfil." GROUP BY `m`.`ID`) AS `choice`");

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
    
    //echo "<pre>".$bq."</pre>\n";
  }
  
  static public function pageSize() {
    return 24;
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
