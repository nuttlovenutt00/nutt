<?php

  include("GetDataLine.php");
  include("ConnectSql.php.php");


 
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
        where type.t_id_auto=$type_product        ";

  $a=[];
  $aaa=[];
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
          $aaa[$numm]=$row2['m_id'];
           $numm++;
        }
        
      if($numm==1){
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
                    "text"=>  $aaa[0]
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
      }elseif($numm==2)
      {
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
                    "text"=>  $aaa[0]
                ],
                [
                    "type"=>  "message",
                    "label"=>  $a[1]." บาท",
                    "text"=>  $aaa[1]
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
            "thumbnailImageUrl"=>  "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT9WvUF2kYT0Rg316K9-4zMCvH2TkNvp15gK6SDQwfRLSQhbkDv&s",
            "imageBackgroundColor"=>  "#FFFFFF",
            "title"=>  $row["tp_name"],
            "text"=>  "กรุณาเลือกประเภทของกาแฟของท่าน ตามเมนูข้างล่างค่ะ",
           
            "actions"=>  [
                [
                    "type"=>  "message",
                    "label"=>  $a[0]." บาท",
                    "text"=>  $aaa[0]
                ],
                [
                    "type"=>  "message",
                    "label"=>  $a[1]." บาท",
                    "text"=>  $aaa[1]
                ],
                [
                    "type"=>  "message",
                    "label"=>  $a[2]." บาท",
                    "text"=>  $aaa[2]
                ]
            ]
          ];

      }
     
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
 





  

  
 $replyJson["messages"][0] = $replyText;
  
  if($text=="เมนูกาแฟ")
  {
    $replyJson["messages"][0] = $replyText1;
  }

   
?>
