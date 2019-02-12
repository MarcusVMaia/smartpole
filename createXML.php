<?php
include("connect.php");

// Start XML file, create parent node
$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);  
 
$link=Connection();

// Select all the rows with unique coords in the markers table
$result=mysqli_query($link,"SELECT lat, lon, MAX(timeStamp) As timeStamp FROM Dados GROUP BY lat,lon");
if (!$result) {
  die('Invalid query: ' . mysqli_error());
}

header("Content-type: text/xml");

// Select unique poles and last timestamp
$arrayLat = array("");
$arrayLon = array("");
$arrayTime = array("");
while ($pole = mysqli_fetch_array($result,MYSQLI_ASSOC)){
  array_push($arrayLat, (string)$pole['lat']);
  array_push($arrayLon, (string)$pole['lon']);
  array_push($arrayTime, (string)$pole['timeStamp']);
}

// Iterate through the rows, adding XML nodes for each
for ($i=1; $i < count($arrayLat); $i++) { 
  // Select all data for each row
  $lastData=mysqli_query($link,"SELECT * FROM Dados WHERE lat = ".$arrayLat[$i]." AND lon = ".$arrayLon[$i]." AND timeStamp = '".$arrayTime[$i]."'");
  if (!$lastData) {
    die('Invalid query: ' . mysqli_error());
  }

  mysqli_data_seek($lastData, 0);
  while($row = mysqli_fetch_array($lastData,MYSQLI_ASSOC)){

  // Add to XML document node
  $node = $dom->createElement("marker");
  $newnode = $parnode->appendChild($node);

  $name = "Poste ".(string)$i;
  $label = "P".(string)$i;

  $newnode->setAttribute("name", $name);
  $newnode->setAttribute("label", $label);
  $newnode->setAttribute("timeStamp", $row['timeStamp']);
  $newnode->setAttribute("lat", $row['lat']);
  $newnode->setAttribute("lng", $row['lon']);
  $newnode->setAttribute("temp", $row['temp']);
  $newnode->setAttribute("umid", $row['umid']);
  $newnode->setAttribute("gasMQ4", $row['gasMQ4']);
  $newnode->setAttribute("gasMQ7", $row['gasMQ7']);
  $newnode->setAttribute("rain", $row['rain']);

  }
}

echo $dom->saveXML();

?>