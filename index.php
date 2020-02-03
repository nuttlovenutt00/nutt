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




  if($text!="" && $text!="เมนูแนะนำ"  && $text!="รายการของฉัน"  && $text!="ช่วยเหลือ" && $text!="ยืนยันการสั่ง")
  {

      $replyText_order_text["type"] = "text";
      $order_text = explode("\n", $text);
      $countArrayorder_text = count($order_text);
      for($i=0;$i<$countArrayorder_text;$i++){
         $replyText_order_text["text"].=$order_text[$i]."\n";
     }
          $replyJson["messages"][0] = $replyText_order_text;




  }elseif($text=="เมนูแนะนำ"){

    $numl_ProHot=0;  
    //ค้นหาเมนูแนะนำ
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
          //แสดงผล
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

    
  }elseif($text=="รายการของฉัน" || $text=="ยืนยันการสั่ง")//เอา 2 คำสั่งนี้มารวมกันเพราะ code บางอย่างเหมือนกัน
  {

              //ค้นหาข้อมูลในฐานข้อมูล
              $sql_sorderme = "Select orId,ortDate,ortTime,ortStatus from  OrderTemp  where ortUserId='$userID'  order by orAutoId DESC";
              $result_sorderme = $mysql->query($sql_sorderme);
              $objResult_sorderme = $result_sorderme->fetch_assoc(); 

              //ประกาศตัวแปรเอาไว้เก็บค่า
              $cid =$objResult_sorderme['orId'];
              $cdate =$objResult_sorderme['ortDate'];
              $ctime =$objResult_sorderme['ortTime'];
              $ortStatus=$objResult_sorderme['ortStatus'];

              //ตรวจสอบว่าในฐานข้อมูลมีข้อมูลอยู่หรือป่าว
              if($result_sorderme->num_rows == 0 || $ortStatus == "complete")
               {
                
                  $replyText_orderme["type"] = "text";
                  $replyText_orderme["text"] = "คุณยังไม่ได้สั่งเมนูค่ะ";
                  $replyJson["messages"][0] = $replyText_orderme;

                  $replySticker_orderme=[
                    "type"=> "sticker",
                    "packageId"=> "11538",
                    "stickerId"=> "51626517"
                  ];
                  $replyJson["messages"][1] = $replySticker_orderme;

               }else{
                  //เอาเวลามารวมกับวันที่
                  $datetime_ort=$cdate." ".$ctime;
                  $datetime_now=$datetime." ".$time;

                  //ฟังก์ชั่น คำนวนหาความห่างของเวลา
                   function DateTimeDiff($strDateTime1,$strDateTime2)
                   {
                        return (strtotime($strDateTime2) - strtotime($strDateTime1))/  ( 60 * 60 ); // 1 Hour =  60*60
                   }

                  //ถ้าสั่งออเดอร์ครั้งล่าสุดกับปัจจุบันมีความห่างกันเกิน 5 นาทีหรือยัง
                  if(DateTimeDiff($datetime_ort,$datetime_now)>0.083)
                  {

                      $replyText_orderme["type"] = "text";
                      $replyText_orderme["text"] = "คุณพักการสั่งไป เกิน 5 นาทีแล้วค่ะ กรุณาสั่งรายการใหม่ค่ะ";
                      $replyJson["messages"][0] = $replyText_orderme;

                      $replySticker_orderme=[
                        "type"=> "sticker",
                        "packageId"=> "11538",
                        "stickerId"=> "51626511"
                      ];
                      $replyJson["messages"][1] = $replySticker_orderme;

                  }elseif(DateTimeDiff($datetime_ort,$datetime_now)<=0.083){

                      if($text=="รายการของฉัน")
                      {
                            $num=0;
                            $showorderme_detail=[];
                            $totalpriceorder=0;
                            //ค้นหาข้อมูลในฐานข้อมูลในตาราง Temp
                            $sql_slorderme = "Select ordtMId,PName,ordtUnit,UName,ordtComment,PPrice from  OrderDetailTemp as a
                              left join Product as b on a.ordtMId = b.PId
                              left join Unit as c on b.PUnit = c.UId   where ordtOrId='$cid'";
                            $result_slorderme = $mysql->query($sql_slorderme);
                            while($objResult_slorderme = $result_slorderme->fetch_assoc())
                            {
                              $ordtMId=$objResult_slorderme["ordtMId"];
                              $PName=$objResult_slorderme["PName"];
                              $ordtUnit=$objResult_slorderme["ordtUnit"];
                              $ordtComment=$objResult_slorderme["ordtComment"];
                              $ordtPPrice=$objResult_slorderme["PPrice"];

                              $totalpriceorder=($ordtUnit * $ordtPPrice) + $totalpriceorder;

                              if($objResult_slorderme["ordtComment"]=="ไม่มี"){
                                $ordtComment="";
                              }else{
                                $ordtComment="  *".$objResult_slorderme["ordtComment"];
                              }




                              $showorderme_detail[$num]=[
                                          
                                              "type"=> "text",
                                              "text"=> $ordtMId.":".$PName." ฿".number_format($ordtPPrice,2)." x".$ordtUnit.$ordtComment,
                                              "size"=> "sm",
                                              "color"=> "#000000"
                                            
                                    ];
                              $num++;
                            }
                            $showorderme=[
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
                                          "size"=> "md",
                                          "align"=> "start",
                                          "weight"=> "bold",
                                          "color"=> "#6E422D"
                                        ],
                                        [
                                          "type"=> "separator"
                                        ],
                                        [
                                          "type"=> "box",
                                          "layout"=> "vertical",
                                          "spacing"=> "sm",
                                          "margin"=> "lg",
                                          "contents"=> $showorderme_detail
                                        ],
                                        [
                                          "type"=> "text",
                                          "text"=> "text",
                                          "size"=> "xxs",
                                          "color"=> "#FFFFFF"
                                        ],
                                        [
                                          "type"=> "text",
                                          "text"=> "ราคารวม ฿".number_format($totalpriceorder,2),
                                          "size"=> "sm",
                                          "color"=> "#000000"
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
                                          "text"=> "ยกเลิกเมนู พิมพ์ รหัสสินค้า@0 เช่น P1@0",
                                          "size"=> "xxs",
                                          "color"=> "#000000"
                                        ],
                                        [
                                          "type"=> "text",
                                          "text"=> "แก้ไขจำนวน พิมพ์ รหัสสินค้า@จำนวนที่ต้องการ",
                                          "size"=> "xxs",
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
                                          "color"=> "#11B000",
                                          "height"=> "sm",
                                          "style"=> "primary"
                                        ]
                                      ]
                                    ]
                                  ]
                              ];
                              $replyJson["messages"][0] = $showorderme;
        
                      }elseif($text=="ยืนยันการสั่ง"){


                                //ค้นหารหัส order ก่อนหน้านี้และสร้างใหม่
                                $sql_sirt = "Select Max(orId) as MaxID from  OrderMenu";
                                $result_sirt = $mysql->query($sql_sirt);
                                $objResult = $result_sirt->fetch_assoc();
                                if($objResult["MaxID"]=="")
                                  {
                                    $idfull=date("Ym");
                                     $id_temp= "ORD".$idfull."-1";                                  
                                   }else{
                                  $memidyearold1=substr($objResult["MaxID"],0,7);
                                  $memidyearold=substr($memidyearold1,3);
                                  $memidnewyear=date("Y");
                                  $memidnew=$memidnewyear-$memidyearold;
                                  
                                  $memidmonthold1=substr($objResult["MaxID"],0,9);
                                  $memidmonthold=substr($memidmonthold1,7);
                                  $memidnewmonth=date("m");
                                  $memidmonthnew=$memidnewmonth-$memidmonthold;
                                  

                                  if($memidnew==0 && $memidmonthnew==0)
                                  {
                                    $tmpidold=substr($objResult["MaxID"],0,9);      
                                    $tmpidnumold=substr($objResult["MaxID"],10); 
                                    $tmpidnumnew=$tmpidnumold+1;
                                  
                                    
                                    $tmpnewyearfull=date("Ym");     
                                    $id_temp= "ORD".$tmpnewyearfull."-".$tmpidnumnew;
                                  
                                    
                                  }elseif($memidnew>=1 || $memidmonthnew>=1)
                                  {
                                    
                                    $tmpnewyearfull=date("Ym");
                                    $id_temp= "ORD".$tmpnewyearfull."-1";
                                  }
                                }
                                //สิ้นสุดค้นหารหัส order ก่อนหน้านี้และสร้างใหม่


                                //ค้นหารหัส Q ก่อนหน้านี้และสร้างใหม่
                                $sql_Q = "Select orDate,orQ from  OrderMenu order by orAutoId DESC";
                                $result_Q = $mysql->query($sql_Q);
                                $objResult_Q = $result_Q->fetch_assoc();

                                $Q=$objResult_Q["orQ"];

                                //เอาเวลามารวมกับวันที่
                                $datetime_ort=$objResult_Q["orDate"];
                                $datetime_now=$datetime;

                                //ฟังก์ชั่น คำนวนหาความห่างของเวลา
                                 function DateDiff($strDate1,$strDate2)
                                 {
                                      return (strtotime($strDate2) - strtotime($strDate1))/  ( 60 * 60 * 24 );  // 1 day = 60*60*24
                                 }

                                 if(DateDiff($datetime_ort,$datetime_now)>0 || $Q==""){
                                    $id_Q= "1";
                                 }else{

                                    $id_Q = $Q+1;
                                }
                                
                                //สิ้นสุดค้นหารหัส Q ก่อนหน้านี้และสร้างใหม่


                                //คำนวน Q 
                                $sql_cQ = "Select orQ from  OrderMenu where orDate='$datetime' and orStatus='รอชำระเงิน' order by orAutoId ASC";
                                $result_cQ = $mysql->query($sql_cQ);
                                $objResult_cQ = $result_cQ->fetch_assoc();

                                $cQ=$objResult_cQ["orQ"];

                                
                                if($id_Q==1){
                                  $ccQ=0;
                                }else{
                                  $ccQ=$id_Q-$cQ;
                                }
                                //สิ้นสุดคำนวน Q 

                               

                                //ตัวแปร
                                $id_temp=$id_temp;
                                $ordtMId="";
                                $ordtUnit="";
                                $UName="";
                                $ordtComment="";
                                $numpro=0;
                                $PricePro=0;

                                $timee=date("H:i");

                                //ค้นหาข้อมูลในฐานข้อมูลในตาราง Temp
                                $sql_slorderme = "Select ordtMId,ordtUnit,ordtComment,PPrice from  OrderDetailTemp as a left join Product as b on a.ordtMId = b.PId where ordtOrId='$cid' ";
                                $result_slorderme = $mysql->query($sql_slorderme);
                                while($objResult_slorderme = $result_slorderme->fetch_assoc())
                                {
                                  //ริชข้อมูลจากตางราง temp แล้วมาเก็บในตัวแปร และบันทึกลงตารางจริง
                                  $ordtMId=$objResult_slorderme["ordtMId"];
                                  $ordtUnit=$objResult_slorderme["ordtUnit"];
                                  $ordtComment=$objResult_slorderme["ordtComment"];
                                  $PPrice=$objResult_slorderme["PPrice"];
                                  $mysql->query("INSERT INTO OrderDetail(OrdOrId,OrdPId,OrdUnit,OrdComment) VALUES ('$id_temp','$ordtMId','$ordtUnit','$ordtComment')");
                                  $numpro=$numpro+$ordtUnit; //รวมจำนวนสินค้า
                                  $PricePro=$PricePro+($PPrice*$ordtUnit);//รวมราคาสินค้า
                                }

                                //บันทึกข้อมูลลงตาราง OrderMenu
                                $mysql->query("INSERT INTO OrderMenu(orId,orDate,orTime,orQ,orStatus,orUserId,orUnit,orPriceTotal) VALUES               ('$id_temp','$datetime','$timee','$id_Q','รอชำระเงิน','$userID','$numpro','$PricePro')");

                                //แก้ไขข้อมูลในตาราง Temp ว่ายืนยันการสั่งแล้ว
                                 $mysql->query("UPDATE OrderTemp set ortStatus='complete' where orId='$cid' ");
                                
                                //แสดงคิว
                                  $showQ=[
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
                                            "text"=> "ลำดับคิวของท่าน",
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
                                            "type"=> "text",
                                            "text"=> (String)$id_Q,
                                            "size"=> "4xl",
                                            "align"=> "center",
                                            "weight"=> "bold",
                                            "color"=> "#000000"
                                          ],
                                          [
                                            "type"=> "text",
                                            "text"=> "Text",
                                            "size"=> "xxs",
                                            "color"=> "#FFFFFF"
                                          ], 
                                          [
                                            "type"=> "text",
                                            "text"=> "#".$id_temp,
                                            "size"=> "xs"
                                          ],
                                          [
                                            "type"=> "text",
                                            "text"=> "จำนวนที่รอ ".(String)$ccQ." คิว",
                                            "size"=> "lg",
                                            "weight"=> "bold",
                                            "color"=> "#000000"
                                          ],
                                          [
                                            "type"=> "text",
                                            "text"=> "*กรณีลูกค้าไม่อยู่รับบริการในช่วงเวลาที่",
                                            "size"=> "xs",
                                            "weight"=> "bold",
                                            "color"=> "#FF0000"
                                          ],
                                          [
                                            "type"=> "text",
                                            "text"=> "เรียกคิว ทางร้านของสงวนสิทธิในการข้ามคิว",
                                            "size"=> "xs",
                                            "weight"=> "bold",
                                            "color"=> "#FF0000"
                                          ]
                                        ]
                                      ]
                                    ]

                                ];

                                 $replyJson["messages"][0] = $showQ;
                                


                      }

                  }

               }

              
  }elseif($text=="ช่วยเหลือ")
  {
      //แสดงหน้าต่าง ช่วยเหลือ
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
                    "text"=> "เมนูแนะนำ วิธีสั่งเมนู",
                    "size"=> "md",
                    "align"=> "center",
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
                  ],
                  [
                    "type"=> "spacer"
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
                    "text"=> "เมนูแนะนำ วิธีเปลี่ยนแปลงรายการ",
                    "size"=> "md",
                    "align"=> "center",
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
                    "text"=> 'วิธี "แก้ไข" จำนวนรายการ',
                    "weight"=> "bold",
                    "color"=> "#D86E28"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "เมื่อคุณลูกค้าต้องการที่จะเปลี่ยนจำนวน",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "รายการของท่าน ให้พิมพ์ ",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "รหัสสินค้า@จำนวนที่ต้องการแก้ไข",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "เช่น P123@4 ",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "*ระบบจะนับจำนวนตามครั้งที่สั่งล่าสุด",
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
                    "text"=> 'วิธี "ลบ" รายการ',
                    "size"=> "md",
                    "weight"=> "bold",
                    "color"=> "#FF0000"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "เมื่อลูกค้าต้องการที่จะลบรายการ",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "ที่ไม่ต้องการ ให้พิมพ์",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "รหัสสินค้า@ตามด้วยเลข 0",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "เช่น P123@0",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "spacer"
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
                    "text"=> "เมนูแนะนำ การยืนยันการสั่งเมนู",
                    "size"=> "md",
                    "align"=> "center",
                    "weight"=> "bold",
                    "color"=> "#11B000"
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
                    "text"=> "=> เมื่อลูกค้าสั่งรายการจนครบแล้ว",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> '=> ให้กดเมนู "รายการของฉัน" หรือ',
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> 'ปุ่ม "แสดงรายการทั้งหมดในตะกร้า"',
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "=> จะปรากฏหน้ารายการของฉันขึ้นมา",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> '=>  ให้กด ปุ่ม "ยืนยันการสั่ง"',
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "=> ระบบจะแสดง ลำดับคิวของท่าน",
                    "size"=> "sm"
                  ],
                  [
                    "type"=> "text",
                    "text"=> "เพื่อรอการชำระเงินต่อไป",
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
