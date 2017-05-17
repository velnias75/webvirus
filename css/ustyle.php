<?php

session_start();

$cols = array(
  'cat_0' => array("#3333ff", "#3333ff"),
  'cat_1' => array("black",   "black"),
  'cat_2' => array("#66339f", "#66339f"),
  'cat_3' => array("#253100", "#253100"),
  'cat_4' => array("#510000", "#510000"),
  'col1'  => array("#eeeeee", "#eeffee"),
  'col2'  => array("#ffffff", "#ffffff"),
  'col3'  => array("#606061", "#607161"),
  'col4'  => array("#3333ff", "#3344ff"),
  'col5'  => array("#7e7eff", "#7e8fff"),
  'col6'  => array("#cccccc", "#ccddcc"),
  'col7'  => array("#dddddd", "#ddeedd"),
  'col8'  => array("#bbbbbb", "#bbccbb"),
  'col9'  => array("#00d9ff", "#00eaff"),
  'col10' => array("#eeeeff", "#eeffff"),
  'col11' => array("#9d9dcf", "#9dbfcf"),
  'col12' => array("#ddddff", "#ddeeff"),
  'col13' => array("#ccccff", "#ccddff"),
  'col14' => array("blue",    "#0011ff"),
  'col15' => array("red",     "red"),
  'tgrad_moz' => array("-moz-linear-gradient(-45deg, #eeeeee 0%, #cccccc 100%)", 
    "-moz-linear-gradient(-45deg, #eeffee 0%, #cceecc 100%)"),
  'tgrad_wbk' => array("-webkit-linear-gradient(-45deg, #eeeeee 0%, #cccccc 100%)",
    "-webkit-linear-gradient(-45deg, #eeffee 0%, #cceecc 100%)"),
  'tgrad_std' => array("linear-gradient(135deg, #eeeeee 0%, #cccccc 100%)",
    "linear-gradient(135deg, #eeffee 0%, #cceecc 100%)"),
  'tgrad_fie' => array("progid:DXImageTransform.Microsoft.gradient( startColorstr='#eeeeee', endColorstr='#cccccc',GradientType=1 )",
    "progid:DXImageTransform.Microsoft.gradient( startColorstr='#effeee', endColorstr='#cceecc',GradientType=1 )")

);

$style = isset($_SESSION['ui']) ? $_SESSION['ui']['style'] : 0;

header("Content-Type: text/css");

echo ":root {".
  "--cat_0: ".$cols['cat_0'][$style].";".
  "--cat_1: ".$cols['cat_1'][$style].";".
  "--cat_2: ".$cols['cat_2'][$style].";".
  "--cat_3: ".$cols['cat_3'][$style].";".
  "--cat_4: ".$cols['cat_4'][$style].";".
  "--col1:  ".$cols['col1'][$style].";".
  "--col2:  ".$cols['col2'][$style].";".
  "--col3:  ".$cols['col3'][$style].";".
  "--col4:  ".$cols['col4'][$style].";".
  "--col5:  ".$cols['col5'][$style].";".
  "--col6:  ".$cols['col6'][$style].";".
  "--col7:  ".$cols['col7'][$style].";".
  "--col8:  ".$cols['col8'][$style].";".
  "--col9:  ".$cols['col9'][$style].";".
  "--col10: ".$cols['col10'][$style].";".
  "--col11: ".$cols['col11'][$style].";".
  "--col12: ".$cols['col12'][$style].";".
  "--col13: ".$cols['col13'][$style].";".
  "--col14: ".$cols['col14'][$style].";".
  "--col15: ".$cols['col15'][$style].";".
  "--tgrad_moz: ".$cols['tgrad_moz'][$style].";".
  "--tgrad_wbk: ".$cols['tgrad_wbk'][$style].";".
  "--tgrad_std: ".$cols['tgrad_std'][$style].";".
  "--tgrad_fie: ".$cols['tgrad_fie'][$style].";".
"}";

?>
