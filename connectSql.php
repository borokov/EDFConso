<?php
function connectMaBase(){
    $base = mysql_connect ('localhost', 'usrName', 'password');  
    mysql_select_db ('dbName', $base) ;
}
?>
