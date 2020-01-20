<?php

$servername = "37.59.55.185";
  $username = "Z01XVlWSlA";
  $password = "ogqvLgVKmd";
  $dbname = "Z01XVlWSlA";
  $mysql = new mysqli($servername, $username, $password, $dbname);
  mysqli_set_charset($mysql, "utf8");

  if ($mysql->connect_error){
  $errorcode = $mysql->connect_error;
  print("MySQL(Connection)> ".$errorcode);
  }

  $mysql->query("INSERT INTO `user`(`id`, `pass`, `name`,tel) VALUES ('111','222','333','333')");

  $getUser = $mysql->query("SELECT * FROM `user` ");
 
    while($row = $getUser->fetch_assoc()){
     echo  $row['id']."<br>";
    }
 
 

?>
