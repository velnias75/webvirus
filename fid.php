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

if(isset($_SESSION['ui']) && !isset($_SESSION['error'])) {

  $q  = "filter_ID=".urlencode($_SESSION['ui']['fid']);
  $q .= (isset($_GET['order_by']) ? "&order_by=".$_GET['order_by'] : "");

  session_write_close();
  header("Location: ".MySQLBase::getRequestURI()."/?".(empty($_SESSION['ui']['fid']) ? "" : $q));

} else {
  header("Location: ".MySQLBase::getRequestURI()."/");
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
