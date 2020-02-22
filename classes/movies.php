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

require_once 'ampletraits.php';

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
  use AmpleTraits;

  private $par;
  private $loggedIn = false;

  private static $tooltipJS = <<<'EOD'
  $('.list.hasTooltip').mouseover(function(e) {

    var base     = $(this);
    var tooltipp = base.children("span");
    var image    = tooltipp.find("img");
    var abstract = tooltipp.find("p");

    $('.hasTooltip span').removeAttr('style');

    if(typeof image.attr('data-src') != 'undefined' && abstract.css('display') == 'none') {

      var req = new XMLHttpRequest();
      req.addEventListener('loadend', function(e) {
        if(req.status == 200) {
          abstract.removeAttr('style');
          abstract.css('display', 'inline-block');
          abstract.css('max-width', '250px');
          abstract.css('white-space', 'normal');
          abstract.css('font-variant', 'small-caps');
          abstract.html(req.response);
        }
      });

      req.open('GET', image.attr('data-src')+'&abstract=1');
      req.send(false);
    }

    var __align = function(e) {

      var tooltip = base.children("span");
      var tooltipTop = tooltip.offset().top;
      var tooltipLeft = (base.offset().left + base.width()) - tooltip.width() - 20;
      var tooltipBottom = tooltipTop + tooltip.outerHeight();
      var viewportTop = $(window).scrollTop();
      var viewportBottom = viewportTop + $(window).height();

      tooltip.css({ left: tooltipLeft });

      if(tooltipBottom > viewportBottom) {
        tooltip.css({ top: (viewportBottom - tooltip.outerHeight() - 25) });
      }
    }

    if(image.length) {

      var tooltipLeft = (base.offset().left + base.width()) - tooltipp.width() - 20;
      tooltipp.css({ left: tooltipLeft });

      if(image.attr("src") != image.attr("data-src")) {
        image.attr("src", image.attr("data-src")).on('load', __align);
      } else {
        __align(e);
      }
    }
  });
EOD;

  private static $spinner = <<<'EOD'
data:image/gif;base64,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
EOD;

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

private function renderRow($id = "", $ltitle = "", $st = "", $duration = "", $dursec = 0, $lingos = "", $disc = "", $fname = "", $cat = 1,
$isSummary = false, $isTop250 = false, $rating = -1, $avg = -1, $omdb_id = null, $spooky = null) {

  if(empty($id) && empty($ltitle) && empty($st) && empty($duration) && empty($lingos) && empty($disc) && empty($fname)) {
    $isSummary = true;
  }

  if(!$isSummary) {
    $nid = $this->makeLZID($id);
  } else {
    $nid = $id;
  }

  $atts = array('class' => "parity_".($this->par % 2));
  $tatt = array('align' => "left", 'class' => ($isTop250 ? "top250 " : "")."list ".($isSummary ? "" : "hasTooltip")." cat_".
  $cat.($isSummary ? "" : " ltitle".(($spooky && !$isTop250) ? " spooky" : "")));

  if(!$isSummary) {
    $atts['itemscope'] = null;
    $atts['itemtype']  = "http://schema.org/MediaObject";
    $tatt['nowrap']    = null;
  }

  $this->addRow(new Row(
    $atts,
    array(
      new Cell(array('nowrap' => null, 'class' => "list hack", 'align' => "right"),
      ($id === "" ? "&nbsp;" : ($isSummary || !$this->loggedIn ? "" : "<a href=\"#openModal_".$id."\" onclick=\"enableUserActions(".$id.", true)\">").
      htmlentities($nid, ENT_SUBSTITUTE, "utf-8").($isSummary || !$this->loggedIn ? "" : "</a><div id=\"openModal_".$id."\" class=\"modalDialog\">".
      "<div><a href=\"#close\" title=\"Schlie&szlig;en\" class=\"close\" onclick=\"enableUserActions(".$id.", false)\">X</a><div class=\"ua cat_".$cat."\">".
      $id."&nbsp;&ndash;&nbsp;".htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8")."</div>".(new UserActions($_SESSION['ui'], $id, $rating, $avg, $omdb_id))->render().
      "</div>")).($isSummary || !$this->loggedIn ? "" : "</div>")),
      new Cell($tatt,($this->loggedIn && !$isSummary ? "<a target=\"omdb\" href=\"".
      (is_null($omdb_id) ? "omdb.php?search=".urlencode($st) : "omdb.php?id=".$omdb_id)."&amp;q=".
      urlencode($_SERVER['QUERY_STRING'])."\">" : ($isSummary ? "<a href=\"#openModal_stats\">" : "")).
      (!$isSummary ? $this->ample($rating, $id) : "").
      ($ltitle === "" ? "&nbsp;" : htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8").($this->loggedIn && !$isSummary ? "</a>" : "").
      ($isSummary ? "" : "<span style=\"display: none;\" itemprop=\"name\">".("<center><img itemprop=\"image\" src=\""
	  .self::$spinner."\" "."data-src=\"omdb.php?cover-oid=".$omdb_id.(!$isTop250 ? "" : "&amp;top250=true")."&fallback=".
	  urlencode($ltitle)."\"></center><br>").(!$this->loggedIn || is_null($avg) ? "" : $this->ample($avg, $id, "tt_ample_mid")).
      htmlentities($ltitle, ENT_SUBSTITUTE, "utf-8")."<br /><center><p style=\"display:none;\">abstract</p><center></span>"))),
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

  $i      = 0;

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
	new Cell(array('title' => "/REGEXP/ erm&ouml;glicht Filterung mit regul&auml;ren Ausdr&uuml;cken.&#13;&#10;".
	"&#13;&#10;Spezialsuchen:&#13;&#10;".
	"#top, #top250 &ndash; Filme in der imdb-Top250&#13;&#10;".
	"#flop &ndash; Filme NICHT in der imdb-Top250&#13;&#10;".
	"#omu &ndash; Originale MIT Untertitel&#13;&#10;".
	"#oou &ndash; Originale OHNE Untertitel&#13;&#10;".
	"#good &ndash; gut bewertete Filme&#13;&#10;".
	"#okay &ndash; OK bewertete Filme&#13;&#10;".
	"#bad &ndash; schlecht bewertete Filme&#13;&#10;",
	'class' => "list_filter"),
	"<input name=\"filter_ltitle\" class=\"list_filter\" placeholder=\"Suchbegriff(e) oder /regul&auml;rer Ausdruck/\" ".
	"id=\"list_filter_ltitle\" type=\"text\" onkeydown=\"if (event.keyCode == 13) { this.form.submit(); return false; }\" ".
	"onfocus=\"var temp_value=this.value; this.value=''; this.value=temp_value\" value=\"".
	($this->filters['filter_ltitle'][0] ? $this->filters['filter_ltitle'][1] : "")."\">"),
	new Cell(array('title' => 'L&auml;nge in Minuten&#13;&#10;(Negativer Wert f&uuml;r maximale L&auml;nge)',
	'class' => "list_filter"), "<input name=\"filter_duration\" class=\"list_filter\" id=\"list_filter_duration\" size=\"4\" type=\"text\" ".
	"value=\"".($this->filters['filter_duration'][0] ? $this->filters['filter_duration'][1] : "").
	"\" style=\"width: 94%; text-align: right;\">"),
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
	  $this->renderRow($row['ID'], $row['ltitle'],
	  $row['st'], $row['duration'], $row['dur_sec'],
	  $row['lingos'], $row['disc'], $row['filename'],
	  $row['category'], false, $row['top250'],
	  isset($_SESSION['ui']) ? (is_null($row['user_rating']) ? -1 : $row['user_rating']) : (is_null($row['avg_rating']) ? -1 : $row['avg_rating']),
	  $row['avg_rating'], $row['omdb_id'], $row['spooky']);
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
	$this->pageSize() == -1 ? (MoviesBase::isMobile() ? MoviesBase::MOBILE_PAGESIZE : MoviesBase::STD_PAGESIZE) : $this->pageSize(),
	$this->limit_from, $this->limit_to))->render()))
	));

	if(isset($_SESSION['ui'])) {
	  MySQLBase::instance()->update_fid($_SESSION['ui']['id'], $this->isFiltered() ? $_SESSION['ui']['fid'] : null);
	}
    }

    return parent::render()."<input type=\"submit\" id=\"filter_submit\">";
  }

  public static function tooltipEvent() {
    return self::$tooltipJS;
  }
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
