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

require 'classes/mysql_base.php';

session_start();

if(isset($_POST['btn']) && isset($_POST['btn']['login']) &&
  isset($_POST['login']) && isset($_POST['pass'])) {

  $ui = MySQLBase::instance()->login($_POST['login'], $_POST['pass']);

  if(is_string($ui)) {
    $_SESSION['error'] = $ui;
  } else {
    $_SESSION['ui'] = $ui;
  }
}

if(isset($_POST['btn']) && isset($_SESSION['ui'])) {

  if(isset($_POST['btn']['create']) && $_SESSION['ui']['admin'] && isset($_POST['display']) &&
    isset($_POST['login_new']) && isset($_POST['pass_new'])) {
    MySQLBase::instance()->new_user($_POST['display'], $_POST['login_new'], $_POST['pass_new']);
  } else if(isset($_POST['btn']['chg'])) {
    MySQLBase::instance()->chg_pass($_SESSION['ui']['id'], $_POST['pass_chg']);
  } else if(isset($_POST['btn']['logout'])) {

    unset($_SESSION['error']);
    unset($_SESSION['ui']);

    session_write_close();
  }
}

header("Location: ".dirname($_SERVER['REQUEST_URI'])."/".(isset($_POST['q']) ? "?".urldecode($_POST['q']) : ""), true, 302);

?>
