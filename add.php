<?php
    include("connect.php");
    
    $link=Connection();

 $TEMP=$_POST["TEMPERATURA"];
 $UMID=$_POST["UMIDADE"];
 $CHUVA=$_POST["CHUVA"];
 $MQ4=$_POST["GASMQ4"];
 $MQ7=$_POST["GASMQ7"];
 $LAT=$_POST["LATITUDE"];
 $LON=$_POST["LONGITUDE"];
 $LUZ=$_POST["LUZ"];
 $RUIDO=$_POST["RUIDO"];
 $AR=$_POST["AR"];
 date_default_timezone_set('America/Sao_Paulo');
 $TIMESTAMP=date("Y/m/d H:i");
 
 $query = "INSERT INTO `admin_default`.`Dados` (`timeStamp`,`temp`,`umid`,`rain`,`gasMQ4`,`gasMQ7`,`lat`,`lon`,`light`,`noise`,`air`) 
  VALUES ('".$TIMESTAMP."','".$TEMP."','".$UMID."','".$CHUVA."','".$MQ4."','".$MQ7."','".$LAT."','".$LON."','".$LUZ."','".$RUIDO."','".$AR."')"; 
   
    mysqli_query($link,$query);
 mysqli_close($link);
     
    header("Location: index.php");
?>
