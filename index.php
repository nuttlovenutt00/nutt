<?php


  $LINEData = file_get_contents('php://input');
  $jsonData = json_decode($LINEData,true);
 echo $jsonData;
?>
