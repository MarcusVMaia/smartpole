<!DOCTYPE html>

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

<h2 style="margin-top: 170px">Mapa</h2>
<div id="map" style="width:800px;height:400px;background:white"></div>
<script>

function myMap() {
    var mapOptions = {
        center: new google.maps.LatLng(-15.79, -47.88),
        zoom: 12,
        mapTypeId: google.maps.MapTypeId.HYBRID
    }
    var map = new google.maps.Map(document.getElementById("map"), mapOptions);
    var infoWindow = new google.maps.InfoWindow;

// Change this depending on the name of your PHP or XML file
    downloadUrl('http://smartpoleunb.com/createXML.php', function(data) {
        var xml = data.responseXML;
        var markers = xml.documentElement.getElementsByTagName('marker');
        Array.prototype.forEach.call(markers, function(markerElem) {
          var name = markerElem.getAttribute('name');
          var poleLabel = markerElem.getAttribute('label');
          var timeStamp = markerElem.getAttribute('timeStamp');
          var temperatura = markerElem.getAttribute('temp');
          var umidade = markerElem.getAttribute('umid');
          var gasMQ4 = markerElem.getAttribute('gasMQ4');
          var gasMQ7 = markerElem.getAttribute('gasMQ7');
          var point = new google.maps.LatLng(
              parseFloat(markerElem.getAttribute('lat')),
              parseFloat(markerElem.getAttribute('lng'))
            );
          var infowincontent = document.createElement('div');
          
          var strong = document.createElement('strong');
          strong.textContent = name
          infowincontent.appendChild(strong);
          infowincontent.appendChild(document.createElement('br'));

          var strong = document.createElement('strong');
          strong.textContent = timeStamp
          infowincontent.appendChild(strong);
          infowincontent.appendChild(document.createElement('br'));

          var text = document.createElement('text');
          text.textContent = "Temperature: "+temperatura+" C | Humidity: "+umidade+" %"
          infowincontent.appendChild(text);
          infowincontent.appendChild(document.createElement('br'));

          var text = document.createElement('text');
          text.textContent = "Methane gas: "+gasMQ4+" ppm | CO gas: "+gasMQ7+" ppm"
          infowincontent.appendChild(text);

          var marker = new google.maps.Marker({
            map: map,
            position: point,
            label: poleLabel
          });
          marker.addListener('click', function() {
            infoWindow.setContent(infowincontent);
            infoWindow.open(map, marker);
          });
        });
    });

}

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

</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDg919M42dEk0wRzkrl23wT_yEMtTjUzFE&callback=myMap"></script>
</body>
</html>