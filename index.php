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

   $mysql->query("INSERT INTO `LOG`(`UserID`, `replyToken`, `Text`, `Timestamp`, `date`, `time`) VALUES ('$userID','$replyToken','$text','$timestamp','$date','$time')");



$order=[ 
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
          "color"=> "#905C44"
        ],
        [
          "type"=> "separator"
        ],
        [
          "type"=> "box",
          "layout"=> "vertical",
          "spacing"=> "sm",
          "margin"=> "lg",
          "contents"=> [
            [
              "type"=> "text",
              "text"=> "P1:เอสเพรชโซ่(ร้อน) 1 แก้ว",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "text",
              "text"=> "P2:มอคค่า(ร้อน)  3 แก้ว",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "text",
              "text"=> "P3:เอสเพรชโซ่(ปั่น)  1 แก้ว",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "text",
              "text"=> "P4:ขนมเค้กช็อกโกแล็ต  2 ชิ้น",
              "weight"=> "bold",
              "color"=> "#000000"
            ],
            [
              "type"=> "spacer"
            ]
          ]
        ],
        [
          "type"=> "separator"
        ],
        [
          "type"=> "text",
          "text"=> "Text",
          "size"=> "xxs",
          "color"=>"#FFFFFF"
        ],
        [
          "type"=> "text",
          "text"=> "ยกเลิกเมนู พิมพ์ รหัสสินค้า+0 เช่น P1+0",
          "size"=> "xs",
          "color"=> "#000000"
        ],
        [
          "type"=> "text",
          "text"=> "แก้ไขจำนวน พิมพ์ รหัสสินค้า+จำนวนที่ต้องการ",
          "size"=> "xs",
          "color"=> "#000000"
        ]
      ]
    ],
    "footer"=> [
      "type"=> "box",
      "layout"=> "vertical",
      "spacing"=> "sm",
      "contents"=> [
        [
          "type"=> "button",
          "action"=> [
            "type"=> "message",
            "label"=> "ยืนยันการสั่ง",
            "text"=> "ยืนยันการสั่ง"
          ],
          "color"=> "#905C44",
          "height"=> "sm",
          "style"=> "primary"
        ]
      ]
    ]
  ]
];


$re1=[
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
          "text"=> "ใบเสร็จรับเงิน",
          "size"=> "sm",
          "align"=> "start",
          "weight"=> "bold",
          "color"=> "#6E422D"
        ],
        [
          "type"=> "text",
          "text"=> "฿ 500.00",
          "size"=> "xxl",
          "weight"=> "bold",
          "color"=> "#000000"
        ],
        [
          "type"=> "text",
          "text"=> "#ORD202001-1",
          "size"=> "sm",
          "weight"=> "bold",
          "color"=> "#000000"
        ],
        [
          "type"=> "text",
          "text"=> "2020-01-28  10:18",
          "size"=> "xs",
          "color"=> "#929292"
        ],
        [
          "type"=> "text",
          "text"=> "Text",
          "color"=> "#FFFFFF"
        ],
        [
          "type"=> "text",
          "text"=> "การชำระเงินเสร็จสิ้น ขอบคุณที่ใช้บริการ",
          "size"=> "xs",
          "weight"=> "bold",
          "color"=> "#000000"
        ],
        [
          "type"=> "text",
          "text"=> "Text",
          "color"=> "#FFFFFF"
        ],
        [
          "type"=> "separator"
        ]
      ]
    ],
    "footer"=> [
      "type"=> "box",
      "layout"=> "horizontal",
      "contents"=> [
        [
          "type"=> "button",
          "action"=> [
            "type"=> "message",
            "label"=> "ดูเพิ่มเติม",
            "text"=> "แสดงใบเสร็จรับเงินเพิ่มเติม"
          ],
          "color"=> "#6E422D",
          "height"=> "sm",
          "style"=> "primary"
       ]
      ]
    ]
  ]

];

$re2=[
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
          "text"=> "ใบเสร็จรับเงิน",
          "size"=> "sm",
          "weight"=> "bold",
          "color"=> "#6E422D"
        ],
        [
          "type"=> "separator"
        ],
        [
          "type"=> "text",
          "text"=> "Text",
          "size"=> "xs",
          "color"=> "#FFFFFF"
        ],
        [
          "type"=> "text",
          "text"=> "#ORD202001-1",
          "size"=> "xl",
          "weight"=> "bold",
          "color"=> "#000000"
        ],
        [
          "type"=> "text",
          "text"=> "2020-01-28  10:18",
          "size"=> "xs"
        ],
        [
          "type"=> "text",
          "text"=> "Text",
          "size"=> "xxs",
          "color"=> "#FFFFFF"
        ],
        [
          "type"=> "separator"
        ]
      ]
    ],
    "body"=> [
      "type"=> "box",
      "layout"=> "vertical",
      "contents"=> [
        [
          "type"=> "box",
          "layout"=> "vertical",
          "spacing"=> "sm",
          "contents"=> [
            [
              "type"=> "box",
              "layout"=> "baseline",
              "spacing"=> "sm",
              "contents"=> [
                [
                  "type"=> "text",
                  "text"=> "P1:เอสเพรชโซ่(ร้อน)",
                  "margin"=> "xxl",
                  "size"=> "xs",
                  "weight"=> "bold",
                  "color"=> "#000000"
                ],
                [
                  "type"=> "text",
                  "text"=> "45.00 (2)",
                  "margin"=> "xs",
                  "size"=> "xs",
                  "align"=> "end",
                  "weight"=> "bold",
                  "color"=> "#6E422D"
                ]
              ]
            ],
            [
              "type"=> "box",
              "layout"=> "baseline",
              "contents"=> [
                [
                  "type"=> "text",
                  "text"=> "P2:มอคค่า(ร้อน)",
                  "size"=> "xs",
                  "weight"=> "bold",
                  "color"=> "#000000"
                ],
                [
                  "type"=> "text",
                  "text"=> "45.00 (1)",
                  "size"=> "xs",
                  "align"=> "end",
                  "weight"=> "bold",
                  "color"=> "#6E422D"
                ]
              ]
            ]
          ]
        ],
        [
          "type"=> "text",
          "text"=> "text",
          "size"=> "xs",
          "color"=> "#FFFDFD"
        ],
        [
          "type"=> "separator"
        ],
        [
          "type"=> "text",
          "text"=> "Text",
          "color"=> "#FFFFFF"
        ],
        [
          "type"=> "box",
          "layout"=> "vertical",
          "spacing"=> "sm",
          "contents"=> [
            [
              "type"=> "box",
              "layout"=> "baseline",
              "spacing"=> "sm",
              "contents"=> [
                [
                  "type"=> "text",
                  "text"=> "จำนวน",
                  "margin"=> "xxl",
                  "size"=> "xs",
                  "weight"=> "bold",
                  "color"=> "#000000"
                ],
                [
                  "type"=> "text",
                  "text"=> "3",
                  "size"=> "xs",
                  "align"=> "end",
                  "weight"=> "bold",
                  "color"=> "#6E422D"
                ]
              ]
            ],
            [
              "type"=> "box",
              "layout"=> "baseline",
              "spacing"=> "sm",
              "contents"=> [
                [
                  "type"=> "text",
                  "text"=> "ราคาสุทธิ",
                  "margin"=> "xxl",
                  "size"=> "xs",
                  "align"=> "start",
                  "weight"=> "bold",
                  "color"=> "#000000"
                ],
                [
                  "type"=> "text",
                  "text"=> "135.00",
                  "size"=> "xs",
                  "align"=> "end",
                  "weight"=> "bold",
                  "color"=> "#6E422D"
                ]
              ]
            ],
            [
              "type"=> "box",
              "layout"=> "baseline",
              "contents"=> [
                [
                  "type"=> "text",
                  "text"=> "รับมา",
                  "size"=> "xs",
                  "weight"=> "bold",
                  "color"=> "#000000"
                ],
                [
                  "type"=> "text",
                  "text"=> "200.00",
                  "size"=> "xs",
                  "align"=> "end",
                  "weight"=> "bold",
                  "color"=> "#6E422D"
                ]
              ]
            ],
            [
              "type"=> "box",
              "layout"=> "baseline",
              "contents"=> [
                [
                  "type"=> "text",
                  "text"=> "เงินทอน",
                  "size"=> "xs",
                  "weight"=> "bold",
                  "color"=> "#000000"
                ],
                [
                  "type"=> "text",
                  "text"=> "65.00",
                  "size"=> "xs",
                  "align"=> "end",
                  "weight"=> "bold",
                  "color"=> "#6E422D"
                ]
              ]
            ]
          ]
        ],
        [
          "type"=> "text",
          "text"=> "Text",
          "size"=> "xxs",
          "color"=> "#FFFFFF"
        ],
        [
          "type"=> "separator"
        ]
      ]
    ],
    "footer"=> [
      "type"=> "box",
      "layout"=> "horizontal",
      "contents"=> [
        [
          "type"=> "box",
          "layout"=> "vertical",
          "contents"=> [
            [
              "type"=> "box",
              "layout"=> "baseline",
              "contents"=> [
                [
                  "type"=> "text",
                  "text"=> "พนักงานขาย",
                  "size"=> "xs",
                  "align"=> "start",
                  "weight"=> "bold",
                  "color"=> "#7B7B7B"
                ],
                [
                  "type"=> "text",
                  "text"=> "นัทคำเดียว",
                  "size"=> "xs",
                  "align"=> "end",
                  "weight"=> "bold",
                  "color"=> "#7B7B7B"
                ]
              ]
            ]
          ]
        ]
      ]
    ]
  ]

];


$ord=[
  "type"=> "template",
  "altText"=> "this is a image carousel template",
  "template"=> [
      "type"=> "image_carousel",
      "columns"=> [
          [
            "imageUrl"=> "https://raw.githubusercontent.com/nuttlovenutt00/nutt/master/1.jpg",
            "action"=> [
              "type"=> "message",
              "label"=> "Code : P101",
              "text"=> " "
            ]
          ],
          [
            "imageUrl"=> "https://raw.githubusercontent.com/nuttlovenutt00/nutt/master/2.jpg",
            "action"=> [
              "type"=> "message",
              "label"=> "Code : P102",
              "text"=> " "
            ]
          ],
          [
            "imageUrl"=> "https://raw.githubusercontent.com/nuttlovenutt00/nutt/master/3.jpg",
            "action"=> [
             "type"=> "message",
              "label"=> "Code : P103",
              "text"=> " "
            ]
          ],
          [
            "imageUrl"=> "https://raw.githubusercontent.com/nuttlovenutt00/nutt/master/4.jpg",
            "action"=> [
              "type"=> "message",
              "label"=> "Code : P104",
               "text"=> " "
            ]
          ],
          [
            "imageUrl"=> "https://raw.githubusercontent.com/nuttlovenutt00/nutt/master/5.jpg",
            "action"=> [
              "type"=> "message",
              "label"=> "Code : P105",
               "text"=> " "
            ]
          ]
      ]
  ]

  
];


  
  $lineData['URL'] = "https://api.line.me/v2/bot/message/reply";
  $lineData['AccessToken'] = "0EhBTTseT51jUDZTB2ExoXM+4VM59TybE8WoW6GdG7I9ugLQyQssBVyKuWw18GgvhVOXYLtJCbAwnamRdP10iFyFkpSIdlgskfDHONLWlJ/f9MB9IitlaOHZzIyGxDZgrDLiX+XXp/BOq+4SjJZe7AdB04t89/1O/w1cDnyilFU=";
  $replyJson["replyToken"] = $replyToken;

  if($text=="รายการของฉัน"){
    $replyJson["messages"][0] = $order;
  }elseif($text=="ยืนยันการสั่ง"){
    $replyJson["messages"][0] = $re1;
  }elseif($text=="แสดงใบเสร็จรับเงินเพิ่มเติม"){
    $replyJson["messages"][0] = $re2;
  }elseif($text=="เมนูแนะนำ"){
    $replyJson["messages"][0] = $ord;
  }
  
  

   
  
  $encodeJson = json_encode($replyJson);
  $results = sendMessage($encodeJson,$lineData);
  echo $results;
  http_response_code(200);
?>
