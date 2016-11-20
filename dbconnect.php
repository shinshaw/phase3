<?php
  $DBhost = "localhost";
  $DBuser = "root";
  $DBpass = "";
  $DBname = "erms";
  
  $DBconn = new MySQLi($DBhost,$DBuser,$DBpass,$DBname);
  
     if ($DBconn->connect_error) {
         die("DB Connection Failed : -> ".$DBconn->connect_error);
     } 
?>
 
