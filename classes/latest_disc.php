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

require_once 'mysql_base.php';
require_once 'irenderable.php';

final class LatestDisc implements IRenderable {

  private $result;
  private $movies;
  private $con;

  function __construct(Movies $m) {

    $this->con = MySQLBase::instance()->con();
    $this->result = $this->con->query("SELECT `id`, `name`, DATE_FORMAT(`created`, '%d.%m.%Y') AS `df` ".
      "FROM `disc` ORDER BY `created` DESC LIMIT 1");

    if($this->con->errno) {
      throw new ErrorException("MySQL-Fehler: ".$this->con->error);
    }

    $this->movies = $m;

  }

  function __destruct() {
    $this->result->free_result();
  }

  public function render() {

    $row = $this->result->fetch_assoc();

    echo "<table class=\"cat_nav\" border=\"0\" width=\"100%\"><tr><th class=\"cat_nav\">Neueste DVD</th></tr>".
    "<tr><td align=\"left\" nowrap><ul class=\"cat_nav\"><li><a class=\"cat_nav\" href=\"".$this->movies->discQueryString($row['id'])."\">".
      htmlentities($row['name'], ENT_SUBSTITUTE, "utf-8")."</a>&nbsp;(".htmlentities($row['df'], ENT_SUBSTITUTE, "utf-8").")</li>\n";

    echo "</ul></td></tr></table>\n";
  }

}

?>
