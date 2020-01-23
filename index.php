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
  
  $sql = "SELECT * FROM type 
        left join type_product  on type.t_id_auto = type_product.tp_t_id
        where type.t_id_auto='1'        ";

  $a=[];
$b="";
  $result = $mysql->query($sql);
  $num=0;    
  while($row = $result->fetch_assoc()) 
    {
      $aa=$row["tp_id"];
      $sql2 = "SELECT * FROM menu 
      left join type_product  on menu.m_tp_id = type_product.tp_id
      where m_tp_id=  $aa      ";
      $result2 = $mysql->query($sql2);
      $numm=0;
      while($row2 = $result2->fetch_assoc()) 
        {
          $a[$numm]= $row2['m_name'].$row2['m_price'];
           $numm++;
        }
        
      $a2[$num]=
          [
            "thumbnailImageUrl"=>  "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT9WvUF2kYT0Rg316K9-4zMCvH2TkNvp15gK6SDQwfRLSQhbkDv&s",
            "imageBackgroundColor"=>  "#FFFFFF",
            "title"=>  $row["tp_name"],
            "text"=>  "กรุณาเลือกประเภทของกาแฟของท่าน ตามเมนูข้างล่างค่ะ",
           
            "actions"=>  [
                [
                    "type"=>  "message",
                    "label"=>  $a[0]." บาท",
                    "text"=>  "H001"
                ],
                [
                    "type"=>  "message",
                    "label"=>  $a[1]." บาท",
                    "text"=>  "C001"
                ],
                [
                    "type"=>  "message",
                    "label"=>  $a[2]." บาท",
                    "text"=>  "S001"
                ]
            ]
          ];
          $num++;
    }


  $replyText1= [ 
  "type"=> "template",
  "altText"=>  "this is a carousel template",
  "template"=>  [
      "type"=>  "carousel",
      "columns"=>  

      
      $a2
    
         
      ,
      "imageAspectRatio"=>  "rectangle",
      "imageSize"=>  "cover"
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
