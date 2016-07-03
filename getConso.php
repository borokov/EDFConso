<?php

$minDate = $_GET["minDate"];
$maxDate = $_GET["maxDate"];
$delta = $_GET["delta"];

include("connectSql.php");
//On se connecte
connectMaBase();

$sql = 'SELECT * FROM conso WHERE date BETWEEN \''.date("Y-m-d H:m:s", $minDate-$delta).'\' AND \''.date("Y-m-d H:m:s", $maxDate).'\' ORDER BY date ASC';  

$req = mysql_query($sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysql_error());
$data = mysql_fetch_array($req);
$prevConsoHC = $data['hc'];
$prevConsoHP = $data['hp'];
$prevConsoTot = $data['hc'] + $data['hp'];
$currentDate = strtotime($data['date']);
$prevDate = $currentDate;
$valueId = 0;

echo("[[null, \"HC\", \"HP\", \"Tot\"]\n");
while ($data = mysql_fetch_array($req))
{
  $currentDate = strtotime($data['date']);
  $currentDateMs = 1000*$currentDate;
  $currentConsoHC = $data['hc'];
  $currentConsoHP = $data['hp'];
  $currentConsoTot = $data['hc'] + $data['hp'];
  if ( $currentDate > $prevDate + $delta )
  {
    $deltaConsoHC = $currentConsoHC - $prevConsoHC; // watt heure
    $deltaConsoHP = $currentConsoHP - $prevConsoHP; // watt heure
    $deltaConsoTot = $deltaConsoHC + $deltaConsoHP; // watt heure
    $deltaDate = ($currentDate - $prevDate) / 3600.0; // hours
    echo(",");
    $valueId = $valueId + 1;
    echo("[".$currentDateMs.", ".round($deltaConsoHC/$deltaDate).", ".round($deltaConsoHP/$deltaDate).", ".round($deltaConsoTot/$deltaDate)."]\n");
    $prevConsoHC = $currentConsoHC;
    $prevConsoHP = $currentConsoHP;
    $prevConsoTot = $prevConsoHC + $prevConsoHP;
    $prevDate = $currentDate;
  }
}

// fill end of request with zeros
while ( $prevDate + $delta < $maxDate )
{
  $prevDateMS = 1000 * $prevDate;
  echo(",[".$prevDateMS.", 0, 0, 0]\n");
  $prevDate += $delta;
}
echo("\n]");

?>
