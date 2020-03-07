<?php
/*
 * Copyright 2020 by Heiko Schäfer <heiko@rangun.de>
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

trait OrderMailTraits {

  private function mail_att($to, $cc, $subject, $message, $sender, $sender_email, $reply_email, $dateien) {

	if(!is_array($dateien)) {
	  $dateien = array($dateien);
	}

	$attachments = array();

	foreach($dateien AS $key => $val) {
	  if(is_int($key)) {
		$datei = $val;
		$name = basename($datei);
	  } else {
		$datei = $key;
		$name = basename($val);
	  }

	  $size = filesize($datei);
	  $data = file_get_contents($datei);
	  $type = mime_content_type($datei);

	  $attachments[] = array("name" => $name, "size" => $size, "type" => $type, "data" => $data);
	}

	$mime_boundary  = "-----=" . md5(uniqid(microtime(), true));
	$mime_boundary2 = "-----=" . md5(uniqid(microtime(), true));

	$encoding = mb_detect_encoding($message, "utf-8, iso-8859-1, cp-1252");

	$header  = 'From: "'.addslashes(mb_encode_mimeheader($sender, 'UTF-8', 'B')).'" <'.$sender_email.">\r\n";
	$header .= "CC: ".$cc."\r\n";
	$header .= "Reply-To: ".$reply_email."\r\n";
	$header .= "Priority: urgent\r\n";
	$header .= "Sensitivity: private\r\n";
	$header .= "Disposition-Notification-To: ".$cc."\r\n";

	$header .= "MIME-Version: 1.0\r\n";
	$header .= "Content-Type: multipart/mixed; boundary=\"".$mime_boundary."\"\r\n";
	$header .= "This is a multi-part message in MIME format.\r\n\r\n";

	$content  = "--".$mime_boundary."\r\n";
	$content .= "Content-Type: multipart/alternative; boundary=\"".$mime_boundary2."\"\r\n";

	$content .= "This is a multi-part message in MIME format.\r\n\r\n";
	$content .= "--".$mime_boundary2."\r\n";

	$content .= "Content-Type: text/html; charset=\"$encoding\"\r\n";
	$content .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
	$content .= $message."\r\n";
	$content .= "--".$mime_boundary2."\r\n";

	$content .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
	$content .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
	$content .= html_entity_decode(strip_tags($message), ENT_COMPAT|ENT_HTML401, 'UTF-8')."\r\n";
	$content .= "--".$mime_boundary2."\r\n";

	foreach($attachments AS $dat) {

	  $data = chunk_split(base64_encode($dat['data']));
	  $content.= "--".$mime_boundary."\r\n";
	  $content.= "Content-Disposition: attachment;\r\n";
	  $content.= "\tfilename=\"".$dat['name']."\";\r\n";
	  $content.= "Content-Length: .".$dat['size'].";\r\n";
	  $content.= "Content-Type: ".$dat['type']."; name=\"".$dat['name']."\"\r\n";
	  $content.= "Content-Transfer-Encoding: base64\r\n\r\n";
	  $content.= $data."\r\n";
	}

	$content .= "--".$mime_boundary."--";

	return mail($to, mb_encode_mimeheader($subject, 'UTF-8', 'B'), $content, $header);
  }
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
