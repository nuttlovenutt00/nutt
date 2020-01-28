<?php

  //รับค่าจาก line
  $LINEData = file_get_contents('php://input');
  $jsonData = json_decode($LINEData,true);
  $replyToken = $jsonData["events"][0]["replyToken"];
  $userID = $jsonData["events"][0]["source"]["userId"];
  $text = $jsonData["events"][0]["message"]["text"];
  $timestamp = $jsonData["events"][0]["timestamp"];

  //เชื่อมต่อฐานข้อมูล
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


  //ฟังก์ชั่นการส่งข้อมูลไปหา Line
  $lineData['URL'] = "https://api.line.me/v2/bot/message/reply";
  $lineData['AccessToken'] = "0EhBTTseT51jUDZTB2ExoXM+4VM59TybE8WoW6GdG7I9ugLQyQssBVyKuWw18GgvhVOXYLtJCbAwnamRdP10iFyFkpSIdlgskfDHONLWlJ/f9MB9IitlaOHZzIyGxDZgrDLiX+XXp/BOq+4SjJZe7AdB04t89/1O/w1cDnyilFU=";
  $replyJson["replyToken"] = $replyToken;

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


  //ตั้งค่า วันที่ เวลา
  date_default_timezone_set("Asia/Bangkok");
  $datetime=date("Y-m-d");
  $time=date("H:i:s");

   //บันทึก Log ไฟล์
   $mysql->query("INSERT INTO `LOG`(`UserID`, `replyToken`, `Text`, `Timestamp`, `date`, `time`) VALUES ('$userID','$replyToken','$text','$timestamp','$datetime','$time')");




  if($text=="เมนูแนะนำ"){

    $numl_ProHot=0;  
    $sql_ProHot = "SELECT * FROM ProductHot as a
        left join Product as b  on a.PHPId = b.PId
        where PHStatus= 'เปิดใช้งาน'";
    $result_ProHot = $mysql->query($sql_ProHot);
     if( $result_ProHot->num_rows > 0)
    {
        while($row_ProHot = $result_ProHot->fetch_assoc()) 
          {

            $ProHot[$numl_ProHot]=[

                "thumbnailImageUrl"=> $row_ProHot["PHPic"],
                "title"=> $row_ProHot["PName"],
                "text"=> "วิธีการสั่ง พิมพ์ ".$row_ProHot["PId"]."@จำนวนที่ต้องการ",
                "actions"=> [
                  [
                    "type"=> "message",
                    "label"=> "Code : ".$row_ProHot["PId"],
                    "text"=> " "
                  ]
                ]
              
            ];
             $numl_ProHot++;
          }
          $ord=[
          "type"=> "template",
          "altText"=> "this is a carousel template",
          "template"=> [
            "type"=> "carousel",
            "actions"=> [],
            "columns"=> $ProHot
          ]
        ];
        $replyJson["messages"][0] = $ord;
    }else{
      $replyText_ProHot["type"] = "text";
      $replyText_ProHot["text"] = "ตอนนี้ไม่มีเมนูแนะนำค่ะ";
      $replyJson["messages"][0] = $replyText_ProHot;

      $replySticker_ProHot=[
        "type"=> "sticker",
        "packageId"=> "52002755",
        "stickerId"=> "52002755"
      ];
      $replyJson["messages"][1] = $replySticker_ProHot;
    }

    
  }

  
  
  

   
  //ส่งค่าทั้งหมดกลับไปหา Line
  $encodeJson = json_encode($replyJson);
  $results = sendMessage($encodeJson,$lineData);
  echo $results;
  http_response_code(200);
?>
