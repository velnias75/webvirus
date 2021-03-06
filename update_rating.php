<?php
/*
 * Copyright 2018-2019 by Heiko Schäfer <heiko@rangun.de>
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

session_start();

require 'classes/mysql_base.php';
require 'classes/tracker.php';

if(isset($_SESSION['ui']) && !isset($_SESSION['ui']['error']) && isset($_GET['uid']) && isset($_GET['mid']) && isset($_GET['rating'])) {
  MySQLBase::instance()->update_rating($_GET['uid'], $_GET['mid'], $_GET['rating']);
  (new Tracker())->track("UPDATE RATING request for movie #".$_GET['mid']." to ".$_GET['rating']." by {".$_SERVER['HTTP_USER_AGENT']."}");
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
