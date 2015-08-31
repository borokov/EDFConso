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
    $prevConso = $data['hc'];
    $prevDate = strtotime($data['date']);
    $valueId = 0;
    echo("{\n\"data\": [\n");
    while ($data = mysql_fetch_array($req))
    {
      $currentDate = strtotime($data['date']);
      $currentConso = $data['hc'];
      if  ( $currentDate > $prevDate + $delta )
      {
        $deltaConso = $currentConso - $prevConso; // w
        $currentDateMs = 1000*$currentDate;
        $deltaDate = ($currentDate - $prevDate) / 3600.0; // hours
        if ( $valueId > 0 )
        {
          echo(",");
        }
        $valueId = $valueId + 1;
        echo("[".$currentDateMs.", ".$deltaConso/$deltaDate."]\n");
        $prevConso = $currentConso;
        $prevDate = $currentDate;
      }
    }
    echo("\n]}");

?>
