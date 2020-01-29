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




  if($text!="" && $text!="เมนูแนะนำ"  && $text!="รายการของฉัน"  && $text!="ช่วยเหลือ")
  {

     $message = strtoupper($text);//แปลงเป็นตัวพิมพ์ใหญ่

      $a= strpos( $message, "@" );
        $b =substr($message,$a+1);

        //หาข้อความเพิ่มเติม
       if(strpos( $b, "@" ) != FALSE){
          //หาข้อความเพิ่มเติม
          $d=strpos( $b, "@" );
          $e =substr($b,$d+1);
           //หาจำนวน
          $f =iconv_substr($b,0,$d,"UTF-8");
       }else{
          //หาจำนวน
          $e="ไม่มี";
          $f = $b;
       }
        $c =iconv_substr($message,0,$a,"UTF-8");

        $chkpro="";
        //ตรวจสอบรหัสสินค้าในฐานข้อมูล
        $sql_SPro = "SELECT PAutoId,PName,UName FROM  Product as a
          left join Unit as b on a.PUnit = b.UId
          where PId= '$c' ";
        $result_SPro = $mysql->query($sql_SPro);
         if($result_SPro->num_rows > 0)
        {
          $chkpro="yes";
        }else{
           $chkpro="no";
        }


      if(strpos( $message, "P" )== 0  && strpos( $message, "P" ) !== FALSE && strpos( $message, "@" ) !== FALSE &&  is_numeric($f) && $chkpro=="yes"){

          $array_SPro=$result_SPro->fetch_assoc();
          $namePro=$array_SPro["PName"];
          $nameProUnit=$array_SPro["UName"];

          $replyText_sp=[
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
                      "text"=> "รับออเดอร์ลูกค้า เรียบร้อย",
                      "size"=> "sm",
                      "align"=> "start",
                      "weight"=> "bold",
                      "color"=> "#6E422D"
                    ],
                    [
                      "type"=> "text",
                      "text"=> "Text",
                      "size"=> "xxs",
                      "color"=> "#FFFFFF"
                    ],
                    [
                      "type"=> "separator"
                    ],
                    [
                      "type"=> "text",
                      "text"=> "Text",
                      "size"=> "xxs",
                      "color"=> "#FFFFFF"
                    ],
                    [
                      "type"=> "text",
                      "text"=> "รหัสสินค้า : ".$c,
                      "size"=> "sm",
                      "color"=> "#000000"
                    ],
                    [
                      "type"=> "text",
                      "text"=> "ชื่อสินค้า : ".$namePro,
                      "size"=> "sm",
                      "color"=> "#000000"
                    ],
                    [
                      "type"=> "text",
                      "text"=> "จำนวน : ".$f." ".$nameProUnit,
                      "size"=> "sm",
                      "color"=> "#000000"
                    ],
                    [
                      "type"=> "text",
                      "text"=> "ข้อความเพิ่มเติม : ".$e,
                      "size"=> "sm",
                      "color"=> "#000000"
                    ]
                  ]
                ],
                "footer"=> [
                  "type"=> "box",
                  "layout"=> "vertical",
                  "contents"=> [
                    [
                      "type"=> "button",
                      "action"=> [
                        "type"=> "message",
                        "label"=> "แสดงรายการทั้งหมด",
                        "text"=> "รายการของฉัน"
                      ],
                      "color"=> "#6E422D",
                      "height"=> "sm",
                      "style"=> "primary"
                    ],
                    [
                      "type"=> "text",
                      "text"=> "Text",
                      "size"=> "xxs",
                      "color"=> "#FFFFFF"
                    ],
                    [
                      "type"=> "button",
                      "action"=> [
                        "type"=> "message",
                        "label"=> "ยืนยันการสั่ง",
                        "text"=> "ยืนยันการสั่ง"
                      ],
                      "color"=> "#11B000",
                      "height"=> "sm",
                      "style"=> "primary"
                    ]
                  ]
                ]
              ]
          ];
          $replyJson["messages"][0] = $replyText_sp;

      }elseif(strpos( $message, "P" )== 0  && strpos( $message, "P" ) !== FALSE && strpos( $message, "@" ) !== FALSE &&  is_numeric($f) && $chkpro=="no")
      {
          $replyText_sp["type"] = "text";
          $replyText_sp["text"] = "ไม่พบรหัสสินค้า : ".$c." นี้ในฐานข้อมูลค่ะ";
          $replyJson["messages"][0] = $replyText_sp;

          $replySticker_sp=[
            "type"=> "sticker",
            "packageId"=> "11538",
            "stickerId"=> "51626522"
          ];
          $replyJson["messages"][1] = $replySticker_sp;

      }elseif(strpos( $message, "@" ) !== FALSE &&  is_numeric($f) && $chkpro=="no")
      {
          $replyText_sp["type"] = "text";
          $replyText_sp["text"] = "ไม่พบรหัสสินค้า : ".$c." นี้ในฐานข้อมูลค่ะ";
          $replyJson["messages"][0] = $replyText_sp;

          $replySticker_sp=[
            "type"=> "sticker",
            "packageId"=> "11538",
            "stickerId"=> "51626522"
          ];
          $replyJson["messages"][1] = $replySticker_sp;

      }else{
         $replyText_sp=[
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
                    "text"=> "คุณพิมพ์รูปแบบการสั่งไม่ถูกต้องค่ะ!",
                    "size"=> "sm",
                    "align"=> "center",
                    "weight"=> "bold",
                    "color"=> "#FF0000"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "Text",
                    "size"=> "xxs",
                    "color"=> "#FFFFFF"
                  ],
                  [
                    "type"=> "separator"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "Text",
                    "size"=> "xxs",
                    "color"=> "#FFFFFF"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "วิธีสั่งเมนู",
                    "size"=> "sm",
                    "weight"=> "bold",
                    "color"=> "#000000"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "พิมพ์ รหัสสินค้า@จำนวนที่ต้องการ  ",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "เช่น P123@2",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "Text",
                    "size"=> "xxs",
                    "color"=> "#FFFFFF"
                  ],
                  [
                    "type"=> "separator"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "Text",
                    "size"=> "xxs",
                    "color"=> "#FFFFFF"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "ถ้าต้องการพิมพ์ข้อความเพิ่มเติม",
                    "size"=> "sm",
                    "weight"=> "bold",
                    "color"=> "#000000"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "พิมพ์ รหัสสินค้า@จำนวน@ข้อความ",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "เช่น P123@2@หวานน้อย",
                    "size"=> "sm"
                  ]
                ]
              ]
            ]
          ];
          $replyJson["messages"][0] = $replyText_sp;
      }




  }elseif($text=="เมนูแนะนำ"){

    $numl_ProHot=0;  
    $sql_ProHot = "SELECT PHPic,PName,PId FROM ProductHot as a
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
      $replyText_ProHot["text"] = "ตอนนี้ทางร้านยังไม่มีเมนูแนะนำค่ะ รบกวนดูในริชเมนูไปก่อนนะคะ";
      $replyJson["messages"][0] = $replyText_ProHot;

      $replySticker_ProHot=[
        "type"=> "sticker",
        "packageId"=> "11537",
        "stickerId"=> "52002755"
      ];
      $replyJson["messages"][1] = $replySticker_ProHot;
    }

    
  }elseif($text=="ช่วยเหลือ")
  {
     $reply_help=[
        "type"=> "flex",
  "altText"=> "Flex Message",
  "contents"=> [
    "type"=> "carousel",
    "contents"=> [
      [
        "type"=> "bubble",
        "direction"=> "ltr",
        "header"=> [
          "type"=> "box",
          "layout"=> "vertical",
          "contents"=> [
            [
              "type"=> "text",
              "text"=> "คุณพิมพ์รูปแบบการสั่งไม่ถูกต้องค่ะ!",
              "size"=> "sm",
              "align"=> "center",
              "weight"=> "bold",
              "color"=> "#FF0000"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "separator"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "text",
              "text"=> "วิธีสั่งเมนู",
              "size"=> "sm",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "text",
              "text"=> "พิมพ์ รหัสสินค้า@จำนวนที่ต้องการ  ",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "เช่น P123@2",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "separator"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "text",
              "text"=> "ถ้าต้องการพิมพ์ข้อความเพิ่มเติม",
              "size"=> "sm",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "text",
              "text"=> "พิมพ์ รหัสสินค้า@จำนวน@ข้อความ",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "เช่น P123@2@หวานน้อย",
              "size"=> "sm"
            ]
          ]
        ]
      ],
      [
        "type"=> "bubble",
        "direction"=> "ltr",
        "header"=> [
          "type"=> "box",
          "layout"=> "vertical",
          "contents"=> [
            [
              "type"=> "text",
              "text"=> "คุณพิมพ์รูปแบบการสั่งไม่ถูกต้องค่ะ!",
              "size"=> "sm",
              "align"=> "center",
              "weight"=> "bold",
              "color"=> "#FF0000"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "separator"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "text",
              "text"=> "วิธีสั่งเมนู",
              "size"=> "sm",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "text",
              "text"=> "พิมพ์ รหัสสินค้า@จำนวนที่ต้องการ  ",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "เช่น P123@2",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "separator"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "text",
              "text"=> "ถ้าต้องการพิมพ์ข้อความเพิ่มเติม",
              "size"=> "sm",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "text",
              "text"=> "พิมพ์ รหัสสินค้า@จำนวน@ข้อความ",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "เช่น P123@2@หวานน้อย",
              "size"=> "sm"
            ]
          ]
        ]
      ],
      [
        "type"=> "bubble",
        "direction"=> "ltr",
        "header"=> [
          "type"=> "box",
          "layout"=> "vertical",
          "contents"=> [
            [
              "type"=> "text",
              "text"=> "คุณพิมพ์รูปแบบการสั่งไม่ถูกต้องค่ะ!",
              "size"=> "sm",
              "align"=> "center",
              "weight"=> "bold",
              "color"=> "#FF0000"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "separator"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "text",
              "text"=> "วิธีสั่งเมนู",
              "size"=> "sm",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "text",
              "text"=> "พิมพ์ รหัสสินค้า@จำนวนที่ต้องการ  ",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "เช่น P123@2",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "separator"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "text",
              "text"=> "ถ้าต้องการพิมพ์ข้อความเพิ่มเติม",
              "size"=> "sm",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "text",
              "text"=> "พิมพ์ รหัสสินค้า@จำนวน@ข้อความ",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "เช่น P123@2@หวานน้อย",
              "size"=> "sm"
            ]
          ]
        ]
      ],
      [
        "type"=> "bubble",
        "direction"=> "ltr",
        "header"=> [
          "type"=> "box",
          "layout"=> "vertical",
          "contents"=> [
            [
              "type"=> "text",
              "text"=> "คุณพิมพ์รูปแบบการสั่งไม่ถูกต้องค่ะ!",
              "size"=> "sm",
              "align"=> "center",
              "weight"=> "bold",
              "color"=> "#FF0000"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "separator"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "text",
              "text"=> "วิธีสั่งเมนู",
              "size"=> "sm",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "text",
              "text"=> "พิมพ์ รหัสสินค้า@จำนวนที่ต้องการ  ",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "เช่น P123@2",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "separator"
            ],
            [
              "type"=> "text",
              "text"=> "Text",
              "size"=> "xxs",
              "color"=> "#FFFFFF"
            ],
            [
              "type"=> "text",
              "text"=> "ถ้าต้องการพิมพ์ข้อความเพิ่มเติม",
              "size"=> "sm",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "text",
              "text"=> "พิมพ์ รหัสสินค้า@จำนวน@ข้อความ",
              "size"=> "sm"
            ],
            [
              "type"=> "text",
              "text"=> "เช่น P123@2@หวานน้อย",
              "size"=> "sm"
            ]
          ]
        ]
      ]
    ]
  ]
      ];
      $replyJson["messages"][0] = $reply_help;
  }

  
  
  

   
  //ส่งค่าทั้งหมดกลับไปหา Line
  $encodeJson = json_encode($replyJson);
  $results = sendMessage($encodeJson,$lineData);
  echo $results;
  http_response_code(200);
?>
