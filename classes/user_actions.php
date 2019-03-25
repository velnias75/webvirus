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

require_once 'ampletraits.php';

final class UserActions {

  use AmpleTraits;

  private $rating = -1;
  private $omdb   = null;
  private $avg    = -1;
  private $ui     = null;
  private $id     = -1;

  function __construct($ui, $id, $rating, $avg, $omdb) {
    $this->ui     = $ui;
    $this->id     = $id;
    $this->avg    = is_null($avg) ? -1 : $avg;
    $this->omdb   = $omdb;
    $this->rating = $rating;
  }

  public static function enableUserActions() {
    return "function enableUserActions(id, enabled) {".
             "if(enabled) { document.getElementById('id_ua_cover_'+id).src=document.getElementById('id_ua_cover_'+id).getAttribute('data-src'); }".
             "else {document.getElementById('id_ua_cover_'+id).src='img/nocover.jpg'; }".
             "$('input[name=ample_' + id + ']').each(function(i) { $(this).prop('disabled', !enabled); });".
             "$('input[name=ua_omdb_' + id + ']').each(function(i) { $(this).prop('disabled', !enabled); });".
             "$('input[name=ua_mail_msg_show_' + id + ']').each(function(i) { $(this).prop('disabled', !enabled); });".
             "$('textarea[name=ua_mail_msg_' + id + ']').each(function(i) { $(this).prop('disabled', !enabled); });".
             "$('input[name=ua_mail_bcc_' + id + ']').each(function(i) { $(this).prop('disabled', !enabled); });".
             "$('input[name=ua_mailto_' + id + ']').each(function(i) { $(this).prop('disabled', !enabled); ".
             self::loadLastEMail()." $(this).val(localStorage.getItem('lastUsedEMail'));});".
             "return false;".
           "}\n";
  }

  private function script() {
    return "var rad_".$this->id." = document.movies.ample_".$this->id.";".
    "var prev_".$this->id." = null;".
    "for(var i = 0; i < rad_".$this->id.".length; i++) {".
      "rad_".$this->id."[i].onclick = function() {".
	"if(this !== prev_".$this->id.") {".
	  "prev_".$this->id." = this;".
	  "document.getElementById('ample_mid".$this->id."').setAttribute('class', (this.value == 0 ? 'ample_red' : (this.value == 1 ? 'ample_yellow' : (this.value == 2 ? 'ample_green' : 'ample_off'))));".
	  "var oReq_".$this->id." = new XMLHttpRequest();".
	  "oReq_".$this->id.".addEventListener('loadend', function(e) { ".
	    "if(oReq_".$this->id.".status != 200) {".
	      "alert('Aktualisierung der Bewertung fehlgeschlagen.\\nGrund: ' + oReq_".$this->id.".status + ' ' + oReq_".$this->id.".statusText);".
	    "}".
	  " });".
	  "oReq_".$this->id.".open('GET', 'update_rating.php?uid=".$this->ui['id']."&mid=".$this->id."&rating=' + this.value);".
	  "oReq_".$this->id.".send();".
	"}".
      "};".
    "}";
  }

  private static function loadLastEMail() {
    return "if(typeof(Storage) !== 'undefined') {".
      "var eMails = new Set(JSON.parse(localStorage.getItem('usedEMails')));".
      "var opts = '';".
      "for(let item of eMails) {".
        "opts += '<option value=\"'+item+'\" />';".
      "}".
      "document.getElementById('ua_mail_list_'+id).innerHTML = opts;".
    "} ";
  }

  private function saveLastEMail($id) {
    return "if(typeof(Storage) !== 'undefined') {". 
      "var eMails = new Set(JSON.parse(localStorage.getItem('usedEMails')));".
      "var lMail = document.getElementById('ua_mailto_".$id."').value;". 
      "eMails.add(lMail);".
      "localStorage.setItem('usedEMails', JSON.stringify(Array.from(eMails)));".
      "localStorage.setItem('lastUsedEMail', lMail);".
    "} ";
  }

  public function render() {

    $rcheck = array($this->rating == -1 ? "checked" : "",
    $this->rating ==  2 ? "checked" : "",
    $this->rating ==  1 ? "checked" : "",
    $this->rating ==  0 ? "checked" : "");

    return "<center><br /><b>Hirnlose Bewertung:</b><table>".
      ($this->avg != -1 ? "<tr><td align=\"center\"><small>(".$this->ample($this->avg, $this->id, "ua_ample_mid")."durchschn. Bewertung)</small></td></tr>" : "").
      "<tr><td><input id=\"ampleoff_".$this->id."\" type=\"radio\" name=\"ample_".$this->id."\" value=\"-1\" ".$rcheck[0]." disabled>".
        "<label for=\"ampleoff_".$this->id."\"><div class=\"ample_off\">&nbsp;</div>unbewertet/ungesehen</label></td></tr>".
      "<tr><td><input id=\"amplegreen_".$this->id."\" type=\"radio\" name=\"ample_".$this->id."\" value=\"2\" ".$rcheck[1]." disabled>".
        "<label for=\"amplegreen_".$this->id."\"><div class=\"ample_green\">&nbsp;</div>gut</label></td></tr>".
      "<tr><td><input id=\"ampleyellow_".$this->id."\" type=\"radio\" name=\"ample_".$this->id."\" value=\"1\" ".$rcheck[2]." disabled>".
        "<label for=\"ampleyellow_".$this->id."\"><div class=\"ample_yellow\">&nbsp;</div>okay</label></td></tr>".
      "<tr><td><input id=\"amplered_".$this->id."\" type=\"radio\" name=\"ample_".$this->id."\" value=\"0\" ".$rcheck[3]." disabled>".
        "<label for=\"amplered_".$this->id."\"><div class=\"ample_red\">&nbsp;</div>schrecklich</label></td></tr>".
      (empty($this->ui['email']) ? "" : "<tr><td>&nbsp;</td></tr>".
      "<tr><td><label for=\"ua_mailto_".$this->id."\">Video als eMail versenden:</label><br />".
        "<div style=\"display:inline-grid;width:100%\">".
        "<div><input name=\"ua_mail_msg_show_".$this->id."\" id=\"ua_mail_msg_show_".$this->id."\" type=\"checkbox\" disabled ".
        "onchange=\"if(event.target.checked) { document.getElementById('ua_mail_msg_".$this->id.
        "').style='display:inline-grid;' } else { document.getElementById('ua_mail_msg_".$this->id."').style='display:none;'; ".
        "document.getElementById('ua_mail_msg_".$this->id."').value=''; }\">".
        "<label for=\"ua_mail_msg_show_".$this->id."\">eigene Nachricht an Empf&auml;nger</label></div>".
        "<textarea id=\"ua_mail_msg_".$this->id."\" name=\"ua_mail_msg_".$this->id."\" autofocus=\"true\" cols=\"20\" rows=\"5\" style=\"display:none;\" disabled />".
	"</textarea></div><div><input name=\"ua_mail_bcc_".$this->id."\" id=\"ua_mail_bcc_".$this->id."\" type=\"checkbox\" disabled>".
        "<label for=\"ua_mail_bcc_".$this->id."\">Kopie an mich senden</label></div>".
        "<span style=\"width:100%;display:inline-flex;\">".
        "<input id=\"ua_mailto_".$this->id."\" type=\"email\" multiple=\"true\" name=\"ua_mailto_".$this->id."\" list=\"ua_mail_list_".$this->id."\" disabled>".
        "<datalist id=\"ua_mail_list_".$this->id."\"></datalist>".
        "<a class=\"button\" onclick=\"".
          "var oReq_mail_".$this->id." = new XMLHttpRequest();".
          "oReq_mail_".$this->id.".addEventListener('loadend', function(e) { ".
            "if(oReq_mail_".$this->id.".status != 200) {".
              "alert('Versenden der eMail ist fehlgeschlagen.\\nGrund: ' + oReq_mail_".$this->id.".status + ' ' + oReq_mail_".$this->id.".statusText);".
            "} else { ".$this->saveLastEmail($this->id)." }});".
            "oReq_mail_".$this->id.".open('POST', 'mail_video.php', true);".
            "oReq_mail_".$this->id.".setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');".
            "oReq_mail_".$this->id.".send('mid=".$this->id."&msg='+document.getElementById('ua_mail_msg_".$this->id."').value+'&mailto='+encodeURI(document.getElementById('ua_mailto_".$this->id."').value)+".
              "'&bcc='+document.getElementById('ua_mail_bcc_".$this->id."').checked+'');".
        "\">Absenden</a></span></td></tr>").
      ($this->ui['admin'] ? "<tr><td>&nbsp;</td></tr>".
        "<tr><td>OMDB-Id:&nbsp;<input disabled name=\"ua_omdb_".$this->id."\" type=\"number\" min=\"1\" ".
        "oninput=\"document.getElementById('id_ua_cover_".$this->id."').setAttribute('src', 'omdb.php?cover-oid='+event.target.value); ".
        "var oReq_omdb_".$this->id." = new XMLHttpRequest(); ".
        "oReq_omdb_".$this->id.".addEventListener('loadend', function(e) { ".
          "if(oReq_omdb_".$this->id.".status != 200) {".
            "alert('Aktualisierung der OMDB-Id fehlgeschlagen.\\nGrund: ' + oReq_omdb_".$this->id.".status + ' ' + oReq_omdb_".$this->id.".statusText);".
          "}".
        "event.target.disabled=false; }); event.target.disabled=true;".
	"oReq_omdb_".$this->id.".open('GET', 'omdb.php?mid=".$this->id."&oid='+event.target.value+'');".
	"oReq_omdb_".$this->id.".send();".
        "\"".(!empty($this->omdb) ? " value=\"".$this->omdb."\"" : "")."></td></tr>".
        "<tr><td>&nbsp;</td></tr><tr><td><center><img id=\"id_ua_cover_".$this->id."\" class=\"ua_cover\" src=\"img/nocover.png\" data-src=\"".
        (empty($this->omdb) ? "img/nocover.png" : "omdb.php?cover-oid=".$this->omdb)."\"></center></td></tr>" : "").
      "<tr><td>&nbsp;</td></tr>".
      "<tr><td align=\"center\"><a class=\"button\" href=\"#close\" onclick=\"enableUserActions(".$this->id.", false)\">Fertig</a></td></tr>".
      "</table></center><script>".$this->script()."</script>";
  }
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
