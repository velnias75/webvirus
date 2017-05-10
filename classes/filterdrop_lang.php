<?php

require_once 'filterdrop_base.php';

class FilterdropLang extends FilterdropBase {

  function __construct() {
    parent::__construct("SELECT `name`, `id` FROM `languages` ORDER BY `name`");
  }

  protected function showNot() {
    return true;
  }
  
  protected function idField() {
    return "id";
  }
  
  protected function nameField() {
    return "name";
  }
  
  protected function filterName() {
    return "filter_lingo";
  }
  
  protected function noneValue() {
    return "";
  }

}

?>
