<?php


  $LINEData = file_get_contents('php://input');
  $jsonData = json_decode($LINEData,true);
  $replyToken = $jsonData["events"][0]["replyToken"];
  $userID = $jsonData["events"][0]["source"]["userId"];
  $text = $jsonData["events"][0]["message"]["text"];
  $timestamp = $jsonData["events"][0]["timestamp"];


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
  function sendMessage($replyJson, $sendInfo){
          $ch = curl_init($sendInfo["URL"]);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLINFO_HEADER_OUT, true);
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              'Content-Type: application/json',
              'Authorization: Bearer ' . $sendInfo["AccessToken"])
              );
          curl_setopt($ch, CURLOPT_POSTFIELDS, $replyJson);
          $result = curl_exec($ch);
          curl_close($ch);
    return $result;
  }
    $mysql->query("INSERT INTO `LOG`(`UserID`, `Text`, `Timestamp`) VALUES ('$userID','$text','$timestamp')");

   $replyText["type"] = "text";
  if($text=="เมนู")
  {
      $replyText1["type"] = "template";
  $replyText1["altText"] = "this is a confirm template";
  $replyText1["template"] = [
    "type" => "confirm",
        "text" => "เมนู ประเภทสินค้า",
        "actions" => [
               [
                  "type" => "message",
                  "label" => "กาแฟ",
                  "text" => "กาแฟ"
                ],
                [
                  "type" => "message",
                  "label" => "ชา",
                  "text" => "ชา"
                ],
                [
                  "type" => "message",
                  "label" => "โซดา",
                  "text" => "โซดา"
                ],
                [
                  "type" => "message",
                  "label" => "ขนมหวาน",
                  "text" => "ขนมหวาน"
                ]
              ]
  ];
  }else{
     $replyText["text"] = "กรุณาพิมพ์ เมนู";
  }
 

  



  

  
  $lineData['URL'] = "https://api.line.me/v2/bot/message/reply";
  $lineData['AccessToken'] = "0EhBTTseT51jUDZTB2ExoXM+4VM59TybE8WoW6GdG7I9ugLQyQssBVyKuWw18GgvhVOXYLtJCbAwnamRdP10iFyFkpSIdlgskfDHONLWlJ/f9MB9IitlaOHZzIyGxDZgrDLiX+XXp/BOq+4SjJZe7AdB04t89/1O/w1cDnyilFU=";
  $replyJson["replyToken"] = $replyToken;
 
  
  if($text=="เมนู")
  {
    $replyJson["messages"][0] = $replyText1;
  }else{
     $replyJson["messages"][0] = $replyText;
  }   
  
  $encodeJson = json_encode($replyJson);
  $results = sendMessage($encodeJson,$lineData);
  echo $results;
  http_response_code(200);
?>
