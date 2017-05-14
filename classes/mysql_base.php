<?php

final class MySQLBase {
  
  private $mysqli = null;
  private $secret = null;
  private $update = false;
  
  private function __construct() {
  
    require 'db_cred.php';
    
    $this->mysqli = new mysqli($server, $user, $pass, $db);
    
    if($this->mysqli->connect_errno) {
      throw new ErrorException("Konnte keine Verbindung zu MySQL aufbauen: ".$this->mysqli->connect_error);
    }
    
    $this->mysqli->set_charset('utf8');
    
    $this->secret = $secret;
    $this->upload = !isset($update) || $update == true;
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
  
  public function update_allowed() {
    return $this->upload;
  }
  
  public function chg_pass($id, $pass) {
    $this->mysqli->query("UPDATE users SET pass=HEX(AES_ENCRYPT('".
      $this->mysqli->real_escape_string($pass)."', UNHEX(SHA2('".
      $this->mysqli->real_escape_string($this->secret)."', 512)))) WHERE id=".$id);
  }
  
  public function new_user($display, $login, $pass) {

    $this->mysqli->query("INSERT INTO users (login, pass, display_name) VALUES ('".
      $this->mysqli->real_escape_string($login)."', HEX(AES_ENCRYPT('".
      $this->mysqli->real_escape_string($pass)."', UNHEX(SHA2('".
      $this->mysqli->real_escape_string($this->secret)."', 512)))), '".
      $this->mysqli->real_escape_string($display)."')");
  }
  
  public function login($login, $pass, $auto = false) {
    
    $result = $this->mysqli->query("SELECT id, login, pass, CAST(AES_DECRYPT(UNHEX(pass), UNHEX(SHA2('".
      $this->secret."', 512))) AS CHAR (50)) AS cpass, "."display_name, admin, last_login ".
      "FROM users WHERE login = '".$login."' LIMIT 1");

    if($result->num_rows == 1) {
      
      $row = $result->fetch_assoc();
      
      $res = array(
	  'id' => $row['id'],
	  'login' => $row['login'],
	  'epass' => $row['pass'],
	  'cpass' => $row['cpass'],
	  'display_name' => $row['display_name'], 
	  'admin' => boolval($row['admin']),
	  'last_login' => $row['last_login']
	);
      
      $result->free_result();
      
      if(($res['cpass'] == $pass || ($auto && $res['epass'] == $pass))) {
	$this->mysqli->query("UPDATE users SET last_login=CURRENT_TIMESTAMP WHERE id=".$res['id']);
      }
      
      return ($res['cpass'] == $pass || ($auto && $res['epass'] == $pass)) ? $res : "Falsches Passwort";
      
    } else {
      return "Benutzer nicht gefunden";
    } 
  }
}

?>
