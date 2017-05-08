<?php 
  require 'head.php'; 
  require 'classes/movies.php';
?>

<table id="layout" border="0" width="100%">
  <tr><td id="layout_top" valign="center" align="center" colspan="3">
    <h1>Heikos Schrott- &amp; Rentnerfilme</h1><h3>&#9995;&nbsp;Die&nbsp;Webvirenversion&nbsp;&#9995;</h3></td></tr>
  <tr><td id="layout_left" valign="top">&nbsp;</td>
    <td id="layout_content" align="center" valign="top">
      <?php 
	try {
	  (new Movies(isset($_GET['order_by']) ? $_GET['order_by'] : "ltitle", 
	    isset($_GET['from']) ? $_GET['from'] : 0,
	    isset($_GET['to']) ? $_GET['to'] : -1))->render(); 
	} catch(Exception $e) {
	  echo "<strong>Fehler:</strong> ".htmlentities($e->getMessage(), ENT_SUBSTITUTE, "utf-8");
	}
      ?>
      </td>
    <td id="layout_right" valign="top">&nbsp;</td></tr>
  <tr><td id="layout_bottom" valign="center" align="center" colspan="3">
    <small>&copy;&nbsp;<?php echo strftime("%Y"); ?>&nbsp;by Heiko Sch&auml;fer (WORK IN PROGRESS)</small></td></tr>
</table>

<?php require 'foot.php'; ?>
