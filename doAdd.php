<?php
include("connectSql.php");
$base = connectMaBase();
// ex: http://myServer/EDF/doAdd.php?hc=55801068&hp=124984298

//On recupere les valeurs entrees par l'utilisateur :
$heure_creuse = $_POST['hc'];
$heure_pleine = $_POST['hp'];

// seelct last entry to perform sanity check
$sql = 'SELECT * FROM conso ORDER BY date DESC LIMIT 1';
$req = mysqli_query($base, $sql) or die('Erreur SQL !<br />'.$sql.'<br />'.mysqli_error($base));
$data = mysqli_fetch_array($req);
$lastHC = $data['hc'];
$lastHP = $data['hp'];

// sanity check because sometime values are currupted. Ensure monitored HC/HP is > last entry
// and < last entry + some amount
if ( ($lastHC <= $heure_creuse && $heure_creuse < $lastHC + 100000)
   && ($lastHP <= $heure_pleine && $heure_pleine < $lastHP + 100000) )
{
  //On prepare la commande sql d'insertion
  $sql = 'INSERT INTO conso (hc, hp, date) VALUES ('.$heure_creuse.','.$heure_pleine.', now())';

  mysqli_query($base, $sql) or die ('Erreur SQL : '.$sql.'<br />'.mysqli_error($base)); 
  echo("done");
}
else
{
//  echo("Corrupted value");
  echo("done");
}
// on ferme la connexion
mysqli_close($base);

?>
