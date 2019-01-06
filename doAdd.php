<?php
// return time of day floored to 5s
function getTimeOfDay5s()
{
  $timesamp = time();
  $timesamp = (int)floor((float)$timesamp / 5.) * 5;
  
  return date("Y-m-d H:i:s",  $timesamp);
}

include("connectSql.php");
$base = connectMaBase();
// ex: curl --request POST http://myServer/EDF/doAdd.php?hc=55801068&hp=124984298&I=5

// Get inputs
// Add offset corresponding to last report before changing to linky (because linky reset to 0)
$heure_creuse = $_POST['hc'] + 63830532;
$heure_pleine = $_POST['hp'] + 133789932;
$IInst = $POST['I'];

$current_hour = date("H");

// select last entry to perform sanity check
$sql = 'SELECT * FROM conso ORDER BY date DESC LIMIT 1';
$req = mysqli_query($base, $sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysqli_error($base));
$data = mysqli_fetch_array($req);
$lastHC = $data['hc'];
$lastHP = $data['hp'];
$lastHour = date("H", strtotime($data['date']));

// sanity check because sometime values are currupted. Ensure monitored HC/HP is > last entry
// and < last entry + some amount
if ( ($lastHC <= $heure_creuse && $heure_creuse < $lastHC + 100000)
   && ($lastHP <= $heure_pleine && $heure_pleine < $lastHP + 100000) )
{
  // If measure correspond to a new hour, insert value in database
  if ( $lastHour != $current_hour )
  {
    // Just ensure second are really 0. Makes database cleaner.
    $datetime = date("Y-m-d H:i:00");
    $sql = 'INSERT INTO conso (hc, hp, date) VALUES ('.$heure_creuse.','.$heure_pleine.', "'.$datetime.'")';
    mysqli_query($base, $sql) or die ('Erreur SQL : '.$sql.'<br />'.mysqli_error($base));
  }
  echo("done");
}
else
{
  echo("Corrupted value: ".$lastHC." ".$heure_creuse." - ".$lastHP." ".$heure_pleine);
  echo("done");
}
// on ferme la connexion
mysqli_close($base);

?>
