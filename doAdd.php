<?php

// ex: curl --request POST http://myServer/EDF/doAdd.php?hc=55801068&hp=124984298&iinst=5

// Get inputs
// Add offset corresponding to last report before changing to linky (because linky reset to 0)
$heure_creuse = (int)$_POST['hc'] + 63830532;
$heure_pleine = (int)$_POST['hp'] + 133789932;
$iinst = (int)$_POST['iinst'];

//----------------------------------------------------------------------------
// INsert into InfluxDB

require __DIR__ . '/vendor/autoload.php';

# see https://docs.influxdata.com/influxdb/v1.7/introduction/getting-started/
# and https://github.com/influxdata/influxdb-php

$influxDBHost = "127.0.0.1";
$influxDBPort = 8086;

$client = new \InfluxDB\Client($influxDBHost, $influxDBPort);
$database = $client->selectDB('EDF');

// executing a query will yield a resultset object
$result = $database->query('select last(value), hc, hp from IINST');

// get the points from the resultset yields an array
$points = $result->getPoints()[0];

$lastEntryTime = strtotime($points['time']);

// Sanity check: Avoid corrupted values (if values are realy too different or too high),
// But authorize entry when last entry is more than 1 hour. This means arduino may have crashed and have been restarted
if ( ((int)$points['hc']  <= $heure_creuse && $heure_creuse <= (int)$points['hc'] + 10000
    && (int)$points['hp'] <= $heure_pleine && $heure_pleine <= (int)$points['hp'] + 10000
    && 0 <= $iinst && $iinst < 40)
    || (time() - $lastEntryTime > 3600) )
{
  // create an array of points
  $points = array(
    new \InfluxDB\Point(
      'IINST', // name of the measurement
      (int)$iinst, // the measurement value
      [],
      ['hc' => (int)$heure_creuse, 'hp' => (int)$heure_pleine] // optional additional fields
    )
  );

  // we are writing unix timestamps, which have a second precision
  $result = $database->writePoints($points, \InfluxDB\Database::PRECISION_SECONDS);
}
else
{
  error_log("InfluxDB: Corrupted values: lastHC: ".$points['hc']." hp: ".$heure_creuse." - lastHP: ".$points['hp']." hp:".$heure_pleine, 3, "/var/tmp/EDF-errors.log");
}

//----------------------------------------------------------------------------
// Legacy database

include("connectSql.php");
$base = connectMaBase();

$current_hour = date("H");

// select last entry to perform sanity check
$sql = 'SELECT * FROM conso ORDER BY date DESC LIMIT 1';
$req = mysqli_query($base, $sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysqli_error($base));
$data = mysqli_fetch_array($req);
$lastHC = $data['hc'];
$lastHP = $data['hp'];
$lastHour = date("H", strtotime($data['date']));
$lastEntryTime = strtotime($data['date']);

// sanity check because sometime values are currupted. Ensure monitored HC/HP is > last entry
// and < last entry + some amount
// Authorize entry when last entry is more than 1 hour. This means arduino may have crashed and have been restarted
if ( ($lastHC <= $heure_creuse && $heure_creuse < $lastHC + 100000) && ($lastHP <= $heure_pleine && $heure_pleine < $lastHP + 100000) 
   || (time() - $lastEntryTime > 3600) )
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
  error_log("MySQL: Corrupted values: lastHC: ".$lastHC." hp: ".$heure_creuse." - lastHP: ".$lastHP." hp:".$heure_pleine, 3, "/var/tmp/EDF-errors.log");
  echo("done");
}
// on ferme la connexion
mysqli_close($base);

?>
