<?php
include("connectSql.php");

//On récupère les valeurs entrées par l'utilisateur :
$heure_creuse = $_POST['hc'];
$heure_pleine = $_POST['hp'];
                    
connectMaBase();
                    
//On prépare la commande sql d'insertion
$sql = 'INSERT INTO conso (hc, hp, date) VALUES ('.$heure_creuse.','.$heure_pleine.', now())';
                    
mysql_query ($sql) or die ('Erreur SQL : '.$sql.'<br />'.mysql_error()); 
                    
// on ferme la connexion
mysql_close();
echo("done");
?>
