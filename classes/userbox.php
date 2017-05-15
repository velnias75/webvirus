<?php

require_once 'irenderable.php';

final class UserBox implements IRenderable {

  private $ui = null;

  function __construct($ui) {
    $this->ui = $ui;
  }
  
  public function render() {
  
    echo "<table class=\"cat_nav userbox\" border=\"0\" width=\"100%\">\n";
    echo "<form method=\"POST\" action=\"login.php\">\n";
    echo "<input type=\"hidden\" name=\"q\" value=\"".urlencode($_SERVER['QUERY_STRING'])."\">\n";
    echo "<tr><th class=\"cat_nav\">Benutzerbereich</th></tr>\n";
  
    if(is_null($this->ui) || isset($_SESSION['error'])) {

      if(isset($_SESSION['error'])) {
	echo "<tr><td nowrap><span class=\"red_text\">".
	htmlentities($_SESSION['error'], ENT_SUBSTITUTE, "utf-8")."</span></td></tr>\n";
	unset($_SESSION['error']);
      }

      echo "<tr><td nowrap><label>Login:&nbsp;<input type=\"text\" size=\"5\" name=\"login\"></label></td></tr>\n";
      echo "<tr><td nowrap><label>Passwort:&nbsp;<input type=\"password\" size=\"5\" name=\"pass\"></label></td></tr>\n";
      echo "<tr><td align=\"center\" nowrap><input type=\"submit\" name=\"btn[login]\" value=\"Einloggen\"></td></tr>\n";

    } else {
    
      echo "<tr><td align=\"center\">Willkommen ".
	htmlentities($this->ui['display_name'], ENT_SUBSTITUTE, "utf-8")."!</td></tr>\n";      
      echo "<tr><td align=\"center\" nowrap><input type=\"submit\" name=\"btn[logout]\" value=\"Ausloggen\"></td></tr>\n";
      echo "<tr><td align=\"center\" nowrap><hr></td></tr>\n";
      
      echo "<tr><td nowrap>Passwort &auml;ndern:</td></tr>\n";
      echo "<tr><td nowrap><label>Passwort:&nbsp;<input type=\"text\" size=\"5\" name=\"pass_chg\"></label></td></tr>\n";
      echo "<tr><td align=\"center\" nowrap><input type=\"submit\" name=\"btn[chg]\" value=\"&Auml;ndern\"></td></tr>\n";
      
      if($this->ui['admin']) {
	echo "<tr><td align=\"center\" nowrap><hr></td></tr>\n";
	echo "<tr><td nowrap>Benutzer anlegen:</td></tr>\n";
	echo "<tr><td nowrap><label>Name:&nbsp;<input type=\"text\" size=\"5\" name=\"display\"></label></td></tr>\n";
	echo "<tr><td nowrap><label>Login:&nbsp;<input type=\"text\" size=\"5\" name=\"login_new\"></label></td></tr>\n";
	echo "<tr><td nowrap><label>Passwort:&nbsp;<input type=\"text\" size=\"5\" name=\"pass_new\"></label></td></tr>\n";
	echo "<tr><td align=\"center\" nowrap><input type=\"submit\" name=\"btn[create]\" value=\"Anlegen\"></td></tr>\n";
      }
    }
    
    echo "</form>\n";
    
    if(!is_null($this->ui) && !isset($_SESSION['error']) && $this->ui['admin'] && MySQLBase::instance()->update_allowed()) {
      echo "<tr><td align=\"center\" nowrap><hr></td></tr>\n";
      echo "<tr><td><form action=\"update.php\" method=\"POST\" enctype=\"multipart/form-data\">";
      echo "<input type=\"hidden\" name=\"q\" value=\"".urlencode($_SERVER['QUERY_STRING'])."\">\n";
      echo "<label class=\"fileContainer\">Datenupdate: <input type=\"file\" name=\"dateiupload\"><input type=\"submit\" ".
	"name=\"btn[upload]\" accept=\"application/sql\"></label></form></td></tr>\n";
    }
    
    echo "</table>\n";
    
  }
}

?>
