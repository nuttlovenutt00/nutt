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
  $datetime=date("d-m-Y");
  $time=date("H:i:s");

   $mysql->query("INSERT INTO `LOG`(`UserID`, `replyToken`, `Text`, `Timestamp`, `datetime`) VALUES ('$userID','$replyToken','$text','$timestamp','$datetime')");


   //กำหนดค่าของตัวแปร
  $replyText["type"] = "text";
  $replyText2["type"] = "text";


  //ตั้งค่าการตอบ-รับข้อความ
  $type_product="";
  if(strpos($text, "MP") !== FALSE )
  { 

    $sql_sdrt = "Select orId,ortDate,ortTime,ortUserId from  OrderTemp  where ortUserId='$userID' order by orAutoId DESC";
    $result_sdrt = $mysql->query($sql_sdrt);
    $objResult_sdrt = $result_sdrt->fetch_assoc(); 

    $cid =$objResult_sdrt['orId'];
    $cdate =$objResult_sdrt['ortDate'];
    $ctime =$objResult_sdrt['ortTime'];
    $cuser =$objResult_sdrt['ortUserId'];

    if($cid=="")
     {
      $noid ="yes";
     }else{
      $noid ="no";
     }

    function DateTimeDiff($strDateTime1,$strDateTime2)
   {
        return (strtotime($strDateTime2) - strtotime($strDateTime1))/  ( 60 * 60 ); // 1 Hour =  60*60
   }

    $datetime_ort=$cdate." ".$ctime;
    $datetime_now=$datetime." ".$time;

    if(DateTimeDiff($datetime_ort,$datetime_now)>0.083 || $noid == "yes")
    {

   
                                $sql_sirt = "Select Max(orId) as MaxID from  OrderTemp";
                                $result_sirt = $mysql->query($sql_sirt);
                                $objResult = $result_sirt->fetch_assoc(); 

                                $sql_sirt = "Select Max(orId) as MaxID from  OrderTemp";
                                $result_sirt = $mysql->query($sql_sirt);
                                $objResult = $result_sirt->fetch_assoc();
                                if($objResult["MaxID"]=="")
                                  {
                                    $idfull=date("Ym");
                                     $id_temp= "ORD".$idfull."-0001";                                  
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
                                  
                                    if($tmpidnumnew<=9)
                                    {
                                      $tmpidzero="000"; 
                                    }elseif($tmpidnumnew > 9 && $tmpidnumnew <= 99 )
                                      {
                                        $tmpidzero="00";
                                      }
                                      elseif($tmpidnumnew >=100 && $tmpidnumnew <= 999 )
                                      {
                                        $tmpidzero="0";
                                      }
                                      elseif($tmpidnumnew >=1000 && $tmpidnumnew <= 9999 )
                                      {
                                        $tmpidzero="";
                                      }
                                    $tmpnewyearfull=date("Ym");     
                                    $id_temp= "ORD".$tmpnewyearfull."-".$tmpidzero.$tmpidnumnew;
                                  
                                    
                                  }elseif($memidnew>=1 || $memidmonthnew>=1)
                                  {
                                    
                                    $tmpnewyearfull=date("Ym");
                                    $id_temp= "ORD".$tmpnewyearfull."-0001";
                                  }
                                }

         $mysql->query("INSERT INTO OrderTemp(orId,ortDate,ortTime,ortUserId) VALUES ('$id_temp','$datetime','$time','$userID')");

          $mysql->query("INSERT INTO OrderDetailTemp(ordtOrId,ordtMId,ordtUnit) VALUES ('$id_temp','$text','1')");
     }else{
         $mysql->query("INSERT INTO OrderTemp(orId,ortDate,ortTime,ortUserId) VALUES ('$cid','$datetime','$time','$userID')");
          $mysql->query("INSERT INTO OrderDetailTemp(ordtOrId,ordtMId,ordtUnit) VALUES ('$cid','$text','1')");

      }
    

      

      //ค้นหาชื่อกาแฟจากฐานข้อมูล
      $sql_snc = "SELECT tp_name,m_name FROM menu 
      left join type_product on menu.m_tp_id = type_product.tp_id
      where m_id=  '$text'     ";
      $result_snc = $mysql->query($sql_snc);
      $row_snc = $result_snc->fetch_assoc();
      $nametypecafe=$row_snc['tp_name'];
      $namecafe=$row_snc['m_name'];
       //สิ้นสุดค้นหาชื่อกาแฟจากฐานข้อมูล

      $replyText["text"] = "ระบบได้ทำการบันทึก Order:$nametypecafe $namecafe ของท่านแล้วค่ะ";
  }elseif($text=="เมนูกาแฟ")
  {
    $type_product="1";
  }elseif($text=="รายการของฉัน")
  {
    
  }else{
    $replyText["text"] = "กรุณาเลือกเมนูอีกรอบค่ะ";
  }
  

  //แสดงเมนูกาแฟ
  if($text=="เมนูกาแฟ")
  {

    //ค้นหาชื่อของกาแฟทั้งหมด
    $sql_m_cafe = "SELECT tp_pic,tp_id,tp_name FROM type 
          left join type_product  on type.t_id_auto = type_product.tp_t_id
          where type.t_id_auto=$type_product";

    $result_m_cafe = $mysql->query($sql_m_cafe);

    // Array ที่เก็บข้อมูล ร้อน เย็น ปั่น เมนูกาแฟเพื่อไปแสดงในไลน์
    $showdetailmenu=[];

    // Array ที่เก็บข้อมูล รหัส เมนูกาแฟเพื่อไปแสดงในไลน์
    $iddetailmenu=[];

    //จำนวนที่ใส่ไปใน Array 
    $num=0;    
    while($row_m_cafe = $result_m_cafe->fetch_assoc()) 
      {

        $pic= $row_m_cafe['tp_pic']; //รูปของกาแฟ
        $idtypecafe=$row_m_cafe["tp_id"]; //รหัสประเภทของกาแฟ

        //ลิชรายการประเภทของกาแฟมาแสดงว่า ร้อน เย็น ปั่น
        $sql_d_cafe = "SELECT m_name,m_price,m_id FROM menu 
        left join type_product  on menu.m_tp_id = type_product.tp_id
        where m_tp_id=  $idtypecafe";
        $result_d_cafe = $mysql->query($sql_d_cafe);

        //ตัวแปรเอาไว้นับประเภทของการแฟ ร้อน เย็น ปั่น ว่ามีเท่าไร
        $num_unit_m=0;

        while($row_d_cafe = $result_d_cafe->fetch_assoc()) 
          {

            $showdetailmenu[$num_unit_m]= $row_d_cafe['m_name'].$row_d_cafe['m_price'];
            $iddetailmenu[$num_unit_m]=$row_d_cafe['m_id'];
             $num_unit_m++;
          }
          
        if($num_unit_m==1){
           $a2[$num]=
            [
              "thumbnailImageUrl"=>  $pic,
              "title"=>  $row_m_cafe["tp_name"],
              "text"=>  "กรุณาเลือกประเภทของกาแฟของท่าน ตามเมนูข้างล่างค่ะ",
             
              "actions"=>  [
                  [
                      "type"=>  "message",
                      "label"=>  $showdetailmenu[0]." บาท",
                      "text"=>  $iddetailmenu[0]
                  ],
                  [
                       "type"=>  "message",
                      "label"=>  "  ",
                      "text"=>  "  "
                  ],
                  [
                      "type"=>  "message",
                      "label"=>  "  ",
                      "text"=>  "  "
                  ]
              ]
            ];
        }elseif($num_unit_m==2)
        {
          $a2[$num]=
            [
              "thumbnailImageUrl"=>  $pic,
              "imageBackgroundColor"=>  "#FFFFFF",
              "title"=>  $row_m_cafe["tp_name"],
              "text"=>  "กรุณาเลือกประเภทของกาแฟของท่าน ตามเมนูข้างล่างค่ะ",
             
              "actions"=>  [
                  [
                      "type"=>  "message",
                      "label"=>  $showdetailmenu[0]." บาท",
                      "text"=>  $iddetailmenu[0]
                  ],
                  [
                      "type"=>  "message",
                      "label"=>  $showdetailmenu[1]." บาท",
                      "text"=>  $iddetailmenu[1]
                  ],
                  [
                      "type"=>  "message",
                      "label"=>  "  ",
                      "text"=>  "  "
                  ]
              ]
            ];
        }else{
           $a2[$num]=
            [
              "thumbnailImageUrl"=>  $pic,
              "imageBackgroundColor"=>  "#FFFFFF",
              "title"=>  $row_m_cafe["tp_name"],
              "text"=>  "กรุณาเลือกประเภทของกาแฟของท่าน ตามเมนูข้างล่างค่ะ",
             
              "actions"=>  [
                  [
                      "type"=>  "message",
                      "label"=>  $showdetailmenu[0]." บาท",
                      "text"=>  $iddetailmenu[0]
                  ],
                  [
                      "type"=>  "message",
                      "label"=>  $showdetailmenu[1]." บาท",
                      "text"=>  $iddetailmenu[1]
                  ],
                  [
                      "type"=>  "message",
                      "label"=>  $showdetailmenu[2]." บาท",
                      "text"=>  $iddetailmenu[2]
                  ]
              ]
            ];

        }
       
            $num++;
      }
      //ส่งข้อมูลกลับไปหาไลน์
      $replyText1= [ 
      "type"=> "template",
      "altText"=>  "this is a carousel template",
      "template"=>  [
          "type"=>  "carousel",
          "columns"=>  $a2 ,
          "imageAspectRatio"=>  "rectangle",
          "imageSize"=>  "cover"
        ]
    ];
  }elseif($text=="รายการของฉัน")
  {
    $sql_sdrt = "Select orId,ortDate,ortTime,ortUserId from  OrderTemp  where ortUserId='$userID' order by orAutoId DESC";
    $result_sdrt = $mysql->query($sql_sdrt);
    $objResult_sdrt = $result_sdrt->fetch_assoc(); 

    $cid =$objResult_sdrt['orId'];
    $cdate =$objResult_sdrt['ortDate'];
    $ctime =$objResult_sdrt['ortTime'];
    $cuser =$objResult_sdrt['ortUserId'];

    

    function DateTimeDiff1($strDateTime1,$strDateTime2)
   {
        return (strtotime($strDateTime2) - strtotime($strDateTime1))/  ( 60 * 60 ); // 1 Hour =  60*60
   }

    $datetime_ort=$cdate." ".$ctime;
    $datetime_now=$datetime." ".$time;

    if(DateTimeDiff1($datetime_ort,$datetime_now)<0.083 && $cid !== "")
    {
     $a="รายการของฉัน\n";
        $sql_sot = "Select ordtMId,SUM(ordtUnit) from  OrderDetailTemp
                     where ordtOrId='$cid'
                     GROUP BY ordtMId";
         

         $result_sot = $mysql->query($sql_sot);
          while ($objResult_sot = $result_sot->fetch_assoc()) {

              $idpro=$objResult_sot["ordtMId"];
              $sql_sot1 = "Select * from  menu 
                    left join type_product on menu.m_tp_id = type_product.tp_id
                     where menu.m_id='$idpro' ";
               $result_sot1 = $mysql->query($sql_sot1);
               $objResult_sot1 = $result_sot1->fetch_assoc();

             $a.=$objResult_sot1["tp_name"].$objResult_sot1["m_name"].$objResult_sot["SUM(ordtUnit)"]." แก้ว\n";
          } 
         $replyText2= $a;
    }else{
      $replyText2["text"] = "คุณยังไม่มีรายการที่สั่งค่ะ";
    }
  }







  
  $lineData['URL'] = "https://api.line.me/v2/bot/message/reply";
  $lineData['AccessToken'] = "0EhBTTseT51jUDZTB2ExoXM+4VM59TybE8WoW6GdG7I9ugLQyQssBVyKuWw18GgvhVOXYLtJCbAwnamRdP10iFyFkpSIdlgskfDHONLWlJ/f9MB9IitlaOHZzIyGxDZgrDLiX+XXp/BOq+4SjJZe7AdB04t89/1O/w1cDnyilFU=";
  $replyJson["replyToken"] = $replyToken;
  $replyJson["messages"][0] = $replyText;
  
  if($text=="เมนูกาแฟ")
  {
    $replyJson["messages"][0] = $replyText1;
  }elseif($text=="รายการของฉัน")
  {
    $replyJson["messages"][0] = $replyText2;
  }
   
  
  $encodeJson = json_encode($replyJson);
  $results = sendMessage($encodeJson,$lineData);
  echo $results;
  http_response_code(200);
?>
