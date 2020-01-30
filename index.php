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

     $message = strtoupper($text);//แปลงเป็นตัวพิมพ์ใหญ่

        $a= strpos( $message, "@" );
        $b =substr($message,$a+1);

        //หาข้อความเพิ่มเติม
       if(strpos( $b, "@" ) != FALSE){

              //หาข้อความเพิ่มเติม
              $d=strpos( $b, "@" );
              $morePro_fromtext =substr($b,$d+1);
               //หาจำนวน
              $numberPro_fromtext =iconv_substr($b,0,$d,"UTF-8");
       }else{

              //หาจำนวน
              $morePro_fromtext="ไม่มี";
              $numberPro_fromtext = $b;
       }


        $idPro_fromtext =iconv_substr($message,0,$a,"UTF-8");//คัดเอาแต่รหัสสินค้า

        $chkpro="";
        //ตรวจสอบรหัสสินค้าในฐานข้อมูลว่ามีหรือไม่
        $sql_SPro = "SELECT PAutoId,PName,UName FROM  Product as a
          left join Unit as b on a.PUnit = b.UId
          where PId= '$idPro_fromtext' ";
        $result_SPro = $mysql->query($sql_SPro);
         if($result_SPro->num_rows > 0)
        {
            $chkpro="yes";
        }else{
            $chkpro="no";
        }


      if(strpos( $message, "P" )== 0  && strpos( $message, "P" ) !== FALSE && strpos( $message, "@" ) !== FALSE &&  is_numeric($numberPro_fromtext) && $chkpro=="yes"){

              //ค้นหาข้อมูลในฐานข้อมูล
              $sql_sdrt = "Select orId,ortDate,ortTime from  OrderTemp  where ortUserId='$userID' order by orAutoId DESC";
              $result_sdrt = $mysql->query($sql_sdrt);
              $objResult_sdrt = $result_sdrt->fetch_assoc(); 

              //ประกาศตัวแปรเอาไว้เก็บค่า
              $cid =$objResult_sdrt['orId'];
              $cdate =$objResult_sdrt['ortDate'];
              $ctime =$objResult_sdrt['ortTime'];

              //ตรวจสอบว่าในฐานข้อมูลมีข้อมูลอยู่หรือป่าว
              if($cid=="")
               {
                $noid ="yes";
               }else{
                $noid ="no";
               }

              //เอาเวลามารวมกับวันที่
              $datetime_ort=$cdate." ".$ctime;
              $datetime_now=$datetime." ".$time;

              //ฟังก์ชั่น คำนวนหาความห่างของเวลา
               function DateTimeDiff($strDateTime1,$strDateTime2)
               {
                    return (strtotime($strDateTime2) - strtotime($strDateTime1))/  ( 60 * 60 ); // 1 Hour =  60*60
               }


               //ตัวแปรตรวจสอบว่าลูกค้าสั่งใหม่ แก้ไข หรือยกเลิก
               $action_SPro="";
              //ถ้าสั่งออเดอร์ครั้งล่าสุดกับปัจจุบันมีความห่างกันเกิน 5 นาทีหรือยัง
              if(DateTimeDiff($datetime_ort,$datetime_now)>0.083 || $noid == "yes")
              {
                                //คำนวนรหัสของ Order
                                $sql_sirt = "Select Max(orId) as MaxID from  OrderTemp";
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
                                //สิ้นสุดคำนวนรหัสของ Order

                        //เก็บข้อมูลลงฐานข้อมูล      
                        $mysql->query("INSERT INTO OrderTemp(orId,ortDate,ortTime,ortUserId) VALUES ('$id_temp','$datetime','$time','$userID')");

                        $mysql->query("INSERT INTO OrderDetailTemp(ordtOrId,ordtMId,ordtUnit,ordtComment) VALUES ('$id_temp','$idPro_fromtext','$numberPro_fromtext','$morePro_fromtext')");

                        $action_SPro="neworder";
                     
              }else{
                        //เก็บข้อมูลลงฐานข้อมูล      
                        $mysql->query("INSERT INTO OrderTemp(orId,ortDate,ortTime,ortUserId) VALUES ('$cid','$datetime','$time','$userID')");

                        //ค้นหาข้อมูลในฐานข้อมูลว่าเพิ่มซ้ำกันมั้ย ถ้าใช่ให้เปลี่ยนแค่จำนวน
                          $sql_sordt = "Select ordtId from  OrderDetailTemp  where ordtMId='$idPro_fromtext' and ordtOrId='$cid' ";
                          $result_sordt = $mysql->query($sql_sordt);
                          if($result_sordt->num_rows >0){

                              if($numberPro_fromtext==0){
                                $mysql->query("DELETE FROM  OrderDetailTemp where ordtMId='$idPro_fromtext' and ordtOrId='$cid'");
                                $action_SPro="delorder";
                              }else{
                                $mysql->query("UPDATE  OrderDetailTemp set ordtUnit='$numberPro_fromtext',ordtComment='$morePro_fromtext' where ordtMId='$idPro_fromtext' and ordtOrId='$cid'");
                                $action_SPro="uporder";
                              }
                              
                            
                          }else{
                              $mysql->query("INSERT INTO OrderDetailTemp(ordtOrId,ordtMId,ordtUnit,ordtComment) VALUES ('$cid','$idPro_fromtext','$numberPro_fromtext','$morePro_fromtext')");
                               $action_SPro="neworder";
                          }

              }

              $array_SPro=$result_SPro->fetch_assoc();
              $namePro=$array_SPro["PName"];
              $nameProUnit=$array_SPro["UName"];
             

              //สร้างตัวแปรไว้เก็บข้อความตามการกระทำของลูกค้า
              if($action_SPro == "neworder")
              {
                  $replyText_sp_title="ระบบได้รับออเดอร์เรียบร้อยแล้วค่ะ";
                  $replyText_sp_color_title="#6E422D";
                  $replyText_sp_button=[
                    "footer"=> [
                            "type"=> "box",
                            "layout"=> "vertical",
                            "contents"=> [
                              [
                                "type"=> "button",
                                "action"=> [
                                  "type"=> "message",
                                  "label"=> "แสดงรายการทั้งหมดในตะกร้า",
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
                              ]
                            ]
                          ]
                  ];

              }elseif($action_SPro == "uporder")
              {
                  $replyText_sp_title="ระบบได้แก้ไขออเดอร์เรียบร้อยแล้วค่ะ";
                  $replyText_sp_color_title="#6E422D";
                  $replyText_sp_button=[
                    "footer"=> [
                            "type"=> "box",
                            "layout"=> "vertical",
                            "contents"=> [
                              [
                                "type"=> "button",
                                "action"=> [
                                  "type"=> "message",
                                  "label"=> "แสดงรายการทั้งหมดในตะกร้า",
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
                              ]
                            ]
                          ]
                  ];

              }elseif($action_SPro == "delorder")
              {
                $replyText_sp_title="ระบบได้ลบออเดอร์เรียบร้อยแล้วค่ะ";
                $replyText_sp_color_title="#FF0000";
                $replyText_sp_button=[];
              }
                   //แสดงหน้าต่างรับออเดอร์ลูกค้า
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
                                "text"=>  $replyText_sp_title,
                                "size"=> "sm",
                                "align"=> "start",
                                "weight"=> "bold",
                                "color"=> "#FFFFFF"
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
                                "text"=> "รหัสสินค้า : ".$idPro_fromtext,
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
                                "text"=> "จำนวน : ".$numberPro_fromtext." ".$nameProUnit,
                                "size"=> "sm",
                                "color"=> "#000000"
                              ],
                              [
                                "type"=> "text",
                                "text"=> "ข้อความเพิ่มเติม : ".$morePro_fromtext,
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
                                  "label"=> "แสดงรายการทั้งหมดในตะกร้า",
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
                              ]
                            ]
                          ]
                        ]
                    ];

              


              $replyJson["messages"][0] = $replyText_sp;

      }elseif(strpos( $message, "P" )== 0  && strpos( $message, "P" ) !== FALSE && strpos( $message, "@" ) !== FALSE &&  is_numeric($numberPro_fromtext) && $chkpro=="no")
      {
              $replyText_sp["type"] = "text";
              $replyText_sp["text"] = "ไม่พบรหัสสินค้า : ".$idPro_fromtext." นี้ในฐานข้อมูลค่ะ";
              $replyJson["messages"][0] = $replyText_sp;

              $replySticker_sp=[
                "type"=> "sticker",
                "packageId"=> "11538",
                "stickerId"=> "51626522"
              ];
              $replyJson["messages"][1] = $replySticker_sp;

      }elseif(strpos( $message, "@" ) !== FALSE &&  is_numeric($numberPro_fromtext) && $chkpro=="no")
      {
              $replyText_sp["type"] = "text";
              $replyText_sp["text"] = "ไม่พบรหัสสินค้า : ".$idPro_fromtext." นี้ในฐานข้อมูลค่ะ";
              $replyJson["messages"][0] = $replyText_sp;

              $replySticker_sp=[
                "type"=> "sticker",
                "packageId"=> "11538",
                "stickerId"=> "51626522"
              ];
              $replyJson["messages"][1] = $replySticker_sp;

      }else{
              //แสดงหน้าต่าง คุณพิมพ์รูปแบบการสั่งไม่ถูกต้องค่ะ
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
      }//เมื่อพิมพ์ไม่เข้าเงื่อนไข




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

    
  }elseif($text=="รายการของฉัน")
  {


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
