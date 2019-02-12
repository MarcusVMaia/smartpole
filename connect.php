<?php
 function Connection(){
  $user="admin_default";
  $pass="posteinteligente";
  $db="admin_default";
     
  $connection = @mysqli_connect('localhost', $user, $pass, $db);
           
if (mysqli_connect_errno())
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

  return $connection;
 }
?>
