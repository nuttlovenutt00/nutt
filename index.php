<?php

 $servername = "37.59.55.185";
  $username = "Z01XVlWSlA";
  $password = "ogqvLgVKmd";
  $dbname = "Z01XVlWSlA";
  $mysql = new mysqli($servername, $username, $password, $dbname);
  mysqli_set_charset($mysql, "utf8");

$sql = "SELECT * FROM type 
        left join type_product  on type.t_id_auto = type_product.tp_t_id
        where type.t_id_auto='1'        ";

$a=[];

$result = $mysql->query($sql);
$num=0;
if ($result->num_rows > 0) {
    // output data of each row
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
      
            $a[$numm]= $row2['m_name']."&nbsp;".$row2['m_price']."&nbsp;"."บาท"."<br>";
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
} else {
    echo "0 results";
}



echo '<pre>'; print_r($a2); echo '</pre>';

?>
