<?php

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
      "<option ".($id == $this->noneValue() ? "selected" : "").
      " value=\"".$this->noneValue()."\">&nbsp;</option>\n";
    
    while($row = $this->result->fetch_assoc()) {
      $res .= "\t<option ".($id == $row[$this->idField()] ? "selected" : "")." value=\"".
	$row[$this->idField()]."\">".htmlentities($row[$this->nameField()], ENT_SUBSTITUTE, "utf-8")."</option>\n";
    }
    
    return $res."</select>".($this->showNot() ? "&nbsp;<label><input value=\"on\" onchange=\"this.form.submit()\" name=\"".
      $this->filterName()."_not\" ".($checked ? "checked" : "")." type=\"checkbox\"><em>nicht</em></label>" : "")."\n";
  }

}

?>
