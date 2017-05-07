<?php

$id_order = "";
$du_order = "";
$di_order = "";
$ti_order = "";

if(isset($_GET['order_by'])) {
  
  if($_GET['order_by'] === "ID") {
    $order = "`m`.`ID`";
    $id_order = "&nbsp;&#10037;";
  } else if($_GET['order_by'] === "duration") {
    $order = "`dur_sec` DESC, MAKE_MOVIE_SORTKEY(`ltitle`, `m`.`skey`)";
    $du_order = "&nbsp;&#10037;";
  } else if($_GET['order_by'] === "disc") {
    $order = "LEFT( `disc`.`name`, 1 ) ASC, LENGTH( `disc`.`name` ) ASC, `disc`.`name` ASC, MAKE_MOVIE_SORTKEY(`ltitle`, `m`.`skey`)";
    $di_order = "&nbsp;&#10037;";
  } else {
    $order = "MAKE_MOVIE_SORTKEY(`ltitle`, `m`.`skey`)";
    $ti_order = "&nbsp;&#10037;";
  }
  
} else {
  $order = "MAKE_MOVIE_SORTKEY(`ltitle`, `m`.`skey`)";
  $ti_order = "&nbsp;&#10037;";
}

require 'db_cred.php';

$mysqli = mysqli_connect($server, $user, $pass, $db);
mysqli_set_charset($mysqli, 'utf8');

if(mysqli_connect_errno($mysqli)) {
  
  echo "Failed to connect to MySQL: ".mysqli_connect_error();
  
} else {
  
  $dvd_choice = "SELECT `m`.`ID`, MAKE_MOVIE_TITLE(`m`.`title`, `m`.`comment`, `s`.`name`, `es`.`episode`, `s`.`prepend`) AS `ltitle`,
SEC_TO_TIME(m.duration) AS `duration`, `m`.`duration` AS `dur_sec`, IF(`languages`.`name` IS NOT NULL, TRIM(GROUP_CONCAT(`languages`.`name` ORDER BY `movie_languages`.`lang_id` DESC SEPARATOR ', ')), 'n. V.') as `lingos`, `disc`.`name` AS `disc`,`category` FROM `disc` AS `disc`, `movies` AS `m` LEFT JOIN `episode_series` AS `es` ON  `m`.`ID` =`es`.`movie_id` LEFT JOIN`series`AS `s` ON `s`.`id` = `es`.`series_id` LEFT JOIN `movie_languages` ON `m`.`ID` = `movie_languages`.`movie_id` LEFT JOIN `languages` ON `movie_languages`.`lang_id` = `languages`.`id` WHERE `disc`.`ID` = `m`.`disc` GROUP BY `m`.`ID`";

  $result = mysqli_query($mysqli, $dvd_choice."ORDER BY ".$order, MYSQLI_USE_RESULT);

  if($result) {
 
?>

  <table class="list" border="0">
    <tr id="list_header">
      <th class="hack"><a class="list" href="?order_by=ID">Nr<?php echo $id_order; ?></a></th>
      <th class="ltitle"><a class="list" href="?order_by=title">Titel<?php echo $ti_order; ?></a></th>
      <th class="duration"><a class="list" href="?order_by=duration">L&auml;nge<?php echo $du_order; ?></a></th>
      <th class="hack lingos">Sprache(n)</th>
      <th><a class="list" href="?order_by=disc">DVD<?php echo $di_order; ?></a></th>
    </tr>

<?php
  
  $par=1;
  
  while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr class=\"parity_".($par++ % 2)."\"><td nowrap class=\"list hack\" align=\"right\">".
	      htmlentities($row['ID'], ENT_SUBSTITUTE, "utf-8")."</td><td nowrap class=\"list cat_".$row['category']." ltitle\">".
	      htmlentities($row['ltitle'], ENT_SUBSTITUTE, "utf-8")."</td><td nowrap align=\"right\" class=\"list duration cat_".$row['category']."\">".
	      htmlentities($row['duration'], ENT_SUBSTITUTE, "utf-8")."</td><td nowrap class=\"list cat_".$row['category']." hack lingos\">".
	      htmlentities($row['lingos'], ENT_SUBSTITUTE, "utf-8")."</td><td nowrap class=\"list cat_".$row['category']."\">".
	      htmlentities($row['disc'], ENT_SUBSTITUTE, "utf-8")."</td></tr>\n";
  }
  
  echo "<tr class=\"list parity_".($par++ % 2)."\"><td class=\"list hack\" align=\"right\">&nbsp;</td><td class=\"list\">&nbsp;</td><td class=\"list\">&nbsp;</td><td class=\"list\">&nbsp;</td><td>&nbsp;</td class=\"list\"></tr>\n";
  
  $total_res = mysqli_query($mysqli, "SELECT CONCAT( IF( FLOOR( SUM( `dur_sec` ) / 3600 ) <= 99, RIGHT( CONCAT( '00', FLOOR( SUM( `dur_sec` ) / 3600 ) ), 2 ), FLOOR( SUM( `dur_sec` ) / 3600 ) ), ':', RIGHT( CONCAT( '00', FLOOR( MOD( SUM( `dur_sec` ), 3600 ) / 60 ) ), 2 ), ':', RIGHT( CONCAT( '00', MOD( SUM( `dur_sec` ), 60 ) ), 2 ) ) AS `tot_dur` FROM (".$dvd_choice.") AS `choice`");
  $total = mysqli_fetch_assoc($total_res);
  
  if($total_res && $total) {
  
    echo "<tr class=\"parity_".($par++ % 2)."\"><td nowrap class=\"list hack\" align=\"right\">".
	      mysqli_num_rows($result)."</td><td nowrap class=\"list\">Videos insgesamt</td><td align=\"right\" class=\"list duration\">".htmlentities($total['tot_dur'], ENT_SUBSTITUTE, "utf-8")."</td><td class=\"list\">&nbsp;</td><td class=\"list\">&nbsp;</td></tr>\n";
    
    mysqli_free_result($total_res);
	      
  }

?>

  </table>

<?php
    mysqli_free_result($result);
    
  } else {
    
    printf("Error: %s\n", mysqli_error($mysqli));
    
  }
  
  mysqli_close($mysqli);
}

?>
