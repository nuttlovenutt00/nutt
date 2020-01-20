<?php
$servername = "37.59.55.185";
$username = "Z01XVlWSlA";
$password = "ogqvLgVKmd";

try {
    $conn = new PDO("mysql:host=$servername;dbname=Z01XVlWSlA", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }
?>
