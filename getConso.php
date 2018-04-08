<?php

$minDate = $_GET["minDate"];
$maxDate = $_GET["maxDate"];
$delta = $_GET["delta"];

include("connectSql.php");
//On se connecte
$base = connectMaBase();

$sql = 'SELECT * FROM conso WHERE date BETWEEN \''.date("Y-m-d H:m:s", $minDate-$delta).'\' AND \''.date("Y-m-d H:m:s", $maxDate).'\' ORDER BY date ASC';  

$req = mysqli_query($base, $sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysqli_error($base));
$data = mysqli_fetch_array($req);
$prevConsoHC = $data['hc'];
$prevConsoHP = $data['hp'];
$prevConsoTot = $data['hc'] + $data['hp'];
$currentDate = strtotime($data['date']);
$prevDate = $currentDate;
$valueId = 0;

echo("[[null, \"HC\", \"HP\", \"Tot\"]\n");
while ($data = mysqli_fetch_array($req))
{
  $currentDate = strtotime($data['date']);
  $currentConsoHC = $data['hc'];
  $currentConsoHP = $data['hp'];
  $currentConsoTot = $data['hc'] + $data['hp'];
  if ( $currentDate > $prevDate + $delta )
  {
    $deltaConsoHC = $currentConsoHC - $prevConsoHC; // watt heure
    $deltaConsoHP = $currentConsoHP - $prevConsoHP; // watt heure
    $deltaConsoTot = $deltaConsoHC + $deltaConsoHP; // watt heure
    echo(",");
    $valueId = $valueId + 1;
    $prevDateMs = 1000*$prevDate;
    echo("[".$prevDateMs.", ".number_format($deltaConsoHC/1000, 2, '.', '').", ".number_format($deltaConsoHP/1000, 2, '.', '').", ".number_format($deltaConsoTot/1000, 2, '.', '')."]\n");
    
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
