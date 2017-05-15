<?php

final class UserActions {

  private $ui = null;
  private $id = -1;

  function __construct($ui, $id) {
    $this->ui = $ui;
    $this->id = $id;
  }
  
  public function render() {
    return "Render some useless user actions for ".htmlentities($this->ui['display_name'], ENT_SUBSTITUTE, "utf-8")." here...";
  }

}

?>
