<?php

$minDate = $_GET["minDate"];
$maxDate = $_GET["maxDate"];
$delta = $_GET["delta"];


    include("connectSql.php");
    //On se connecte
    connectMaBase();

    $sql = 'SELECT * FROM conso WHERE date BETWEEN \''.date("Y-m-d h:m:s", $minDate).'\' AND \''.date("Y-m-d h:m:s", $maxDate).'\' ORDER BY date ASC';  
    $req = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
    $data = mysql_fetch_array($req);
    $prevConsoHC = $data['hc'];
    $prevConsoHP = $data['hp'];
    $prevDate = strtotime($data['date']);
    $valueId = 0;
    echo("[[null, \"HC\", \"HP\"]\n");
    while ($data = mysql_fetch_array($req))
    {
      $currentDate = strtotime($data['date']);
      $currentConsoHC = $data['hc'];
      $currentConsoHP = $data['hp'];
      if  ( $currentDate > $prevDate + $delta )
      {
        $deltaConsoHC = $currentConsoHC - $prevConsoHC; // w
        $deltaConsoHP = $currentConsoHP - $prevConsoHP; // w
        $currentDateMs = 1000*$currentDate;
        $deltaDate = ($currentDate - $prevDate) / 3600.0; // hours
          echo(",");
        $valueId = $valueId + 1;
        echo("[".$currentDateMs.", ".round($deltaConsoHC/$deltaDate).", ".round($deltaConsoHP/$deltaDate)."]\n");
        $prevConsoHC = $currentConsoHC;
        $prevConsoHP = $currentConsoHP;
        $prevDate = $currentDate;
      }
    }
    echo("\n]");

?>
