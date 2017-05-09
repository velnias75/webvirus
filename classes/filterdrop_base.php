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
  
  abstract protected function idField();
  abstract protected function nameField();
  abstract protected function filterName();
  abstract protected function noneValue();
  
  public function render($id) {
    
    $res = "<select class=\"input_filter\" name=\"".$this->filterName()."\" onchange=\"this.form.submit()\">".
      "<option ".($id == $this->noneValue() ? "selected" : "").
      " value=\"".$this->noneValue()."\">&nbsp;</option>\n";
    
    while($row = $this->result->fetch_assoc()) {
      $res .= "\t<option ".($id == $row[$this->idField()] ? "selected" : "")." value=\"".
	$row[$this->idField()]."\">".htmlentities($row[$this->nameField()], ENT_SUBSTITUTE, "utf-8")."</option>\n";
    }
    
    return $res."</select>\n";
  }

}

?>
