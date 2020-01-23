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



  $type_product="";
        if($text=="เมนูกาแฟ"){
            $type_product="1";
          }else{
             $replyText["text"] = "กรุณาเลือกเมนูอีกรอบค่ะ";
          }


  $replyText1= [ 
    "type"=> "template",
  "altText"=> "this is a carousel template",
  "template"=> [
      "type"=> "carousel",
      "columns"=> [
          [
            "thumbnailImageUrl"=> "https://example.com/bot/images/item1.jpg",
            "imageBackgroundColor"=> "#FFFFFF",
            "title"=> "this is menu",
            "text"=> "description",
            "defaultAction"=> {
                "type"=> "uri",
                "label"=> "View detail",
                "uri"=> "http://example.com/page/123"
            ],
            "actions"=> [
                [
                    "type"=> "postback",
                    "label"=> "Buy",
                    "data"=> "action=buy&itemid=111"
                ],
                [
                    "type"=> "postback",
                    "label"=> "Add to cart",
                    "data"=> "action=add&itemid=111"
                ],
                [
                    "type"=> "uri",
                    "label"=> "View detail",
                    "uri"=> "http://example.com/page/111"
                ]
            ]
          ],
          [
            "thumbnailImageUrl"=> "https://example.com/bot/images/item2.jpg",
            "imageBackgroundColor"=> "#000000",
            "title"=> "this is menu",
            "text"=> "description",
            "defaultAction"=> [
                "type"=> "uri",
                "label"=> "View detail",
                "uri"=>"http://example.com/page/222"
            ],
            "actions"=> [
                [
                    "type"=> "postback",
                    "label"=> "Buy",
                    "data"=> "action=buy&itemid=222"
                ],
                [
                    "type"=> "postback",
                    "label"=> "Add to cart",
                    "data"=> "action=add&itemid=222"
                ],
                [
                    "type"=> "uri",
                    "label"=> "View detail",
                    "uri"=> "http://example.com/page/222"
                ]
            ]
          ]
      ],
      "imageAspectRatio": "rectangle",
      "imageSize": "cover"
  ]
  ];





  

  
  $lineData['URL'] = "https://api.line.me/v2/bot/message/reply";
  $lineData['AccessToken'] = "0EhBTTseT51jUDZTB2ExoXM+4VM59TybE8WoW6GdG7I9ugLQyQssBVyKuWw18GgvhVOXYLtJCbAwnamRdP10iFyFkpSIdlgskfDHONLWlJ/f9MB9IitlaOHZzIyGxDZgrDLiX+XXp/BOq+4SjJZe7AdB04t89/1O/w1cDnyilFU=";
  $replyJson["replyToken"] = $replyToken;
  $replyJson["messages"][0] = $replyText;
  
  if($text=="เมนูกาแฟ")
  {
    $replyJson["messages"][0] = $replyText1;
  }
   
  
  $encodeJson = json_encode($replyJson);
  $results = sendMessage($encodeJson,$lineData);
  echo $results;
  http_response_code(200);
?>
