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

require_once 'irenderable.php';
require_once 'mysql_base.php';

abstract class MoviesBase implements IRenderable {

  private $con;
  private $order;
  private $latest;
  private $category;
  private $filtered = false;

  protected $limit_to;
  protected $limit_from;

  protected $id_order = "";
  protected $du_order = "";
  protected $di_order = "";
  protected $ti_order = "";

  private static $dvd_choice = <<<'EOD'
    SELECT `m`.`ID`, MAKE_MOVIE_TITLE(`m`.`title`, `m`.`comment`, `s`.`name`, `es`.`episode`, `s`.`prepend`) AS `ltitle`, `m`.`title` AS `st`,
    SEC_TO_TIME(m.duration) AS `duration`, `m`.`duration` AS `dur_sec`, IF(`languages`.`name` IS NOT NULL, TRIM(GROUP_CONCAT(`languages`.`name`
    ORDER BY `movie_languages`.`lang_id` DESC SEPARATOR ', ')), 'n. V.') as `lingos`, `disc`.`name` AS `disc`, `disc`.`name` AS `ddisc`, `category`,
    `m`.`filename` AS `filename`, MAKE_MOVIE_SORTKEY(MAKE_MOVIE_TITLE(`m`.`title`, `m`.`comment`, `s`.`name`,`es`.`episode`, `s`.`prepend`), `m`.`skey`) AS `msk`,
    `m`.`ID` as `mid` FROM `disc` AS `disc`, `movies` AS `m` LEFT JOIN `episode_series` AS `es` ON  `m`.`ID` =`es`.`movie_id`
    LEFT JOIN`series`AS `s` ON `s`.`id` = `es`.`series_id` LEFT JOIN `movie_languages` ON `m`.`ID` = `movie_languages`.`movie_id`
    LEFT JOIN `languages` ON `movie_languages`.`lang_id` = `languages`.`id` WHERE `disc`.`ID` = `m`.`disc`
EOD;

  protected $filters = array();

  function __construct($order_by = "ltitle", $from = 0, $to = -1, $cat = -1) {

    $this->con = MySQLBase::instance()->con();

    if($order_by === "ID") {
      $this->order = "`mid`";
      $this->id_order = "&nbsp;&#10037;";
    } else if($order_by === "duration") {
      $this->order = "`dur_sec` DESC, `msk` ";
      $this->du_order = "&nbsp;&#10037;";
    } else if($order_by === "disc") {
      $this->order = "LEFT( `ddisc`, 1 ) ASC, LENGTH( `ddisc` ) ASC, `ddisc` ASC, `msk`";
      $this->di_order = "&nbsp;&#10037;";
    } else {
      $this->order = " `msk` ";
      $this->ti_order = "&nbsp;&#10037;";
    }

    $this->limit_from = $to == -1 ? $from : min($from, $to);
    $this->limit_to   = $to == -1 ? $to : max($from, $to);
    $this->category   = $cat;

    $this->filters['filter_ID'] = array(isset($_GET['filter_ID']) && !empty($_GET['filter_ID']),
      isset($_GET['filter_ID']) ? $_GET['filter_ID'] : 0,
      isset($_GET['filter_ID']) ? urldecode($_GET['filter_ID']) : 0);
    $this->filters['filter_ltitle'] = array(isset($_GET['filter_ltitle']) && !empty($_GET['filter_ltitle']),
      isset($_GET['filter_ltitle']) ? $_GET['filter_ltitle'] : "",
      isset($_GET['filter_ltitle']) ? urldecode($_GET['filter_ltitle']) : "");
    $this->filters['filter_lingo'] = array(isset($_GET['filter_lingo']) && !empty($_GET['filter_lingo']),
      isset($_GET['filter_lingo']) ? $_GET['filter_lingo'] : "",
      isset($_GET['filter_lingo']) ? urldecode($_GET['filter_lingo']) : "");
    $this->filters['filter_lingo_not'] = array(isset($_GET['filter_lingo_not']) && $_GET['filter_lingo_not'] == "on",
      isset($_GET['filter_lingo_not']) ? $_GET['filter_lingo_not'] : "",
      isset($_GET['filter_lingo_not']) ? urldecode($_GET['filter_lingo_not']) : "");
    $this->filters['filter_disc'] = array(isset($_GET['filter_disc']) && is_numeric($_GET['filter_disc']) && $_GET['filter_disc'] != -1,
      isset($_GET['filter_disc']) ? $_GET['filter_disc'] : -1,
      isset($_GET['filter_disc']) ? urldecode($_GET['filter_disc']) : -1);

    $latest_res = $this->con->query("SELECT `id`, `name`, DATE_FORMAT(`created`, '%d.%m.%Y') AS `df` ".
      "FROM `disc` ORDER BY `created` DESC LIMIT 1");
    $this->latest = $latest_res->fetch_assoc()['df'];
    $latest_res->free_result();

    if($this->filters['filter_ID'][0] ||
      $this->filters['filter_ltitle'][0] ||
      $this->filters['filter_lingo'][0] ||
      $this->filters['filter_disc'][0]) $this->filtered = true;
  }

  protected final function isFiltered() {
    return $this->filtered;
  }

  protected final function latest() {
    return $this->latest;
  }

  public final function category() {
    return $this->category;
  }

  protected final function order() {

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

    if($this->filters['filter_ID'][0]) {
      $ret .= "&filter_ID=".urlencode($this->filters['filter_ID'][1]);
    }

    if($this->filters['filter_ltitle'][0]) {
      $ret .= "&filter_ltitle=".urlencode($this->filters['filter_ltitle'][1]);
    }

    if($this->filters['filter_lingo'][0]) {
      $ret .= "&filter_lingo=".urlencode($this->filters['filter_lingo'][1]);
    }

    if($this->filters['filter_lingo_not'][0]) {
      $ret .= "&filter_lingo_not=".urlencode($this->filters['filter_lingo_not'][1]);
    }

    if($this->filters['filter_disc'][0]) {
      $ret .= "&filter_disc=".urlencode($this->filters['filter_disc'][1]);
    }

    return $ret;

  }

  public final function fullQueryString() {
    return $this->createQueryString(true, true, true, true);
  }

  public final function catQueryString($cat) {
    return $this->createQueryString(false, true, true, false)."&from=0&to=".urlencode($this->pageSize())."&cat=".urlencode($cat);
  }

  public final function discQueryString($disc) {
    return $this->createQueryString(false, true, false, false)."&from=0&to=-1&filter_disc=".urlencode($disc);
  }

  protected final function createQueryString($cat, $order, $filter, $limits, $qm = true) {
    return ($qm ? "?" : "").(urlencode($cat) ? "&cat=".urlencode($this->category) : "").
      ($order   ? "&order_by=".urlencode($this->order()) : "").
      ($filter  ? $this->filters() : "").
      ($limits  ? "&from=".urlencode($this->limit_from)."&to=".urlencode($this->limit_to) : "");
  }

  private function filterSQLArray($q = "") {

    $like = ($this->filters['filter_ltitle'][0] && (($this->filters['filter_ltitle'][2][0] == '/' &&
      substr($this->filters['filter_ltitle'][2], -1)) == '/') ? " REGEXP '".
      substr($this->con->real_escape_string(substr($this->filters['filter_ltitle'][2], 1)), 0, -1)."' " :
      " LIKE ".($this->filters['filter_ltitle'][0] ? " CONCAT('%', '".
      $this->con->real_escape_string($this->filters['filter_ltitle'][2])."', '%')" : "'%'"));

    $fids = $this->filters['filter_ID'][0] ? str_replace(",", " OR `m`.`ID` = ", $this->filters['filter_ID'][2]) : "";

    return array(
      'tfil' => ($this->filters['filter_ltitle'][0] ? $like : ""),
      'ifil' => ($this->filters['filter_ID'][0] ? " AND (`m`.`ID` = ".$fids.")" : ""),
      'dfil' => ($this->filters['filter_disc'][0] ? " AND `m`.`disc` = ".$this->filters['filter_disc'][1] : ""),
      'lfil' => ($this->filters['filter_lingo'][0] ? " AND '".$this->con->real_escape_string($this->filters['filter_lingo'][2])."' ".
	($this->filters['filter_lingo_not'][0] ? "NOT " : "").
	"IN (SELECT `movie_languages`.`lang_id` FROM `movie_languages` WHERE `movie_languages`.`movie_id` = `m`.`id`)" : ""),
      'q' => $q
    );
  }

  private function getBuiltQuery($q = "") {

    $fi = $this->filterSQLArray($q);
    $ef = empty($fi['tfil'].$fi['dfil'].$fi['lfil']);

    if($this->filters['filter_ID'][0]) {

      $bq = (!$ef ? "(".
	self::$dvd_choice.($this->category == -1 ? "" : " AND `category` = ".$this->category).
	$fi['dfil'].$fi['lfil']." GROUP BY `m`.`ID` ".
	(empty($fi['tfil']) ? "" : "HAVING `ltitle` ".$fi['tfil']).$fi['q']
      .") UNION (" : "").
	self::$dvd_choice./*($this->category == -1 ? "" : " AND `category` = ".$this->category).*/
	$fi['ifil']." GROUP BY `m`.`ID` "
      .(!$ef ? ")" : "")." ORDER BY ".$this->order;

    } else {

      $bq = self::$dvd_choice.($this->category == -1 ? "" : " AND `category` = ".$this->category).
	$fi['dfil'].$fi['lfil']." GROUP BY `m`.`ID` ".
	(empty($fi['tfil']) ? "" : "HAVING `ltitle` ".$fi['tfil']).$fi['q']." ORDER BY ".$this->order;
    }

    return $bq;
  }

  protected final function mySQLRowsQuery($q = "") {
//     echo "<pre>".$this->getBuiltQuery($q)."</pre>\n";
    return $this->con->query($this->getBuiltQuery($q));
  }

  protected final function mySQLTotalQuery($q = "") {

    $fi = $this->filterSQLArray();

    return $this->con->query("SELECT CONCAT( IF( FLOOR( SUM( `dur_sec` ) / 3600 ) <= 99, ".
	"RIGHT( CONCAT( '00', FLOOR( SUM( `dur_sec` ) / 3600 ) ), 2 ), FLOOR( SUM( `dur_sec` ) / 3600 ) ), ':', ".
	"RIGHT( CONCAT( '00', FLOOR( MOD( SUM( `dur_sec` ), 3600 ) / 60 ) ), 2 ), ':', ".
	"RIGHT( CONCAT( '00', MOD( SUM( `dur_sec` ), 60 ) ), 2 ) ) AS `tot_dur` FROM (".$this->getBuiltQuery($q).") AS `choice`");
  }

  static public final function pageSize() {
    return 24;
  }

}

?>
