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

$urx = "/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/";

$msg  = <<<'EOD'
Guten Morgen,<br /><br />in einer hirnlosen Aktion habe ich &ndash; %RNAME% &ndash;,<br />unter Anwendung meiner schwachsinnigen Erkenntnisse meiner %RAND%-jährigen Dissertation in Informatik,<br />die <a href="%URL%/index.php">DB</a> angewiesen, Dir folgenden Schrott- bzw. Rentnerfilm per eMail zu senden:
EOD;

$mail = <<<'EOD'
<html>
  <head>
    <style>
.ample_red,.ample_yellow{background-position:center;background-repeat:no-repeat;background-attachment:local;background-size:.75em}.ample_red{display:inline-flex;height:1em;width:1em;margin-right:.25em;border-radius:100%;background-color:#ff000080;background-image:url(%URL%/img/thumb-down.svg)}.ample_yellow{background-color:#ffff0080;background-image:url(%URL%/img/finger-of-a-hand-pointing-to-right-direction.svg)}.ample_green,.ample_off,.ample_yellow{display:inline-flex;height:1em;width:1em;margin-right:.25em;border-radius:100%}.ample_green{background-color:#00ff0080;background-position:center;background-repeat:no-repeat;background-attachment:local;background-image:url(%URL%/img/thumbs-up2.svg);background-size:.75em}.ample_off{background:#00000007}
    </style>
  </head>
  <body style="font-family:sans-serif;background-color:#eeeeee;">
    <p>%MESSAGE%</p>
    <table border="0">
      <tr><td>
        <img style="box-shadow:3px 3px 3px #c06ee4;width:150px;" 
          title="Plakat von Schrott- bzw. Rentnerfilm &quot;%TITLE%&quot;"
          alt="Plakat von Schrott- bzw. Rentnerfilm &quot;%TITLE%&quot;" src="%IMAGE%" />
      </td>
      <td style="vertical-align:top;padding-left:10px;padding-top:10px;">
        <dl style="line-height:1.4em;">
          <dt><b>Nr</b></dt><dd><a href="%URL%/video/%MID%">%MID%</a></dd>
          <dt><b>Titel</b></dt><dd>%TITLE%</dd>
          <dt><b>L&auml;nge</b></dt><dd>%DUR%</dd>
          <dt><b>Sprachen(n)</b></dt><dd>%LINGOS%</dd>
          <dt><b>DVD</b></dt><dd><a href="%URL%/disc/%DNR%">%DISC%</a></dd>
          <dt><b>Kategorie</b></dt><dd>%CAT%</dd>
          <dt><b>Bewertung</b></dt><dd>%RATING%</dd>
        </dl>
      </td></tr>
    </table>
    <p>Gr&uuml;sse<br />%RNAME%</p>
  </body>
</html>
EOD;

if(!isset($_SESSION['ui'])) {
  http_response_code(401);
  die;
}

if(empty($_SESSION['ui']['email'])) {
  http_response_code(403);
  die;
}

$r  = new Rating();

$sql = "SELECT m.ID AS `mid`, d.id AS dnr, m.top250 AS top250, c.name AS cat, MAKE_MOVIE_TITLE(`m`.`title`, `m`.`comment`,`s`.`name`, `es`.`episode`, ".
  "`s`.`prepend`, `m`.`omu`) AS `title`, duration_string(m.duration) AS dur, IF(`l`.`name` IS NOT NULL, TRIM(GROUP_CONCAT( `l`.`name` ORDER BY `ml`.`lang_id` ".
  "DESC SEPARATOR ', ')), 'n. V.') AS `lingos`, d.name AS disc, AVG(ur.rating) AS avg_rating,  m.omdb_id AS oid FROM movies AS m LEFT JOIN disc AS d ".
  "ON m.disc = d.id LEFT JOIN user_ratings AS ur ON ur.movie_id = m.id LEFT JOIN movie_languages AS ml ON  ml.movie_id = m.ID LEFT JOIN languages AS l ".
  "ON l.id = ml.lang_id LEFT JOIN `episode_series` AS `es` ON `m`.`ID` = `es`.`movie_id` LEFT JOIN `series` AS `s` ON `s`.`id` = `es`.`series_id` ".
  "LEFT JOIN categories AS c ON c.ID = m.category WHERE m.ID = ".$_POST['mid'];

$result = MySQLBase::instance()->con()->query($sql);
$rows   = $result->fetch_assoc();

$mail   = preg_replace('/%RATING%/', !is_null($rows['avg_rating']) ? $r->getRating($rows['avg_rating']) : "unbewertet", $mail);
$msg    = preg_replace('/%RNAME%/', "Dr. inf. ".(isset($_SESSION['ui']) ? htmlentities($_SESSION['ui']['display_name']) : "O. Normalverbraucher"), $msg);
$mail   = preg_replace('/%RNAME%/', "Dr. inf. ".(isset($_SESSION['ui']) ? htmlentities($_SESSION['ui']['display_name']) : "O. Normalverbraucher"), $mail);
$mail   = preg_replace('/%URL%/', $r->getLink(), $mail);
$msg    = preg_replace('/%URL%/', $r->getLink(), $msg);
$mail   = preg_replace('/%IMAGE%/', $r->getLink()."/omdb.php?cover-oid=".$rows['oid'].($rows['top250'] ? "&top250=true" : ""), $mail);
$mail   = preg_replace('/%MID%/', $rows['mid'], $mail);
$mail   = preg_replace('/%TITLE%/', htmlentities($rows['title']), $mail);
$mail   = preg_replace('/%DUR%/', $rows['dur'], $mail);
$mail   = preg_replace('/%DNR%/', $rows['dnr'], $mail);
$mail   = preg_replace('/%DISC%/', htmlentities($rows['disc']), $mail);
$mail   = preg_replace('/%LINGOS%/', htmlentities($rows['lingos']), $mail);
$msg    = preg_replace('/%RAND%/', mt_rand(5, 30), $msg);
$mail   = preg_replace('/%CAT%/', htmlentities($rows['cat']), $mail);
$mail   = preg_replace('/%MESSAGE%/', empty($_POST['msg']) ? $msg : nl2br(preg_replace($urx, "<a href=\"$0\">$0</a>", $_POST['msg']))."<hr />", $mail);

$header = "From: =?utf-8?B?".base64_encode("\xF0\x9F\x98\xA8 Heikos Schrott- & Rentnerfilme")."?= <no-reply@rangun.de>\n".(empty($_SESSION['ui']['email']) ?
  "" : ((filter_var($_POST['bcc'], FILTER_VALIDATE_BOOLEAN) ? "Bcc: ".$_SESSION['ui']['email']."\n" : "").
  "Reply-To: =?utf-8?B?".base64_encode($_SESSION['ui']['display_name'])."?= <".$_SESSION['ui']['email'].">\n")).
  "Organization: Informatiker-Sucht-Hilfe\n".
  "X-Mailer: hirnloser-db-webvirus-mailer 1.0\n".
  "MIME-Version: 1.0\n".
  "Content-Type: text/html; charset=utf-8"; 

if(!mail($_POST['mailto'], "=?utf-8?B?".base64_encode("\xF0\x9F\x98\x92 Schrott- bzw. Rentnerfilm: #".$rows['mid']." - ".$rows['title'])."?=", $mail, 
  $header, "-f ".(empty($_SESSION['ui']['email']) ? "heiko@rangun.de" : $_SESSION['ui']['email']))) {
    http_response_code(503);
}

?>
