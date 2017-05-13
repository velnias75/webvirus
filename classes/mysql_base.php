<?php

final class MySQLBase {
  
  private $mysqli = null;
  private $secret = null;
  
  private function __construct() {
  
    require 'db_cred.php';
    
    $this->mysqli = new mysqli($server, $user, $pass, $db);
    
    if($this->mysqli->connect_errno) {
      throw new ErrorException("Konnte keine Verbindung zu MySQL aufbauen: ".$this->mysqli->connect_error);
    }
    
    $this->mysqli->set_charset('utf8');
    
    $this->secret = $secret;
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
  
  public function login($login, $pass) {
    
    $result = $this->mysqli->query("SELECT id, login, pass, CAST(AES_DECRYPT(UNHEX(pass), UNHEX(SHA2('".
      $this->secret."', 512))) AS CHAR (50)) AS cpass, "."display_name, admin FROM users WHERE login = '".
      $login."' LIMIT 1");

    if($result->num_rows == 1) {
      
      $row = $result->fetch_assoc();
      
      $res = array(
	  'id' => $row['id'],
	  'login' => $row['login'],
	  'epass' => $row['pass'],
	  'cpass' => $row['cpass'],
	  'display_name' => $row['display_name'], 
	  'admin' => boolval($row['admin'])
	);
      
      $result->free_result();
      
      return ($res['cpass'] == $pass || $res['epass'] == $pass) ? $res : "Falsches Passwort";
      
    } else {
      return "Benutzer nicht gefunden";
    } 
  }
}

?>
