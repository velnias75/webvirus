<?php

require 'classes/pdf.php';

try {
  (new PDF(isset($_GET['order_by']) ? $_GET['order_by'] : "ltitle", 
    isset($_GET['from']) ? $_GET['from'] : 0,
    isset($_GET['to']) ? $_GET['to'] : PDF::pageSize(), isset($_GET['cat']) ? $_GET['cat'] : -1))->render();
} catch(Exception $e) {
  echo "<strong>Fehler:</strong> ".htmlentities($e->getMessage(), ENT_SUBSTITUTE, "utf-8");
}

?>
