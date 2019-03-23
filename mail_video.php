<?php
/*
 * Copyright 2019 by Heiko Schäfer <heiko@rangun.de>
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
require 'classes/ampletraits.php';

final class Rating {
  use AmpleTraits;

  public function getRating($avg) {
      return $this->ample($avg, 0, 0, false, true);
  }

  function getLink() {
    return MySQLBase::instance()->protocol()."://".$_SERVER['SERVER_NAME'].MySQLBase::getRequestURI();
  }
}

$mail = <<<'EOD'
<html>
  <head>
    <style>
.ample_red,.ample_yellow{background-position:center;background-repeat:no-repeat;background-attachment:local;background-size:.75em}.ample_red{display:inline-flex;height:1em;width:1em;margin-right:.25em;border-radius:100%;background-color:#ff000080;background-image:url(%URL%/img/thumb-down.svg)}.ample_yellow{background-color:#ffff0080;background-image:url(%URL%/img/finger-of-a-hand-pointing-to-right-direction.svg)}.ample_green,.ample_off,.ample_yellow{display:inline-flex;height:1em;width:1em;margin-right:.25em;border-radius:100%}.ample_green{background-color:#00ff0080;background-position:center;background-repeat:no-repeat;background-attachment:local;background-image:url(%URL%/img/thumbs-up2.svg);background-size:.75em}.ample_off{background:#00000007}
    </style>
  </head>
  <body style="font-family:sans-serif;background-color:#eeeeee;">
    <p>Guten Morgen,<br /><br />in einer hirnlosen Aktion habe ich &ndash; %RNAME% &ndash;,<br />unter Anwendung meiner schwachsinnigen Erkenntnisse meiner %RAND%-jährigen Dissertation in Informatik,<br />die <a href="%URL%/index.php">DB</a> angewiesen, Dir folgenden Schrott- bzw. Rentnerfilm per eMail zu senden:</p>
    <table border="0">
      <tr><td>
        <img style="box-shadow:3px 3px 3px #c06ee4;" alt="Cover von Schrott- bzw. Rentnerfilm &quot;%TITLE%&quot;" src="data:image/png;base64,%IMAGE%" />
      </td>
      <td style="vertical-align:top;padding-left:10px;padding-top:10px;">
        <dl style="line-height:1.4em;">
          <dt><b>Nr</b></dt><dd><a href="%URL%/video/%MID%">%MID%</a></dd>
          <dt><b>Titel</b></dt><dd>%TITLE%</dd>
          <dt><b>L&auml;nge</b></dt><dd>%DUR%</dd>
          <dt><b>Sprachen(n)</b></dt><dd>%LINGOS%</dd>
          <dt><b>DVD</b></dt><dd><a href="%URL%/disc/%DNR%">%DISC%</a></dd>
          <dt><b>Bewertung</b></dt><dd>%RATING%</dd>
        </dl>
      </td></tr>
    </table>
    <p>Gr&uuml;sse</br>%RNAME%</p>
  </body>
</html>
EOD;

$r  = new Rating();

$sql = "SELECT m.ID AS `mid`, d.id AS dnr, m.top250 AS top250 , MAKE_MOVIE_TITLE( `m`.`title`, `m`.`comment`, `s`.`name`, `es`.`episode`, `s`.`prepend`, `m`.`omu` ) ".
  "AS `title`, duration_string(m.duration) AS dur, IF( `l`.`name` IS NOT NULL, TRIM( GROUP_CONCAT( `l`.`name` ORDER BY `ml`.`lang_id` DESC SEPARATOR ', ' ) ), 'n. V.' ) ".
  "AS `lingos`, d.name AS disc, AVG(ur.rating) AS avg_rating, m.omdb_id AS oid FROM movies AS m LEFT JOIN disc AS d ON m.disc = d.id LEFT JOIN user_ratings AS ur ".
  "ON ur.movie_id = m.id LEFT JOIN movie_languages AS ml ON ml.movie_id = m.ID LEFT JOIN languages AS l ON l.id = ml.lang_id LEFT JOIN `episode_series` AS `es` ".
  "ON `m`.`ID` = `es`.`movie_id` LEFT JOIN `series` AS `s` ON `s`.`id` = `es`.`series_id` WHERE m.ID = ".$_GET['mid'];

$result = MySQLBase::instance()->con()->query($sql);
$rows   = $result->fetch_assoc();

$ch = curl_init($r->getLink()."/omdb.php?cover-oid=".$rows['oid'].($rows['top250'] ? "&top250=true" : ""));
curl_setopt($ch, CURLOPT_USERAGENT, "db-webvirus-mailer/1.0");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$pic = base64_encode(curl_exec($ch));
curl_close($ch);

$mail = preg_replace('/%RATING%/', !is_null($rows['avg_rating']) ? $r->getRating($rows['avg_rating']) : "unbewertet", $mail);
$mail = preg_replace('/%RNAME%/', "Dr. inf. ".(isset($_SESSION['ui']) ? htmlentities($_SESSION['ui']['display_name']) : "O. Normalverbraucher"), $mail);
$mail = preg_replace('/%URL%/', $r->getLink(), $mail);
$mail = preg_replace('/%IMAGE%/', $pic, $mail);
$mail = preg_replace('/%MID%/', $rows['mid'], $mail);
$mail = preg_replace('/%TITLE%/', htmlentities($rows['title']), $mail);
$mail = preg_replace('/%DUR%/', $rows['dur'], $mail);
$mail = preg_replace('/%DNR%/', $rows['dnr'], $mail);
$mail = preg_replace('/%DISC%/', htmlentities($rows['disc']), $mail);
$mail = preg_replace('/%LINGOS%/', htmlentities($rows['lingos']), $mail);
$mail = preg_replace('/%RAND%/', mt_rand(5, 30), $mail);

$header = "From: Heikos Schrott- & Rentnerfilme <no-reply@rangun.de>\n".(empty($_SESSION['ui']['email']) ?
  "" : ("Bcc: ".$_SESSION['ui']['email']."\n".
  "Reply-To: ".$_SESSION['ui']['display_name']." <".$_SESSION['ui']['email'].">\n")).
  "Organization: Informatiker-Sucht-Hilfe\n".
  "X-Mailer: hirnloser-db-webvirus-mailer 1.0\n".
  "Content-Type: text/html; charset=utf-8"; 

mail($_GET['mailto'], "Schrott- bzw. Rentnerfilm: #".$rows['mid']." - ".$rows['title'], $mail, $header);

?>
