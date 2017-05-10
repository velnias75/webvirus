<?php

require_once 'filterdrop_base.php';

final class FilterdropDisc extends FilterdropBase {

  function __construct() {
    parent::__construct("SELECT `name`, `ID` FROM `disc` AS `disc` WHERE `ID` <> 265 AND `ID` <> 263 ORDER BY `regular` ASC, `vdvd` ASC, LEFT( `name`, 1 ) ASC, LENGTH( `name` ) ASC, `name` ASC");
  }

  protected function idField() {
    return "ID";
  }
  
  protected function nameField() {
    return "name";
  }
  
  protected function filterName() {
    return "filter_disc";
  }
  
  protected function noneValue() {
    return -1;
  }

}

?>
