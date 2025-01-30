<?php
$user = "root";
$pass = "";
$host = "localhost";
$db = "automobile";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}else{
    
}

?>