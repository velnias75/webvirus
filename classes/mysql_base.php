<?php
/*
 * Copyright 2017-2019 by Heiko Schäfer <heiko@rangun.de>
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

final class compressed_mysqli extends mysqli {

  public function __construct($host, $user, $pass, $db, $socket) {

    parent::init();

    /*if (!parent::options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 0')) {
     *      die('Setting MYSQLI_INIT_COMMAND failed');
  }*/

    /* if (!parent::options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
     *      die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
  } */

    if(!parent::real_connect($host, $user, $pass, $db, 0, $socket, MYSQLI_CLIENT_COMPRESS)) {
      die('Connect Error (' . mysqli_connect_errno() . ') '.mysqli_connect_error());
    }
  }
}

final class MySQLBase {

  private $proxy = null;
  private $mysqli = null;
  private $secret = null;
  private $update = false;
  private $secure = false;

  private $server = null;
  private $db = null;

  private function __construct() {

    require 'db_cred.php';

    $this->mysqli = new compressed_mysqli($server, $user, $pass, $db, empty($socket) ? '' : $socket);

    if($this->mysqli->connect_errno) {
      throw new ErrorException("Konnte keine Verbindung zu MySQL aufbauen: ".$this->mysqli->connect_error);
    }

    $this->mysqli->set_charset('utf8');
    if(!$this->mysqli->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,".
      "ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'")) {
       error_log($this->mysqli->error);
    }

    $this->proxy  = isset($proxy) ? $proxy : null;
    $this->secret = $secret;
    $this->upload = !isset($update) || $update == true;
    $this->secure = isset($secure) ? $secure : false;
    $this->server = $server;
    $this->db = $db;
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

  public function proxy() {
    return $this->proxy;
  }

  public function server() {
    return $this->server;
  }

  public function db() {
    return $this->db;
  }

  public function update_fid($id, $fid) {
    $this->mysqli->query("UPDATE users SET fid = ".
    (empty($fid) ? "null" : "'".$this->mysqli->real_escape_string($fid)."'")." WHERE id=".$id);
  }

  public function update_allowed() {
    return $this->upload;
  }

  public function update_omdb_id($mid, $tid, $ttp) { error_log("updating db");
    $this->mysqli->query("UPDATE movies SET tmdb_type='".$ttp."', tmdb_id=".$tid." WHERE ID=".$mid);
  }

  public function update_rating($uid, $mid, $rating) {
    if($rating != -1) {
      $this->mysqli->query("REPLACE INTO user_ratings VALUES (".$uid.", ".$mid.", ".$rating.")");
    } else {
      $this->mysqli->query("DELETE FROM user_ratings WHERE uid=".$uid." AND movie_id=".$mid);
    }
  }

  public function title_from_omdb_id($omdb) {

	$res = $this->mysqli->query("SELECT DISTINCT MAKE_MOVIE_TITLE(IF(s.name IS NOT NULL, s.name, m.title), ".
		                        "NULL, NULL, NULL, 0, 0) AS title FROM movies AS m ".
								"LEFT JOIN episode_series AS es ON m.ID = es.movie_id LEFT JOIN series AS s ".
								"ON s.id = es.series_id WHERE omdb_id = ".$omdb);

	if($res->num_rows) {
	  $row = $res->fetch_assoc();
	  $res->free_result();
	}

	return isset($row) ? $row['title'] : null;
  }

  public function tmdb_url_from_id($id) {

	$res = $this->mysqli->query("SELECT tmdb_type, tmdb_id FROM `movies`WHERE id = ".$id);

	if($res->num_rows) {
	  $row = $res->fetch_assoc();
	  $res->free_result();
	}

	return "https://www.themoviedb.org/".$row['tmdb_type']."/".$row['tmdb_id'];
  }

  public function chg_pass($id, $pass) {
    if(!$this->mysqli->query("UPDATE users SET pass=HEX(AES_ENCRYPT('".
      $this->mysqli->real_escape_string($pass)."', UNHEX(SHA2('".
      $this->mysqli->real_escape_string($this->secret)."', 512)))) WHERE id=".$id)) {
        throw new ErrorException($this->mysqli->error);
    }
  }

  public function new_user($display, $login, $pass) {

    if(!$this->mysqli->query("INSERT INTO users (login, pass, display_name) VALUES ('".
      $this->mysqli->real_escape_string($login)."', HEX(AES_ENCRYPT('".
      $this->mysqli->real_escape_string($pass)."', UNHEX(SHA2('".
      $this->mysqli->real_escape_string($this->secret)."', 512)))), '".
      $this->mysqli->real_escape_string($display)."')")) throw new ErrorException($this->mysqli->error);
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
      $this->secret."', 512))) AS CHAR (50)) AS cpass, display_name, admin, email, last_login, style, fid, ".
      "pagesize, oauth_access_token, oauth_access_token_secret, consumer_key, consumer_secret ".
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
        'email' => $row['email'],
	'last_login' => $row['last_login'],
	'auto_login' => $auto,
	'style' => $row['style'],
	'fid' => $row['fid'],
	'pagesize' => $row['pagesize'],
	'oauth_access_token' => $row['oauth_access_token'],
	'oauth_access_token_secret' => $row['oauth_access_token_secret'],
	'consumer_key' => $row['consumer_key'],
	'consumer_secret' => $row['consumer_secret']
	);

	$result->free_result();

	if(($res['cpass'] == $pass || ($auto && $res['epass'] == $pass))) {
	  $this->mysqli->query("UPDATE users SET last_login=CURRENT_TIMESTAMP WHERE id=".$res['id']);
	}

	return ($res['cpass'] == $pass || ($auto && $res['epass'] == $pass)) ? $res : "Falsches Passwort";

    } else {
      error_log($this->mysqli->error);
      return "Benutzer nicht gefunden";
    }
  }

  public function setLoggedInSession($ui, $update = false) {

    if(is_string($ui)) {

      $_SESSION['error'] = $ui;

    } else {

      $_SESSION['ui'] = $ui;

      if($update) {
	setcookie('wvpltok', MySQLBase::instance()->updatePLSet($ui['id']), time()+60*60*24*365, MySQLBase::getRequestURI()."/");
      }

      if(!empty($ui['fid']) && !$ui['auto_login']) {
	header("Location: ".$this->getRequestURI()."/fid.php");
	die;
      }
    }
  }

  public static function getRequestURI() {
    $dname = dirname($_SERVER['REQUEST_URI']);
    return ($dname != "/" ? $dname : "");
  }

  public function getOMDBId($disc = null) {

    $result = $this->mysqli->query("SELECT tmdb_type, tmdb_id FROM disc LEFT JOIN movies ON movies.disc = disc.ID AND tmdb_id IS NOT NULL ".
      "LEFT JOIN user_ratings ON movies.ID = user_ratings.movie_id".
      (is_null($disc) ? "" : " WHERE disc.ID = ".$disc).
      " GROUP BY movies.ID ORDER BY movies.disc DESC , AVG(user_ratings.rating) DESC , movies.ID DESC LIMIT 1");

    if($result || $result->num_rows) {
      $row = $result->fetch_assoc();

      if(empty($row['tmdb_id'])) {
        $result->free_result();
        throw new UnexpectedValueException();
      }

      $result->free_result();

      return [ $row['tmdb_id'], $row['tmdb_type'] ];

    } else error_log($this->mysqli->error);

    throw new UnexpectedValueException();
  }

  public function getOverallAvgRating() {

    $result = $this->mysqli->query("SELECT AVG(rating) AS avg_rating FROM user_ratings");
    $row = $result->fetch_assoc();
    $result->free_result();

    return $row['avg_rating'];
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

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: true; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
