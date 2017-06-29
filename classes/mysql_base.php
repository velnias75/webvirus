<?php
/*
 * Copyright 2017 by Heiko Schäfer <heiko@rangun.de>
 *
 * This file is part of webvirus.
 *
 * webvirus is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * webvirus is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with webvirus.  If not, see <http://www.gnu.org/licenses/>.
 */

final class MySQLBase {

  private $mysqli = null;
  private $secret = null;
  private $update = false;
  private $secure = false;

  private function __construct() {

    require 'db_cred.php';

    $this->mysqli = new mysqli($server, $user, $pass, $db);

    if($this->mysqli->connect_errno) {
      throw new ErrorException("Konnte keine Verbindung zu MySQL aufbauen: ".$this->mysqli->connect_error);
    }

    $this->mysqli->set_charset('utf8');

    $this->secret = $secret;
    $this->upload = !isset($update) || $update == true;
    $this->secure = isset($secure) || $secure == true;
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

  public function update_fid($id, $fid) {
    $this->mysqli->query("UPDATE users SET fid = ".
      (empty($fid) ? "null" : "'".$this->mysqli->real_escape_string($fid)."'")." WHERE id=".$id);
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

  public function plogin($uid, $tok) {

    $sql = "SELECT id, login, pass FROM users LEFT JOIN users_plogins ON users_plogins.uid = users.id ".
      "WHERE users_plogins.uid = ".hexdec($uid)." AND users_plogins.token = '".$tok."'";

    $result = $this->mysqli->query($sql);

    if($result->num_rows) {
      $row = $result->fetch_assoc();
      $result->free_result();

      return $this->login($row['login'], $row['pass'], true);
    }

    return null;
  }

  public function login($login, $pass, $auto = false) {

    $result = $this->mysqli->query("SELECT id, login, pass, CAST(AES_DECRYPT(UNHEX(pass), UNHEX(SHA2('".
      $this->secret."', 512))) AS CHAR (50)) AS cpass, display_name, admin, last_login, style, fid, ".
      "pagesize FROM users WHERE login = '".$login."' LIMIT 1");

    if($result->num_rows == 1) {

      $row = $result->fetch_assoc();

      $res = array(
	  'id' => $row['id'],
	  'login' => $row['login'],
	  'epass' => $row['pass'],
	  'cpass' => $row['cpass'],
	  'display_name' => $row['display_name'],
	  'admin' => boolval($row['admin']),
	  'last_login' => $row['last_login'],
	  'auto_login' => $auto,
	  'style' => $row['style'],
	  'fid' => $row['fid'],
	  'pagesize' => $row['pagesize']
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

  public function setLoggedInSession($ui, $update = false) {

    if(is_string($ui)) {

      $_SESSION['error'] = $ui;

    } else {

      $_SESSION['ui'] = $ui;

      if($update) {
	setcookie('wvpltok', MySQLBase::instance()->updatePLSet($ui['id']), time()+60*60*24*365, dirname($_SERVER['REQUEST_URI'])."/");
      }

      if(!empty($ui['fid']) && !$ui['auto_login']) {
	header("Location: ".dirname($_SERVER['REQUEST_URI'])."/fid.php");
	die;
      }
    }
  }

  private function createPLSet($uid) {
    $tok = strtoupper(substr("00000000000000000000000000000000".bin2hex(openssl_random_pseudo_bytes(16)), -32));
    return array('uid' => (int)$uid, 'tok' => $tok, 'str' => $tok.strtoupper(substr("00000000".dechex($uid), -8)));
  }

  public function deletePLSet($uid) {
    $this->mysqli->query("DELETE FROM users_plogins WHERE uid = ".dechex($uid));
  }

  public function updatePLSet($uid) {
    $pls = $this->createPLSet($uid);
    $this->mysqli->query("REPLACE INTO users_plogins (uid, token) VALUES (".$pls['uid'].", '".$pls['tok']."')");
    return $pls['str'];
  }

  public function protocol() {
    return "http".($this->secure ? "s" : "");
  }
}

?>
