<?php
error_reporting(E_ALL ^ E_DEPRECATED);
function connectMaBase(){
    $base = mysqli_connect ('localhost', 'user', 'password');  
    if (mysqli_connect_errno())
    {
      echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    mysqli_select_db ($base, 'EDF') ;

    return $base;
}
?>
