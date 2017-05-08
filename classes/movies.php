<?php

require_once 'mysql_base.php';
require_once 'irenderable.php';

class Movies extends MySQLBase implements IRenderable {

  private $par;
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
    SEC_TO_TIME(m.duration) AS `duration`, `m`.`duration` AS `dur_sec`, IF(`languages`.`name` IS NOT NULL, TRIM(GROUP_CONCAT(`languages`.`name` ORDER BY 
    `movie_languages`.`lang_id` DESC SEPARATOR ', ')), 'n. V.') as `lingos`, `disc`.`name` AS `disc`,`category` FROM `disc` AS `disc`, `movies` AS `m` 
    LEFT JOIN `episode_series` AS `es` ON  `m`.`ID` =`es`.`movie_id` LEFT JOIN`series`AS `s` ON `s`.`id` = `es`.`series_id` LEFT JOIN `movie_languages` 
    ON `m`.`ID` = `movie_languages`.`movie_id` LEFT JOIN `languages` ON `movie_languages`.`lang_id` = `languages`.`id` WHERE `disc`.`ID` = `m`.`disc` 
EOD;

  function __construct($order_by = "ltitle", $from = 0, $to = -1, $cat = -1) {
    
    parent::__construct();
    
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
    
    $this->limit_from = $from;
    $this->limit_to = $to;
    $this->category = $cat;
    $this->par = 1;

  }
  
  private function renderRow($id = "", $ltitle = "", $duration = "", $lingos = "", $disc = "", $cat = 1, $isSummary = false) {
    echo "<tr class=\"parity_".($this->par++ % 2)."\"><td nowrap class=\"list hack\" align=\"right\">".
      ($id === "" ? "&nbsp;" : htmlentities($id, ENT_SUBSTITUTE, "utf-8"))."</td><td nowrap class=\"list cat_".$cat.($isSummary ? "" : " ltitle")."\">".
      ($ltitle === "" ? "&nbsp;" : htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8"))."</td><td nowrap align=\"right\" class=\"list duration cat_".$cat."\">".
      ($duration === "" ? "&nbsp;" : htmlentities($duration, ENT_SUBSTITUTE, "utf-8"))."</td><td nowrap class=\"list cat_".$cat." hack lingos\">".
      ($lingos === "" ? "&nbsp;" : htmlentities($lingos, ENT_SUBSTITUTE, "utf-8"))."</td><td nowrap class=\"list cat_".$cat."\">".
      ($disc === "" ? "&nbsp;" : htmlentities($disc, ENT_SUBSTITUTE, "utf-8"))."</td></tr>\n";
  }
  
  public function category() {
    return $this->category;
  }
  
  public function queryString($cat) {
    return $this->createOrderCatHref(true)."&from=0&to=24&cat=".$cat;
  }
  
  private function appendLimits() {
    return "&cat=".$this->category."&from=".$this->limit_from."&to=".$this->limit_to;
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
  
  private function createOrderCatHref($nocat = false) {
    $res = ($nocat ? "?" : "?cat=".$this->category).
      (isset($_GET['filter_ltitle']) ? "&filter_ltitle=".$_GET['filter_ltitle']."&" : "");

    return $res."order_by=".$this->order();
  }

  public function render() {

    $i = 0;
  
    echo "<form method=\"GET\"><table class=\"list\" border=\"0\">\n";
    echo "<input type=\"hidden\" name=\"order_by\" value=\"".$this->order()."\">".
      "<input type=\"hidden\" name=\"cat\" value=\"".$this->category()."\">".
      "<input type=\"hidden\" name=\"from\" value=\"".$this->limit_from."\">".
      "<input type=\"hidden\" name=\"to\" value=\"".$this->limit_to."\">\n";
    
    $like = "LIKE CONCAT('%', '".$this->con()->real_escape_string(urldecode($_GET['filter_ltitle']))."', '%')";
    $tfil = (isset($_GET['filter_ltitle']) ? " AND (`m`.`title` ".$like." OR `m`.`comment` ".$like." OR `s`.`name` ".$like." OR `es`.`episode` ".$like.") " : "");
    
    $result = $this->con()->query(self::$dvd_choice.($this->category == -1 ? "" : " AND `category` = ".$this->category).$tfil.
      " GROUP BY `m`.`ID` ORDER BY ".$this->order, MYSQLI_USE_RESULT);

    if($result) {
      
      $act_id = ($this->id_order === "");
      $act_ti = ($this->ti_order === "");
      $act_du = ($this->du_order === "");
      $act_di = ($this->di_order === "");

      echo "<tr id=\"list_topbot\">".
	"<th class=\"hack\">".($act_id ? "<a class=\"list\" href=\"?order_by=ID".$this->appendLimits()."\">" : "")."Nr".$this->id_order.($act_id ? "</a>" : "").
	"</th><th class=\"ltitle\">".($act_ti ? "<a class=\"list\" href=\"?order_by=title".$this->appendLimits()."\">" : "")."Titel".$this->ti_order.
	($act_ti ? "</a>" : "")."</th><th class=\"duration\">".($act_du ? "<a class=\"list\" href=\"?order_by=duration".
	$this->appendLimits()."\">" : "")."L&auml;nge".$this->du_order.($act_du ? "</a>" : "")."</th><th class=\"hack lingos\">Sprache(n)</th><th>".
	($act_di ? "<a class=\"list\" href=\"?order_by=disc".$this->appendLimits()."\">" : "")."DVD".$this->di_order.($act_di ? "</a>" : "")."</th></tr>\n";
	
      echo "<tr class=\"list_filter\">".
	"<td><input readonly disabled class=\"list_filter\" id=\"list_filter_id\" size=\"3\" type=\"text\"></td>".
	"<td><input name=\"filter_ltitle\" class=\"list_filter\" id=\"list_filter_ltitle\" type=\"text\" value=\"".
	(isset($_GET['filter_ltitle']) ? urldecode($_GET['filter_ltitle']) : "")."\"></td>".
	"<td><input readonly disabled class=\"list_filter\" id=\"list_filter_duration\" type=\"text\"></td>".
	"<td><input readonly disabled class=\"list_filter\" id=\"list_filter_lingo\" type=\"text\"></td>".
	"<td><input readonly disabled class=\"list_filter\" id=\"list_filter_disc\" type=\"text\"></td></tr>\n";
      
      while ($row = $result->fetch_assoc()) {

        if($i >= $this->limit_from && ($this->limit_to == -1 || $i <= $this->limit_to)) {
	  $this->renderRow($row['ID'], $row['ltitle'], $row['duration'], $row['lingos'], $row['disc'], $row['category']);
	}

	$i++;
      }

      $this->renderRow();

      $total_res = $this->con()->query("SELECT CONCAT( IF( FLOOR( SUM( `dur_sec` ) / 3600 ) <= 99, ".
	"RIGHT( CONCAT( '00', FLOOR( SUM( `dur_sec` ) / 3600 ) ), 2 ), FLOOR( SUM( `dur_sec` ) / 3600 ) ), ':', ".
	"RIGHT( CONCAT( '00', FLOOR( MOD( SUM( `dur_sec` ), 3600 ) / 60 ) ), 2 ), ':', ".
	"RIGHT( CONCAT( '00', MOD( SUM( `dur_sec` ), 60 ) ), 2 ) ) AS `tot_dur` FROM (".self::$dvd_choice.
	  ($this->category == -1 ? "" : "AND `category` = ".$this->category).$tfil." GROUP BY `m`.`ID`) AS `choice`");

      if($total_res) $total = $total_res->fetch_assoc();
      
      if($total_res && $total) {
	$this->renderRow($result->num_rows, ($result->num_rows != 1 ? "Videos insgesamt" : "Video"), $total['tot_dur'], "", "", 1, true);
	$total_res->free_result();
      } else {
	$this->renderRow(0, "MySQL-Fehler: ".$this->con()->error, "00:00:00", "", "", 4, true);
      }

      $result->free_result();
      
    } else {
      $this->renderRow(0, "MySQL-Fehler: ".$this->con()->error, "00:00:00", "", "", 4, true);
    }
    
    echo "<tr id=\"list_topbot\"><td align=\"center\" valign=\"center\" colspan=\"5\">".$this->createPagination($i)."</td></tr>\n";
    echo "</table><input type=\"submit\" id=\"filter_submit\"></form>\n";

  }
  
  private function createAllPage() {
    return "<td class=\"page_nr".($this->limit_to == -1 ? " page_active" : "")."\">".
      ($this->limit_to == -1 ? "Alle" : "<a class=\"page_nr\" href=\"".$this->createOrderCatHref()."&from=0&to=-1\">Alle</a>")."</td>";
  }
  
  private function createPagination($rows) {
    
    $psize = ($this->limit_to == -1 ? 24 : $this->limit_to) - $this->limit_from;
    $pages = ceil($rows/$psize);
    
    $prev  = ($this->limit_from - $psize) >= 0 ? $this->limit_from - $psize : (($pages - 1) * $psize);
    $next  = ($this->limit_from + $psize) < $rows ? $this->limit_from + $psize : 0;
    
    
    $pagin = "<table width=\"100%\" border=\"0\"><tr align=\"center\">".$this->createAllPage().
      "<td width=\"".floor(100/($pages + 4))."%\" class=\"page_nr\"><a class=\"page_nr\" href=\"".$this->createOrderCatHref().
	"&from=".$prev."&to=".($prev + $psize)."\">&#10525;</a></td>";
    
    
    for($i = 0; $i < $pages; $i++) {
      $from  = ($psize * $i);
      $activ = $this->limit_to == -1 || !($this->limit_from >= $from && $this->limit_to <= ($from + $psize));
      $pagin = $pagin."<td width=\"".floor(100/($pages + 4))."%\" class=\"page_nr".($activ ? "" : " page_active")."\">".
	($activ ? "<a class=\"page_nr\" href=\"".$this->createOrderCatHref().
	"&from=".$from."&to=".($from + $psize)."\">" : "").($i + 1).($activ ? "</a>" : "")."</td>";
    }
    
    return $pagin."<td width=\"".floor(100/($pages + 4)).
      "%\" class=\"page_nr\"><a class=\"page_nr\" href=\"".$this->createOrderCatHref()."&from=".$next."&to=".($next + $psize)."\">&#10526;</a></td>".
	$this->createAllPage()."</tr></table>";
  }
  
}

?>
