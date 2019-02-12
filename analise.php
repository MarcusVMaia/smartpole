<!DOCTYPE html>

<?php

 $compTipo=$_GET['compTipo'];
 $compVariavel=$_GET['compVariavel'];
 $queryComp="SELECT ".$compTipo."(".$compVariavel.") As ".$compVariavel." FROM Dados GROUP BY lat,lon";

 $tempVariavel=$_GET['tempVariavel'];
 $tempCoords=explode(',',$_GET['tempPoste'],2);
 printf("%s",$tempCoords[0]);
 printf("%s",$tempCoords[1]);
 $queryTemp="SELECT ".$tempVariavel.", timeStamp FROM Dados WHERE lat=".$tempCoords[0]." AND lon=".$tempCoords[1]." ORDER BY timeStamp ASC";

 include("connect.php");  
 
 $link=Connection();

 printf("%s",$queryComp);

 $resultComp=mysqli_query($link,$queryComp);

 $compArray = array();
 $i = 0;
 while($row = mysqli_fetch_array($resultComp,MYSQLI_ASSOC)) {
	$tempLabel = "P".(string)$i; 
	$compArray[$tempLabel] = $row;
	$i = $i +1;
 }

 $resultTemp=mysqli_query($link,$queryTemp);
 $tempArray = array();
 $j = 0;
 while($row = mysqli_fetch_array($resultTemp,MYSQLI_ASSOC)){
	$tempArray[$j] = $row;
	$j = $j +1;
 }

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

<h2 style="margin-top: 170px">Análise Comparativa</h2>
<form id="compOpcoes" action="" method="get">
<select id="compVariavel" name="compVariavel" onchange="this.form.submit()">
  <option selected value="temp">Temperatura</option>
  <option value="umid">Umidade</option>
  <option value="rain">Precipitacao</option>
  <option value="gasMQ4">Metano</option>
  <option value="gasMQ7">Monoxido de Carbono</option>
</select>
<select id="compTipo" name="compTipo" onchange="this.form.submit()">
  <option selected value="MAX">Maximo</option>
  <option value="AVG">Medio</option>
  <option value="MIN">Minimo</option>
  <option value="LIVE">Atual</option>
</select>

<canvas id="graficoComp" width="60" height="16"></canvas>

<h2 style="margin-top: 170px">Análise Temporal</h2>
<select id="tempVariavel" name="tempVariavel" onchange="this.form.submit()">
  <option selected value="temp">Temperatura</option>
  <option value="umid">Umidade</option>
  <option value="rain">Precipitacao</option>
  <option value="gasMQ4">Metano</option>
  <option value="gasMQ7">Monoxido de Carbono</option>
</select>
<select id="tempPoste" name="tempPoste" onchange="this.form.submit()">
</select>

<canvas id="graficoTemp" width="60" height="16"></canvas>

</form>

<script type="text/javascript">
  document.getElementById('compVariavel').value = "<?php echo $_GET['compVariavel'];?>";
  document.getElementById('compTipo').value = "<?php echo $_GET['compTipo'];?>";
  document.getElementById('tempVariavel').value = "<?php echo $_GET['tempVariavel'];?>";
</script>


<br><br>


<script src="./node_modules/chart.js/dist/Chart.js"></script>
<script>

function downloadUrl(url,callback) {
 var request = window.ActiveXObject ?
     new ActiveXObject('Microsoft.XMLHTTP') :
     new XMLHttpRequest;

 request.onreadystatechange = function() {
   if (request.readyState == 4) {
     request.onreadystatechange = doNothing;
     callback(request, request.status);
   }
 };

 request.open('GET', url, true);
 request.send(null);
}

function doNothing() {}

window.onload = function() {
	var ctx = document.getElementById("graficoComp");
	window.myCompChart = new Chart(ctx, {
	    type: 'bar',
	    data: grafData,
	    options: {
	        scales: {
	            yAxes: [{
	                ticks: {
	                    beginAtZero:true
	                }
	            }]
	        }
	    } 
	});

	var ctxTemp = document.getElementById("graficoTemp");
	window.myTempChart = new Chart(ctxTemp, {
	    type: 'line',
	    data: grafDataTime,
	    options: {
	        scales: {
	            yAxes: [{
	                ticks: {
	                    beginAtZero:true
	                }
	            }]
	        }
	    } 
	});
};

var compData = <?php echo json_encode($compArray,JSON_PRETTY_PRINT) ?>;
console.log(compData);

var values = [];
var x_label = [];
var j = 0;
for (i in compData) {
	x_label[j]="Poste "+(j+1);
	for(k in compData[i]) values[j]=Number(compData[i][k]);
	j++;
}

console.log(x_label);
console.log(values);

var grafData = {
        labels: x_label,
        datasets: [{
        	label: document.getElementById('compVariavel').value+" "+document.getElementById('compTipo').value,
            data: values,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
}


var lats = [];
var longs = [];
var names = [];
var opcoesPostes=" ";

downloadUrl('http://smartpoleunb.com/createXML.php', function(data) {
    var xml = data.responseXML;
    var markers = xml.documentElement.getElementsByTagName('marker');
    var i = 0;

    Array.prototype.forEach.call(markers, function(markerElem) {
      names[i] = markerElem.getAttribute('name');
      lats[i] = markerElem.getAttribute('lat');
      longs[names[i]] = markerElem.getAttribute('lng');
	  opcoesPostes = opcoesPostes + "<option value="+markerElem.getAttribute('lat')+","+markerElem.getAttribute('lng')+">"+markerElem.getAttribute('name')+"</option>";

      i++;
    });
   
    document.getElementById("tempPoste").innerHTML = opcoesPostes;
    document.getElementById('tempPoste').value = "<?php echo $_GET['tempPoste'];?>";
    window.myCompChart.update();
    window.myTempChart.update();
});

var tempData = <?php echo json_encode($tempArray,JSON_PRETTY_PRINT) ?>;
console.log(tempData);

var valuesTime = [];
var timeLabel = [];
var j = 0;
for (dadoHistorico in tempData) {
	timeLabel[j]=tempData[dadoHistorico].timeStamp;
	for(k in tempData[dadoHistorico]) {
		valuesTime[j]=Number(tempData[dadoHistorico][k]); 
		break;
	}
	j++;
}
console.log(valuesTime);
console.log(timeLabel);

var grafDataTime = {
        labels: timeLabel,
        datasets: [{
        	label: "historico",
            data: valuesTime,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ],
            borderColor: [
                'rgba(255,99,132,1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ],
            borderWidth: 1
        }]
}

</script>


</body>
</html>