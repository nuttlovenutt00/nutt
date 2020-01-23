<?php

  include("GetDataLine.php");
  include("ConnectSql.php.php");


 
 $mysql->query("INSERT INTO `LOG`(`UserID`, `Text`, `Timestamp`) VALUES ('$userID','$text','$timestamp')");

   $replyText["type"] = "text";



  $type_product="";
        if($text=="เมนูกาแฟ"){
            
          }else{
             $replyText["text"] = "กรุณาเลือกเมนูอีกรอบค่ะ";
          }
 





  

  
 $replyJson["messages"][0] = $replyText;
  
  if($text=="เมนูกาแฟ")
  {
    $replyJson["messages"][0] = $replyText1;
  }

   
?>
