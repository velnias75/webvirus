<?php

require_once 'mysql_base.php';

class CatChoice extends MySQLBase {

  private $result;
  private $movies;

  function __construct($m) {  
    
    parent::__construct();
    
    $this->result = $this->con()->query("SELECT `id`, `name` FROM `categories` ORDER BY `id`");
    
    if($this->con()->errno) {
      throw new ErrorException("MySQL-Fehler: ".$this->con()->error);
    }
    
    $this->movies = $m;
    
  }
  
  function __destruct() {
    $this->result->free_result();
  }
  
  public function render() {
    
    echo "<table class=\"cat_nav\" border=\"0\" width=\"100%\"><tr><th class=\"cat_nav\">Kategorie</th></tr>".
    "<tr><td><ul class=\"cat_nav\"><li class=\"cat_0".($this->movies->category() == -1 ? " cat_nav_active" : "")."\">".
      ($this->movies->category() != -1 ? "<a class=\"cat_nav\" href=\"".$this->movies->queryString(-1)."\">" : "").
      "Alle Videos".
      ($this->movies->category() != -1 ? "</a>" : "")."</li>\n";
    
    while($row = $this->result->fetch_assoc()) {
      echo "<li class=\"cat_".$row['id'].($this->movies->category() == $row['id'] ? " cat_nav_active" : "")."\">".
	($this->movies->category() != $row['id'] ? "<a class=\"cat_nav\" href=\"".$this->movies->queryString($row['id'])."\">" : "").
	htmlentities($row['name'], ENT_SUBSTITUTE, "utf-8").
	($this->movies->category() != $row['id'] ? "</a>" : "").
	"</li>";
    }
    
    echo "</ul></td></tr></table>\n";
  }
  
}

?>
