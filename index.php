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
  $date=date("Y-m-d");
  $time=date("H:i:s");

   $mysql->query("INSERT INTO `LOG`(`UserID`, `replyToken`, `Text`, `Timestamp`, `date`, `time`) VALUES ('$userID','$replyToken','$text','$timestamp','$date','$time')");


   //กำหนดค่าของตัวแปร
  $replyText["type"] = "text";


  if($text=="สวัสดี")
  {
    $replyText["text"] = "สวัสดีค่ะ";
  }else{
    $replyText["text"] = "พูดใหม่อีกครั้งค่ะ";
  }




 $a=[
      "type"=> "flex",
  "altText"=> "Flex Message",
  "contents"=> [
    "type"=> "bubble",
    "body"=> [
      "type"=> "box",
      "layout"=> "vertical",
      "contents"=> [
        [
          "type"=> "text",
          "text"=> "รายการของฉัน",
          "size"=> "lg",
          "align"=> "start",
          "weight"=> "bold",
          "color"=> "#C8690E"
        ],
        [
          "type"=> "box",
          "layout"=> "vertical",
          "spacing"=> "sm",
          "margin"=> "lg",
          "contents"=> [
            [
              "type"=> "text",
              "text"=> "ดแไำดแไำดไำดไำดไำดไอดกเปหกดกหดหกดหกดหกหกดหกดดหก"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ],
            [
              "type"=> "text",
              "text"=> "Text"
            ]
          ]
        ]
      ]
    ],
    "footer"=> [
      "type"=> "box",
      "layout"=> "vertical",
      "flex"=> 0,
      "spacing"=> "sm",
      "contents"=> [
        [
          "type"=> "button",
          "action"=> [
            "type"=> "uri",
            "label"=> "CALL",
            "uri"=> "https=>//linecorp.com"
          ],
          "height"=> "sm",
          "style"=> "link"
        ]
      ]
    ]
  ]

 ];



  
  $lineData['URL'] = "https://api.line.me/v2/bot/message/reply";
  $lineData['AccessToken'] = "0EhBTTseT51jUDZTB2ExoXM+4VM59TybE8WoW6GdG7I9ugLQyQssBVyKuWw18GgvhVOXYLtJCbAwnamRdP10iFyFkpSIdlgskfDHONLWlJ/f9MB9IitlaOHZzIyGxDZgrDLiX+XXp/BOq+4SjJZe7AdB04t89/1O/w1cDnyilFU=";
  $replyJson["replyToken"] = $replyToken;
  $replyJson["messages"][0] = $replyText;
  $replyJson["messages"][1] = a;
   
  
  $encodeJson = json_encode($replyJson);
  $results = sendMessage($encodeJson,$lineData);
  echo $results;
  http_response_code(200);
?>
