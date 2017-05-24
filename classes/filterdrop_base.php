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

abstract class FilterdropBase {

  private $result;

  protected function __construct($q) {

    $this->result = MySQLBase::instance()->con()->query($q);

    if(MySQLBase::instance()->con()->errno) {
      throw new ErrorException("MySQL-Fehler: ".MySQLBase::instance()->con()->error);
    }

  }

  function __destruct() {
    $this->result->free_result();
  }

  protected function showNot() {
    return false;
  }

  abstract protected function idField();
  abstract protected function noneValue();
  abstract protected function nameField();
  abstract protected function filterName();

  public final function render($id, $checked = false) {

    $res = "<select class=\"input_filter\" name=\"".$this->filterName()."\" onchange=\"this.form.submit()\">".
      "<option".($id == $this->noneValue() ? " selected" : "").
      " value=\"".$this->noneValue()."\">alle</option>\n";

    while($row = $this->result->fetch_assoc()) {
      $res .= "<option".($id == $row[$this->idField()] ? " selected" : "")." value=\"".
	$row[$this->idField()]."\">".htmlentities($row[$this->nameField()], ENT_SUBSTITUTE, "utf-8")."</option>";
    }

    return $res."</select>".($this->showNot() ? "&nbsp;<label><input ".($id == $this->noneValue() ? "disabled" : "").
      " value=\"on\" onchange=\"this.form.submit()\" name=\"".
      $this->filterName()."_not\" ".($checked && $id != $this->noneValue() ? "checked" : "").
      " type=\"checkbox\"><em>nicht</em></label>" : "")."\n";
  }

}

?>
