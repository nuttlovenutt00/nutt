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

  //บันทึก Log ไฟล์
  date_default_timezone_set("Asia/Bangkok");
  $datetime=date("Y-m-d");
  $time=date("H:i:s");

   $mysql->query("INSERT INTO `LOG`(`UserID`, `replyToken`, `Text`, `Timestamp`, `datetime`) VALUES ('$userID','$replyToken','$text','$timestamp','$datetime')");



$replyText=[
"type"=> "flex",
  "altText"=> "Flex Message",
  "contents"=> [
    "type"=> "bubble",
    "direction"=> "ltr",
    "header"=> [
      "type"=> "box",
      "layout"=> "vertical",
      "contents"=> [
        [
          "type"=> "text",
          "text"=> "รายการของฉัน",
          "flex"=> 0,
          "size"=> "md",
          "align"=> "center",
          "weight"=> "bold",
          "color"=> "#94796D"
        ]
      ]
    ],
    "body"=>[
      "type"=> "box",
      "layout"=> "vertical",
      "contents"=> [
        [
          "type"=> "text",
          "text"=> "P1 เอสเพรชโซ่(ร้อน) จำนวน 1 แก้ว"
        ],
        [
          "type"=> "text",
          "text"=> "P2 มอคค่า(ร้อน) จำนวน 3 แก้ว"
        ],
        [
          "type"=> "text",
          "text"=> "P3 เอสเพรชโซ่(ปั่น) จำนวน 1 แก้ว"
        ],
        [
          "type"=> "text",
          "text"=> "P4 ขนมเค้กช็อกโกแล็ต จำนวน 2 ชิ้น"
        ]
      ]
    ],
    "footer"=> [
      "type"=>"box",
      "layout"=> "horizontal",
     "contents"=> [
        [
          "type"=> "button",
          "action"=> [
            "type"=> "message",
            "label"=> "ยืนยันการสั่ง",
            "text"=> "ยืนยันการสั่ง"
          ],
          "color"=> "#94796D",
          "height"=>"sm",
          "style"=> "primary"
        ]
      ]
    ]
  ]
];


  
  $lineData['URL'] = "https://api.line.me/v2/bot/message/reply";
  $lineData['AccessToken'] = "0EhBTTseT51jUDZTB2ExoXM+4VM59TybE8WoW6GdG7I9ugLQyQssBVyKuWw18GgvhVOXYLtJCbAwnamRdP10iFyFkpSIdlgskfDHONLWlJ/f9MB9IitlaOHZzIyGxDZgrDLiX+XXp/BOq+4SjJZe7AdB04t89/1O/w1cDnyilFU=";
  $replyJson["replyToken"] = $replyToken;
  $replyJson["messages"][0] = $replyText;
  

   
  
  $encodeJson = json_encode($replyJson);
  $results = sendMessage($encodeJson,$lineData);
  echo $results;
  http_response_code(200);
?>
