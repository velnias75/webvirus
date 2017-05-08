<?php 
  require 'head.php'; 
  require 'classes/movies.php';
?>

<table border="0" width="100%">
  <tr><td id="layout_top" align="center" colspan="3">PLATZHALTER OBEN</td></tr>
  <tr><td id="layout_left" valign="top">PLATZHALTER<br>LINKS</td>
    <td id="layout_content" align="center">
      <?php 
	try {
	  (new Movies($_GET['order_by']))->render(); 
	} catch(Exception $e) {
	  echo "<strong>Fehler:</strong> ".htmlentities($e->getMessage(), ENT_SUBSTITUTE, "utf-8");
	}
      ?></td>
    <td id="layout_right" valign="top">PLATZHALTER<br>RECHTS</td></tr>
  <tr><td id="layout_bottom" align="center" colspan="3">PLATZHALTER UNTEN</td></tr>
</table>

<?php require 'foot.php'; ?>
