<?php

final class MySQLBase {
  
  private $mysqli = null;
  
  private function __construct() {
  
    require 'db_cred.php';
    
    $this->mysqli = new mysqli($server, $user, $pass, $db);
    
    if($this->mysqli->connect_errno) {
      throw new ErrorException("Konnte keine Verbindung zu MySQL aufbauen: ".$this->mysqli->connect_error);
    }
    
    $this->mysqli->set_charset('utf8');
  }
  
  function __destruct() {
    $this->mysqli->close();
  }
  
  public static function instance() {
    
    static $instance = null;
    
    if(is_null($instance)) $instance = new MySQLBase();
    
    return $instance;
  }
  
  public function con() {
    return $this->mysqli;
  }
  
}

?>
