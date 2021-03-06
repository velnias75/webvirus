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

require 'classes/mysql_base.php';

session_start();

if(isset($_POST['btn']) && isset($_POST['btn']['login']) && isset($_POST['login']) && isset($_POST['pass'])) {
    MySQLBase::instance()->setLoggedInSession(MySQLBase::instance()->login($_POST['login'], $_POST['pass']),
    isset($_POST['pl']));
    $_SESSION['authd'] = true;
  }

if(isset($_POST['btn']) && isset($_SESSION['ui'])) {

  if(isset($_POST['btn']['create']) && $_SESSION['ui']['admin'] && isset($_POST['display']) &&
    isset($_POST['login_new']) && isset($_POST['pass_new'])) {
      try {
	MySQLBase::instance()->new_user($_POST['display'], $_POST['login_new'], $_POST['pass_new']);
      } catch(ErrorException $e) {
        $err = "Benutzer \"".$_POST['display']."\" (".$_POST['login_new'].") konnte nicht angelegt werden.";
	error_log($e->getMessage());
      }
    } else if(isset($_POST['btn']['chg'])) {
      try {
        MySQLBase::instance()->chg_pass($_SESSION['ui']['id'], $_POST['pass_chg']);
      } catch(ErrorException $e) {
        $err = "Passwort konnte nicht geändert werden.";
	error_log($e->getMessage());
      }
    } else if(isset($_POST['btn']['logout'])) {

      $err = "Benutzer \"".$_SESSION['ui']['display_name']."\" ist nun ausgeloggt.";

      unset($_SESSION['error']);
      unset($_SESSION['ui']);
      unset($_SESSION['authd']);

      if(isset($_COOKIE['wvpltok'])) {
	MySQLBase::instance()->deletePLSet(substr($_COOKIE['wvpltok'], 32));
	setcookie('wvpltok', '', time() - 3600, MySQLBase::getRequestURI()."/");
      }

      session_write_close();
    }
}

$header = "Location: ".MySQLBase::getRequestURI()."/".(!empty($err) ? "?err=".urlencode($err) : "").
  (isset($_POST['q']) && (!isset($_POST['btn']['logout'])) ? (!empty($err) ? "&" : "?").urldecode($_POST['q']) : "");

header($header, true, 302);

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
