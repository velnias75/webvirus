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

require 'classes/pdf.php';
require 'classes/tracker.php';

(new Tracker())->track("PDF generation request by {".$_SERVER['HTTP_USER_AGENT']."}");

try {
  (new PDF(isset($_GET['order_by']) ? $_GET['order_by'] : "ltitle",
  isset($_GET['from']) ? $_GET['from'] : 0,
  isset($_GET['to']) ? $_GET['to'] : PDF::pageSize(), isset($_GET['cat']) ? $_GET['cat'] : -1))->render();
} catch(Exception $e) {
  echo "<strong>Fehler:</strong> ".htmlentities($e->getMessage(), ENT_SUBSTITUTE, "utf-8");
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
