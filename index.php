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


      if(strpos( $message, "P" )== 0  && strpos( $message, "P" ) !== FALSE && strpos( $message, "@" ) !== FALSE &&  is_numeric($numberPro_fromtext) && $chkpro=="yes")
      {

              //ค้นหาข้อมูลในฐานข้อมูล
              $sql_sdrt = "Select orId,ortDate,ortTime from  OrderTemp  where ortUserId='$userID' order by orAutoId DESC";
              $result_sdrt = $mysql->query($sql_sdrt);
              $objResult_sdrt = $result_sdrt->fetch_assoc(); 

              //ประกาศตัวแปรเอาไว้เก็บค่า
              $cid =$objResult_sdrt['orId'];
              $cdate =$objResult_sdrt['ortDate'];
              $ctime =$objResult_sdrt['ortTime'];

              //ตรวจสอบว่าในฐานข้อมูลมีข้อมูลอยู่หรือป่าว
              if($result_sdrt->num_rows==0)
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

               //ตัวแปรตรวจสอบว่าลูกค้ายกเลิกออเดดอร์ซ้ำหรือป่าว
               $action0_SPro="";
              //ถ้าสั่งออเดอร์ครั้งล่าสุดกับปัจจุบันมีความห่างกันเกิน 5 นาทีหรือยัง
              if(DateTimeDiff($datetime_ort,$datetime_now)>0.083 || $noid == "yes")
              {
                                //คำนวนรหัส Temp ของ Order
                               $sql_sirt = "Select Max(orId) as MaxID from  OrderTemp";
                                $result_sirt = $mysql->query($sql_sirt);
                                $objResult = $result_sirt->fetch_assoc();
                                if($objResult["MaxID"]=="")
                                  {
                                    $id_temp= "1";                                  
                                   }else{

                                    $id_temp = $objResult["MaxID"]+1;
                                }
                                //สิ้นสุดคำนวนรหัสของ Order

                         //ตรวจสอบว่าลูกค้ายกเลิกออเดอร์ตั้งแต่แรกเลยมั้ย       
                        if($numberPro_fromtext=="0"){

                            $action0_SPro="0";
                        }else{
                            //เก็บข้อมูลลงฐานข้อมูล      
                            $mysql->query("INSERT INTO OrderTemp(orId,ortDate,ortTime,ortUserId) VALUES ('$id_temp','$datetime','$time','$userID')");

                            $mysql->query("INSERT INTO OrderDetailTemp(ordtOrId,ordtMId,ordtUnit,ordtComment) VALUES ('$id_temp','$idPro_fromtext','$numberPro_fromtext','$morePro_fromtext')");

                              $action_SPro="neworder";
                              $action0_SPro="1";
                        }
              }else{
                        //เก็บข้อมูลลงฐานข้อมูล      
                        $mysql->query("INSERT INTO OrderTemp(orId,ortDate,ortTime,ortUserId) VALUES ('$cid','$datetime','$time','$userID')");

                        //ค้นหาข้อมูลในฐานข้อมูลว่าเพิ่มซ้ำกันมั้ย ถ้าใช่ให้เปลี่ยนแค่จำนวน
                          $sql_sordt = "Select ordtId from  OrderDetailTemp  where ordtMId='$idPro_fromtext' and ordtOrId='$cid' ";
                          $result_sordt = $mysql->query($sql_sordt);

                          //ถ้ามีอยู่แล้ว
                          if($result_sordt->num_rows >0){

                              //ลูกค้าพิมพ์ยกเลิกออเดอร์
                              if($numberPro_fromtext==0){
                                $mysql->query("DELETE FROM  OrderDetailTemp where ordtMId='$idPro_fromtext' and ordtOrId='$cid'");

                                //เช็คว่าเมื่อยกเลิกสินค้าแล้ว ในตาราง temp มีรายการเหลืออยู่มั้ย ถ้าลบออกหมดให้ลบข้อมูลในตาราง order หลักด้วย
                                $sql_sordt_num = "Select ordtId from  OrderDetailTemp  where ordtOrId='$cid'";
                                 $result_sordt_num = $mysql->query($sql_sordt_num);
                                 if($result_sordt_num->num_rows == 0){
                                      $mysql->query("DELETE FROM  OrderTemp where orId='$cid'");
                                 }

                                $action_SPro="delorder";
                                $action0_SPro="1";
                              }else{ //ลูกค้าเปลี่ยนจำนวนรายการ
                                $mysql->query("UPDATE  OrderDetailTemp set ordtUnit='$numberPro_fromtext',ordtComment='$morePro_fromtext' where ordtMId='$idPro_fromtext' and ordtOrId='$cid'");
                                $action_SPro="uporder";
                                $action0_SPro="1";
                              }
                              
                            
                          }elseif($result_sordt->num_rows ==0 && $numberPro_fromtext!=="0"){
                              $mysql->query("INSERT INTO OrderDetailTemp(ordtOrId,ordtMId,ordtUnit,ordtComment) VALUES ('$cid','$idPro_fromtext','$numberPro_fromtext','$morePro_fromtext')");
                               $action_SPro="neworder";
                               $action0_SPro="1";
                          }elseif($result_sordt->num_rows ==0 && $numberPro_fromtext=="0"){

                              $action0_SPro="0";
                          }

              }

              $array_SPro=$result_SPro->fetch_assoc();
              $namePro=$array_SPro["PName"];
              $nameProUnit=$array_SPro["UName"];
             

              //สร้างตัวแปรไว้เก็บข้อความตามการกระทำของลูกค้า
              if($action_SPro == "neworder")
              {
                  $replyText_sp_title="ระบบได้รับออร์เดอร์ เรียบร้อยแล้วค่ะ";
                  $replyText_sp_color_title="#6E422D";
                  $replyText_sp_button=[
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
                          
                  ];

              }elseif($action_SPro == "uporder")
              {
                  $replyText_sp_title="ระบบได้แก้ไขออร์เดอร์ เรียบร้อยแล้วค่ะ";
                  $replyText_sp_color_title="#6E422D";
                  $replyText_sp_button=[
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
                          
                  ];

              }elseif($action_SPro == "delorder")
              {
                $replyText_sp_title="ระบบได้ลบออร์เดอร์ เรียบร้อยแล้วค่ะ";
                $replyText_sp_color_title="#FF0000";
                $replyText_sp_button=[
                    "type"=> "box",
                    "layout"=> "vertical",
                    "contents"=> [
                      [
                        "type"=> "text",
                        "text"=> "Text",
                        "size"=> "xxs",
                        "color"=> "#FFFFFF"
                      ]
                    ]             
                ];
              }

              //ตรวจสอบว่าลูกค้ายกเลิกออเดอร์ซ้ำหรือป่าว 
              if($action0_SPro=="1")
              {

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
                          
                          "footer"=> $replyText_sp_button
                        ]
                    ];
              //ตรวจสอบว่าลูกค้ายกเลิกออเดอร์ซ้ำหรือป่าว ถ้าใช่
              }elseif($action0_SPro=="0"){
                 $replyText_sp=[
                    "type" => "text",
                    "text" => "คุณไม่มีออร์เดอร์ให้ยกเลิกค่ะ"
                 ];
              }

          $replyJson["messages"][0] = $replyText_sp;

      //เมื่อลูกค้าพิมพ์ข้อความมารูปแบบถูก แต่ไม่มีรหัสสินค้าในฐานข้อมูล
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
      //เมื่อลูกค้าพิมพ์ข้อความมารูปแบบถูก แต่ไม่มีรหัสสินค้าในฐานข้อมูล
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
      //เมื่อพิมพ์ไม่ถูกรูปแบบ       
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
      }




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
              $sql_sorderme = "Select orId,ortDate,ortTime from  OrderTemp  where ortUserId='$userID'  order by orAutoId DESC";
              $result_sorderme = $mysql->query($sql_sorderme);
              $objResult_sorderme = $result_sorderme->fetch_assoc(); 

              //ประกาศตัวแปรเอาไว้เก็บค่า
              $cid =$objResult_sorderme['orId'];
              $cdate =$objResult_sorderme['ortDate'];
              $ctime =$objResult_sorderme['ortTime'];
              $ortStatus=$objResult_sorderme['ortStatus'];

              //ตรวจสอบว่าในฐานข้อมูลมีข้อมูลอยู่หรือป่าว
              if($result_sorderme->num_rows == 0 || $ortStatus == "")
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
                            //ค้นหาข้อมูลในฐานข้อมูลในตาราง Temp
                            $sql_slorderme = "Select ordtMId,PName,ordtUnit,UName,ordtComment from  OrderDetailTemp as a
                              left join Product as b on a.ordtMId = b.PId
                              left join Unit as c on b.PUnit = c.UId   where ordtOrId='$cid'";
                            $result_slorderme = $mysql->query($sql_slorderme);
                            while($objResult_slorderme = $result_slorderme->fetch_assoc())
                            {
                              $ordtMId=$objResult_slorderme["ordtMId"];
                              $PName=$objResult_slorderme["PName"];
                              $ordtUnit=$objResult_slorderme["ordtUnit"];
                              $ordtComment=$objResult_slorderme["ordtComment"];

                              if($objResult_slorderme["ordtComment"]=="ไม่มี"){
                                $ordtComment="";
                              }else{
                                $ordtComment="  *".$objResult_slorderme["ordtComment"];
                              }

                              $showorderme_detail[$num]=[
                                          
                                              "type"=> "text",
                                              "text"=> $ordtMId.":".$PName." x".$ordtUnit.$ordtComment,
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
                                    $id_temp= "ORD".$tmpnewyearfull."-".$tmpidzero.$tmpidnumnew;
                                  
                                    
                                  }elseif($memidnew>=1 || $memidmonthnew>=1)
                                  {
                                    
                                    $tmpnewyearfull=date("Ym");
                                    $id_temp= "ORD".$tmpnewyearfull."-1";
                                  }
                                }
                                //สิ้นสุดค้นหารหัส order ก่อนหน้านี้และสร้างใหม่


                                

                                //ตัวแปร
                                $id_temp=$id_temp;
                                $ordtMId="";
                                $ordtUnit="";
                                $UName="";
                                $ordtComment="";

                                $timee=date("H:i");

                                //ค้นหาข้อมูลในฐานข้อมูลในตาราง Temp
                                $sql_slorderme = "Select ordtMId,ordtUnit,ordtComment from  OrderDetailTemp where ordtOrId='$cid' ";
                                $result_slorderme = $mysql->query($sql_slorderme);
                                while($objResult_slorderme = $result_slorderme->fetch_assoc())
                                {
                                  $ordtMId=$objResult_slorderme["ordtMId"];
                                  $ordtUnit=$objResult_slorderme["ordtUnit"];
                                  $ordtComment=$objResult_slorderme["ordtComment"];
                                  $mysql->query("INSERT INTO OrderDetail(OrdOrId,OrdPId,OrdUnit,OrdComment) VALUES ('$id_temp','$ordtMId','$ordtUnit','$ordtComment')");
                                }

                                $mysql->query("INSERT INTO OrderMenu(orId,orDate,orTime,orQ,orStatus,orUserId,orUnit,orPriceTotal) VALUES               ('$id_temp','$datetime','$timee','1','รอชำระเงิน','$userID','1','1')");

                                 $mysql->query("UPDATE OrderTemp set ortStatus='complete' where orId='$cid' ");
                                



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
