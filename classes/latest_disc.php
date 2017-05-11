<?php

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
