<!DOCTYPE html>

<?php

 include("connect.php");  
 
 $link=Connection();

 $result=mysqli_query($link,"SELECT * FROM Dados ORDER BY timeStamp DESC");
?>

<html>
<head>
	<title>Smart Pole</title>
	<link rel="stylesheet" type="text/css" href="smartpole_stylesheet.css">
</head>
<body>
<header>
<img id="unb_logo" src="unb.jpg">
<h1 class="titulo">Projeto Smart Pole</h1>
<ul>
	<li class="menu_horizontal"><a href="./index.php">Home</a></li>
	<li class="menu_horizontal"><a href="./sobre.html">Sobre</a></li>
	<li class="menu_horizontal"><a href="./dados.php">Mapa</a></li>
  <li class="menu_horizontal"><a href="./analise.php">Análise</a></li>
	<li class="menu_horizontal"><a href="./contato.html">Contato</a></li>
</ul>	
</header>

<br><br><br><br>
<h2 style="margin-top: 170px">Objetivo</h2>
<p>This is a Senior Design Project in Electrical Engineering for the University of Brasilia. In Brazil, a good share of the government budget is destined to public lightning and safety. Besides that, today the air quality of the city is poorly managed. Thinking about those issues we decided to pursue a project which would cause a solid impact in society, bringing the idea of IoT, energy efficiency, and sensor network to build a light-pole which would reduce the cost of lighting and improve the air quality measurement. </p>

<h2>Dados</h2>

   <table border="1" cellspacing="0" cellpadding="0" width="80%">
  <tr>
   <td align='center' width='160px'><b>&nbsp;Data e Hora&nbsp;</b></td>
   <td align='center' width='160px'><b>&nbsp;Latitude&nbsp;</b></td>
   <td align='center' width='160px'><b>&nbsp;Longitude&nbsp;</b></td>
   <td align='center' width='375px'><b>&nbsp;Temperatura (C)&nbsp;</b></td>
   <td align='center' width='375px'><b>&nbsp;Umidade (%)&nbsp;</b></td>
   <td align='center' width='375px'><b>&nbsp;Chuva (%)&nbsp;</b></td>
   <td align='center' width='375px'><b>&nbsp;Gas MQ4 (ppm)&nbsp;</b></td>
   <td align='center' width='375px'><b>&nbsp;Gas MQ7 (ppm)&nbsp;</b></td>
  </tr>

      <?php 
    if($result!==FALSE){
       while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
          printf("<tr><td align='center'> %s </td><td> %s </td><td> %s </td><td> %s </td><td> %s </td><td> %s </td><td> %s </td><td> %s </td></tr>", $row["timeStamp"],$row["lat"],$row["lon"], $row["temp"], $row["umid"], $row["rain"], $row["gasMQ4"], $row["gasMQ7"]);
       }
       mysqli_free_result($result);
       mysqli_close($link);
    }
      ?>

   </table>

</body>
</html>
