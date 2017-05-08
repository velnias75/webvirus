<?php

class Movies {

  private $par;
  private $order;
  private $mysqli;
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
    GROUP BY `m`.`ID`
EOD;

  function __construct($order_by = "ltitle", $from = 0, $to = -1) {
    
    require 'db_cred.php';
    
    $this->mysqli = new mysqli($server, $user, $pass, $db);
    
    if($this->mysqli->connect_errno) {      
      throw new ErrorException("Konnte keine Verbindung zu MySQL aufbauen: ".$this->mysqli->connect_error());
    }
    
    $this->mysqli->set_charset('utf8');
    
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
    $this->par = 1;

  }
  
  function __destruct() {
    $this->mysqli->close();
  }
  
  private function renderRow($id = "", $ltitle = "", $duration = "", $lingos = "", $disc = "", $cat = 1, $isSummary = false) {
    echo "<tr class=\"parity_".($this->par++ % 2)."\"><td nowrap class=\"list hack\" align=\"right\">".
      ($id === "" ? "&nbsp;" : htmlentities($id, ENT_SUBSTITUTE, "utf-8"))."</td><td nowrap class=\"list cat_".$cat.($isSummary ? "" : " ltitle")."\">".
      ($ltitle === "" ? "&nbsp;" : htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8"))."</td><td nowrap align=\"right\" class=\"list duration cat_".$cat."\">".
      ($duration === "" ? "&nbsp;" : htmlentities($duration, ENT_SUBSTITUTE, "utf-8"))."</td><td nowrap class=\"list cat_".$cat." hack lingos\">".
      ($lingos === "" ? "&nbsp;" : htmlentities($lingos, ENT_SUBSTITUTE, "utf-8"))."</td><td nowrap class=\"list cat_".$cat."\">".
      ($disc === "" ? "&nbsp;" : htmlentities($disc, ENT_SUBSTITUTE, "utf-8"))."</td></tr>\n";
  }
  
  private function appendLimits() {
    return "&from=".$this->limit_from."&to=".$this->limit_to;
  }
  
  private function createOrderHref() {
  
    if($this->id_order <> "") {
      return "?order_by=ID";
    } else if($this->du_order <> "") {
      return "?order_by=duration";
    } else if($this->di_order <> "") {
      return "?order_by=disc";
    } 
  
    return "?order_by=ltitle";
  }

  public function render() {

    $i = 0;
  
    echo "<table class=\"list\" border=\"0\">\n";
  
    $result = $this->mysqli->query(self::$dvd_choice." ORDER BY ".$this->order, MYSQLI_USE_RESULT);

    if($result) {
      
        $act_id = ($this->id_order === "");
        $act_ti = ($this->ti_order === "");
        $act_du = ($this->du_order === "");
        $act_di = ($this->di_order === "");
      
	echo "<tr id=\"list_topbot\">
	  <th class=\"hack\">".($act_id ? "<a class=\"list\" href=\"?order_by=ID".$this->appendLimits()."\">" : "")."Nr".$this->id_order.($act_id ? "</a>" : "")."</th>
	  <th class=\"ltitle\">".($act_ti ? "<a class=\"list\" href=\"?order_by=title".$this->appendLimits()."\">" : "")."Titel".$this->ti_order.($act_ti ? "</a>" : "")."</th>
	  <th class=\"duration\">".($act_du ? "<a class=\"list\" href=\"?order_by=duration".$this->appendLimits()."\">" : "")."L&auml;nge".$this->du_order.($act_du ? "</a>" : "")."</th>
	  <th class=\"hack lingos\">Sprache(n)</th>
	  <th>".($act_di ? "<a class=\"list\" href=\"?order_by=disc".$this->appendLimits()."\">" : "")."DVD".$this->di_order.($act_di ? "</a>" : "")."</th>
	</tr>\n";
      
      while ($row = $result->fetch_assoc()) {
        if($i >= $this->limit_from && ($this->limit_to == -1 || $i <= $this->limit_to)) {
	  $this->renderRow($row['ID'], $row['ltitle'], $row['duration'], $row['lingos'], $row['disc'], $row['category']);
	}
	$i++;
      }

      $this->renderRow();

      $total_res = $this->mysqli->query("SELECT CONCAT( IF( FLOOR( SUM( `dur_sec` ) / 3600 ) <= 99, ".
	"RIGHT( CONCAT( '00', FLOOR( SUM( `dur_sec` ) / 3600 ) ), 2 ), FLOOR( SUM( `dur_sec` ) / 3600 ) ), ':', ".
	"RIGHT( CONCAT( '00', FLOOR( MOD( SUM( `dur_sec` ), 3600 ) / 60 ) ), 2 ), ':', ".
	"RIGHT( CONCAT( '00', MOD( SUM( `dur_sec` ), 60 ) ), 2 ) ) AS `tot_dur` FROM (".self::$dvd_choice.") AS `choice`");

      if($total_res) $total = $total_res->fetch_assoc();
      
      if($total_res && $total) {
	$this->renderRow($result->num_rows, "Videos insgesamt", $total['tot_dur'], "", "", 1, true);
	$total_res->free_result();
      } else {
	$this->renderRow(0, "MySQL-Fehler: ".$this->mysqli->error, "00:00:00", "", "", 4, true);
      }

      $result->free_result();
      
    } else {
      $this->renderRow(0, "MySQL-Fehler: ".$this->mysqli->error, "00:00:00", "", "", 4);
    }
    
    echo "<tr id=\"list_topbot\"><td align=\"center\" valign=\"center\" colspan=\"5\">".$this->createPagination($i)."</td></tr>\n";
    
    echo "</table>\n";

  }
  
  private function createAllPage() {
    return "<td class=\"page_nr".($this->limit_to == -1 ? " page_active" : "")."\">".
      ($this->limit_to == -1 ? "Alle" : "<a class=\"page_nr\" href=\"".$this->createOrderHref()."&from=0&to=-1\">Alle</a>")."</td>";
  }
  
  private function createPagination($rows) {
    
    $psize = ($this->limit_to == -1 ? 24 : $this->limit_to) - $this->limit_from;
    $pages = ceil($rows/$psize);
    
    $prev  = ($this->limit_from - $psize) >= 0 ? $this->limit_from - $psize : (($pages - 1) * $psize);
    $next  = ($this->limit_from + $psize) < $rows ? $this->limit_from + $psize : 0;
    
    
    $pagin = "<table width=\"100%\" border=\"0\"><tr align=\"center\">".$this->createAllPage().
      "<td width=\"".floor(100/($pages + 4))."%\" class=\"page_nr\"><a class=\"page_nr\" href=\"".$this->createOrderHref().
	"&from=".$prev."&to=".($prev + $psize)."\">&#10525;</a></td>";
    
    
    for($i = 0; $i < $pages; $i++) {
      $from  = ($psize * $i);
      $activ = $this->limit_to == -1 || !($this->limit_from >= $from && $this->limit_to <= ($from + $psize));
      $pagin = $pagin."<td width=\"".floor(100/($pages + 4))."%\" class=\"page_nr".($activ ? "" : " page_active")."\">".
	($activ ? "<a class=\"page_nr\" href=\"".$this->createOrderHref().
	"&from=".$from."&to=".($from + $psize)."\">" : "").($i + 1).($activ ? "</a>" : "")."</td>";
    }
    
    return $pagin."<td width=\"".floor(100/($pages + 4)).
      "%\" class=\"page_nr\"><a class=\"page_nr\" href=\"".$this->createOrderHref()."&from=".$next."&to=".($next + $psize)."\">&#10526;</a></td>".
	$this->createAllPage()."</tr></table>";
  }
  
}

?>
